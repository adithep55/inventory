<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_transfers']);


// ฟังก์ชันสำหรับบันทึก log
function logError($message) {
    $logFile = '../error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
$user_id = $_SESSION['UserID'];
// รับข้อมูล JSON จาก request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

logError("Received data: " . json_encode($data));

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วน
if (!isset($data['transfer_id']) || !isset($data['transfer_date']) || !isset($data['products'])) {
    logError("Missing required data");
    echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    exit;
}

$transferId = $data['transfer_id'];
$transferDate = $data['transfer_date'];
$products = $data['products'];

try {
    $conn->beginTransaction();

    // ดึงข้อมูลสต็อกทั้งหมด
    $productIds = array_column($products, 'product_id');
    $getInventorySql = "SELECT product_id, location_id, quantity FROM inventory WHERE product_id IN ('" . implode("','", $productIds) . "')";
    $stmt = $conn->query($getInventorySql);
    $inventoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $inventory = [];
    foreach ($inventoryData as $item) {
        $inventory[$item['product_id']][$item['location_id']] = floatval($item['quantity']);
    }
    logError("Fresh inventory data: " . json_encode($inventory));

    // ตรวจสอบว่ามีสต็อกเพียงพอสำหรับการโอนย้ายใหม่
    foreach ($products as $product) {
        $fromLocationId = $product['from_location_id'];
        $currentStock = $inventory[$product['product_id']][$fromLocationId] ?? 0;
        if ($currentStock < $product['quantity']) {
            throw new Exception("Insufficient stock for product {$product['product_id']} at location {$fromLocationId}. Available: $currentStock, Required: {$product['quantity']}");
        }
    }

    // อัพเดทข้อมูลหลักของการโอนย้าย
    $updateHeaderSql = "UPDATE h_transfer SET transfer_date = :transfer_date WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($updateHeaderSql);
    $stmt->execute([
        ':transfer_date' => $transferDate,
        ':transfer_id' => $transferId
    ]);

    // ลบข้อมูลรายการสินค้าเดิม
    $deleteDetailsSql = "DELETE FROM d_transfer WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($deleteDetailsSql);
    $stmt->execute([':transfer_id' => $transferId]);

    // เพิ่มข้อมูลรายการสินค้าใหม่และปรับปรุงสต็อก
    $insertDetailsSql = "INSERT INTO d_transfer (transfer_header_id, product_id, from_location_id, to_location_id, quantity, unit) 
                         VALUES (:transfer_id, :product_id, :from_location_id, :to_location_id, :quantity, :unit)";
    $stmt = $conn->prepare($insertDetailsSql);

    $updateInventorySql = "INSERT INTO inventory (product_id, location_id, quantity, user_id) 
                           VALUES (:product_id, :location_id, :quantity, :user_id)
                           ON DUPLICATE KEY UPDATE quantity = :quantity, user_id = :user_id";
    $updateInventoryStmt = $conn->prepare($updateInventorySql);

    foreach ($products as $product) {
        $stmt->execute([
            ':transfer_id' => $transferId,
            ':product_id' => $product['product_id'],
            ':from_location_id' => $product['from_location_id'],
            ':to_location_id' => $product['to_location_id'],
            ':quantity' => $product['quantity'],
            ':unit' => $product['unit']
        ]);

        $fromLocationId = $product['from_location_id'];
        $toLocationId = $product['to_location_id'];
        $quantity = $product['quantity'];

        // ปรับปรุงสต็อกในข้อมูลชั่วคราว
        $inventory[$product['product_id']][$fromLocationId] -= $quantity;
        $inventory[$product['product_id']][$toLocationId] = ($inventory[$product['product_id']][$toLocationId] ?? 0) + $quantity;

        // อัพเดตฐานข้อมูล inventory
        $updateInventoryStmt->execute([
            ':product_id' => $product['product_id'],
            ':location_id' => $fromLocationId,
            ':quantity' => $inventory[$product['product_id']][$fromLocationId],
            ':user_id' => $user_id
        ]);
        $updateInventoryStmt->execute([
            ':product_id' => $product['product_id'],
            ':location_id' => $toLocationId,
            ':quantity' => $inventory[$product['product_id']][$toLocationId],
            ':user_id' => $user_id
        ]);
    }

    logError("Final inventory state: " . json_encode($inventory));

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transfer updated successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    $errorMessage = 'Error updating transfer: ' . $e->getMessage();
    logError($errorMessage);
    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
}
?>