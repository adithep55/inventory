<?php
require_once '../config/connect.php';

// รับข้อมูล JSON จาก request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วน
if (!isset($data['transfer_id']) || !isset($data['transfer_date']) || !isset($data['products'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    exit;
}

$transferId = $data['transfer_id'];
$transferDate = $data['transfer_date'];
$products = $data['products'];

try {
    $conn->beginTransaction();

    // ดึงข้อมูลการโอนย้ายเดิม
    $getOldTransferSql = "SELECT * FROM h_transfer WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($getOldTransferSql);
    $stmt->execute([':transfer_id' => $transferId]);
    $oldTransfer = $stmt->fetch(PDO::FETCH_ASSOC);

    // ดึงรายการสินค้าเดิม
    $getOldDetailsSql = "SELECT * FROM d_transfer WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($getOldDetailsSql);
    $stmt->execute([':transfer_id' => $transferId]);
    $oldDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // อัพเดทข้อมูลหลักของการโอนย้าย
    $updateHeaderSql = "UPDATE h_transfer SET transfer_date = :transfer_date, 
                        from_location_id = :from_location_id, 
                        to_location_id = :to_location_id 
                        WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($updateHeaderSql);
    $stmt->execute([
        ':transfer_date' => $transferDate,
        ':from_location_id' => $products[0]['from_location_id'],
        ':to_location_id' => $products[0]['to_location_id'],
        ':transfer_id' => $transferId
    ]);

    // ปรับปรุงสต็อกสินค้า
    foreach ($oldDetails as $oldItem) {
        // คืนสินค้าจากปลายทางเดิมไปต้นทางเดิม
        updateStock($conn, $oldItem['product_id'], $oldTransfer['to_location_id'], -$oldItem['quantity']);
        updateStock($conn, $oldItem['product_id'], $oldTransfer['from_location_id'], $oldItem['quantity']);
    }

    // ลบข้อมูลรายการสินค้าเดิม
    $deleteDetailsSql = "DELETE FROM d_transfer WHERE transfer_header_id = :transfer_id";
    $stmt = $conn->prepare($deleteDetailsSql);
    $stmt->execute([':transfer_id' => $transferId]);

    // เพิ่มข้อมูลรายการสินค้าใหม่และปรับปรุงสต็อก
    $insertDetailsSql = "INSERT INTO d_transfer (transfer_header_id, product_id, quantity, unit) 
                         VALUES (:transfer_id, :product_id, :quantity, :unit)";
    $stmt = $conn->prepare($insertDetailsSql);

    foreach ($products as $product) {
        $stmt->execute([
            ':transfer_id' => $transferId,
            ':product_id' => $product['product_id'],
            ':quantity' => $product['quantity'],
            ':unit' => $product['unit']
        ]);

        // ปรับปรุงสต็อกตามการโอนย้ายใหม่
        updateStock($conn, $product['product_id'], $product['from_location_id'], -$product['quantity']);
        updateStock($conn, $product['product_id'], $product['to_location_id'], $product['quantity']);
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transfer updated successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error updating transfer: ' . $e->getMessage()]);
}

function updateStock($conn, $productId, $locationId, $quantity) {
    $updateStockSql = "UPDATE inventory SET quantity = quantity + :quantity 
                       WHERE product_id = :product_id AND location_id = :location_id";
    $stmt = $conn->prepare($updateStockSql);
    $stmt->execute([
        ':quantity' => $quantity,
        ':product_id' => $productId,
        ':location_id' => $locationId
    ]);

    // ถ้าไม่มีรายการในคลัง ให้สร้างใหม่
    if ($stmt->rowCount() == 0) {
        $insertStockSql = "INSERT INTO inventory (product_id, location_id, quantity) 
                           VALUES (:product_id, :location_id, :quantity)";
        $stmt = $conn->prepare($insertStockSql);
        $stmt->execute([
            ':product_id' => $productId,
            ':location_id' => $locationId,
            ':quantity' => $quantity
        ]);
    }
}
?>