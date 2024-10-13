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

// ฟังก์ชันสำหรับหาคลังที่มีสต็อกเพียงพอ
function findAvailableLocations($inventory, $productId, $requiredQuantity) {
    $availableLocations = [];
    foreach ($inventory[$productId] as $locationId => $quantity) {
        if ($quantity > 0) {
            $availableLocations[$locationId] = $quantity;
        }
    }
    arsort($availableLocations); // เรียงลำดับคลังตามจำนวนสต็อกจากมากไปน้อย
    return $availableLocations;
}

try {
    $conn->beginTransaction();

    // ดึงข้อมูลสต็อกทั้งหมด
    $productIds = array_column($products, 'product_id');
    $getInventorySql = "SELECT product_id, location_id, quantity FROM inventory WHERE product_id IN ('" . implode("','", $productIds) . "')";
    $stmt = $conn->query($getInventorySql);
    $inventoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $inventory = [];
    $totalInventory = [];
    foreach ($inventoryData as $item) {
        $inventory[$item['product_id']][$item['location_id']] = floatval($item['quantity']);
        if (!isset($totalInventory[$item['product_id']])) {
            $totalInventory[$item['product_id']] = 0;
        }
        $totalInventory[$item['product_id']] += floatval($item['quantity']);
    }
    logError("Initial inventory data: " . json_encode($inventory));
    logError("Total inventory data: " . json_encode($totalInventory));

    // ดึงข้อมูลการโอนย้ายเดิม
    $getOldTransferSql = "SELECT product_id, from_location_id, to_location_id, quantity FROM d_transfer WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($getOldTransferSql);
    $stmt->execute([':transfer_id' => $transferId]);
    $oldTransferData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // สร้าง array เพื่อเก็บข้อมูลการโอนย้ายเดิม
    $oldTransfers = [];
    foreach ($oldTransferData as $oldItem) {
        $oldTransfers[$oldItem['product_id']] = $oldItem;
    }

    // ยกเลิกการโอนย้ายเก่าและคำนวณสต็อกที่มีอยู่จริง
    foreach ($oldTransfers as $productId => $oldTransfer) {
        $oldFromLocationId = $oldTransfer['from_location_id'];
        $oldToLocationId = $oldTransfer['to_location_id'];
        $oldQuantity = $oldTransfer['quantity'];

        // เพิ่มกลับไปที่คลังต้นทางเดิม
        if (!isset($inventory[$productId][$oldFromLocationId])) {
            $inventory[$productId][$oldFromLocationId] = 0;
        }
        $inventory[$productId][$oldFromLocationId] += $oldQuantity;
        
        // ลบออกจากคลังปลายทางเดิม
        if (isset($inventory[$productId][$oldToLocationId])) {
            $inventory[$productId][$oldToLocationId] = max(0, $inventory[$productId][$oldToLocationId] - $oldQuantity);
        }

        // อัปเดต totalInventory
        $totalInventory[$productId] = array_sum($inventory[$productId]);
    }

    logError("Inventory after cancelling old transfer: " . json_encode($inventory));
    logError("Total inventory after cancelling: " . json_encode($totalInventory));

    // ตรวจสอบและทำการโอนย้ายใหม่
    $newTransfers = [];
    foreach ($products as $product) {
        $productId = $product['product_id'];
        $toLocationId = $product['to_location_id'];
        $requiredQuantity = $product['quantity'];

        if ($totalInventory[$productId] < $requiredQuantity) {
            throw new Exception("Insufficient total stock for product {$productId}. Total available: {$totalInventory[$productId]}, Required: {$requiredQuantity}");
        }

        $availableLocations = findAvailableLocations($inventory, $productId, $requiredQuantity);
        $remainingQuantity = $requiredQuantity;

        foreach ($availableLocations as $fromLocationId => $availableQuantity) {
            $transferQuantity = min($availableQuantity, $remainingQuantity);
            
            // ทำการโอนย้าย
            $inventory[$productId][$fromLocationId] -= $transferQuantity;
            $inventory[$productId][$toLocationId] = ($inventory[$productId][$toLocationId] ?? 0) + $transferQuantity;
            
            // บันทึกการโอนย้าย
            $newTransfers[] = [
                'product_id' => $productId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'quantity' => $transferQuantity,
                'unit' => $product['unit']
            ];

            $remainingQuantity -= $transferQuantity;
            if ($remainingQuantity <= 0) {
                break;
            }
        }

        if ($remainingQuantity > 0) {
            throw new Exception("Unable to fulfill transfer for product {$productId}. Remaining quantity: {$remainingQuantity}");
        }
    }

    logError("Inventory after new transfers: " . json_encode($inventory));

    // อัปเดตฐานข้อมูล inventory
    $updateInventorySql = "INSERT INTO inventory (product_id, location_id, quantity, user_id) 
                           VALUES (:product_id, :location_id, :quantity, :user_id)
                           ON DUPLICATE KEY UPDATE quantity = :quantity, user_id = :user_id";
    $updateInventoryStmt = $conn->prepare($updateInventorySql);

    foreach ($inventory as $productId => $locations) {
        foreach ($locations as $locationId => $quantity) {
            $updateInventoryStmt->execute([
                ':product_id' => $productId,
                ':location_id' => $locationId,
                ':quantity' => $quantity,
                ':user_id' => $user_id
            ]);
        }
    }

    // อัปเดตข้อมูลการโอนย้าย
    $deleteOldTransferSql = "DELETE FROM d_transfer WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($deleteOldTransferSql);
    $stmt->execute([':transfer_id' => $transferId]);

    $insertNewTransferSql = "INSERT INTO d_transfer (transfer_header_id, product_id, from_location_id, to_location_id, quantity, unit) VALUES (:transfer_id, :product_id, :from_location_id, :to_location_id, :quantity, :unit)";
    $insertStmt = $conn->prepare($insertNewTransferSql);

    foreach ($newTransfers as $transfer) {
        $insertStmt->execute([
            ':transfer_id' => $transferId,
            ':product_id' => $transfer['product_id'],
            ':from_location_id' => $transfer['from_location_id'],
            ':to_location_id' => $transfer['to_location_id'],
            ':quantity' => $transfer['quantity'],
            ':unit' => $transfer['unit']
        ]);
    }

    // อัปเดตวันที่โอนย้าย
    $updateHeaderSql = "UPDATE h_transfer SET transfer_date = :transfer_date WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($updateHeaderSql);
    $stmt->execute([
        ':transfer_date' => $transferDate,
        ':transfer_id' => $transferId
    ]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transfer updated successfully', 'transfers' => $newTransfers]);
} catch (Exception $e) {
    $conn->rollBack();
    $errorMessage = 'Error updating transfer: ' . $e->getMessage();
    logError($errorMessage);
    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
}
?>