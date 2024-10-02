<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/connect.php';

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '../error.log');
}

function generateBillNumber($conn) {
    $thaiYear = (int)date('Y') + 543;
    $year = substr($thaiYear, -2);
    
    $stmt = $conn->query("SELECT MAX(CAST(SUBSTRING(bill_number, 4) AS UNSIGNED)) as max_number FROM h_transfer WHERE bill_number LIKE 'T{$year}%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxNumber = $result['max_number'];
    $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
    return 'T' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    logError("Invalid JSON data received: " . $json);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    exit;
}

logError("Received transfer data: " . print_r($data, true));

$transferDate = new DateTime($data['transfer_date']);
$today = new DateTime();
$today->setTime(0, 0, 0);

if ($transferDate > $today) {
    echo json_encode(['status' => 'error', 'message' => 'Transfer date cannot be in the future']);
    exit;
}

try {
    $conn->beginTransaction();

    $transferHeaders = [];
    foreach ($data['products'] as $product) {
        $key = $product['from_location_id'] . '-' . $product['to_location_id'];
        if (!isset($transferHeaders[$key])) {
            $billNumber = generateBillNumber($conn);
            $stmt = $conn->prepare("INSERT INTO h_transfer (bill_number, transfer_date, from_location_id, to_location_id, user_id) VALUES (:bill_number, :transfer_date, :from_location_id, :to_location_id, :user_id)");
            $result = $stmt->execute([
                ':bill_number' => $billNumber,
                ':transfer_date' => $data['transfer_date'],
                ':from_location_id' => $product['from_location_id'],
                ':to_location_id' => $product['to_location_id'],
                ':user_id' => $_SESSION['user_id'] ?? 1
            ]);

            if (!$result) {
                throw new Exception("Failed to insert into h_transfer: " . implode(", ", $stmt->errorInfo()));
            }

            $transferHeaders[$key] = [
                'id' => $conn->lastInsertId(),
                'bill_number' => $billNumber
            ];
        }
    }

    foreach ($data['products'] as $product) {
        // ตรวจสอบจำนวนสินค้าในคลังต้นทาง
        $stmt = $conn->prepare("
            SELECT quantity 
            FROM inventory 
            WHERE product_id = :product_id 
            AND location_id = :location_id 
            AND quantity > 0
            ORDER BY updated_at DESC
            LIMIT 1
        ");
        $stmt->execute([
            ':product_id' => $product['product_id'],
            ':location_id' => $product['from_location_id']
        ]);
        $currentQuantity = $stmt->fetchColumn();
    
        if ($currentQuantity === false) {
            throw new Exception("ไม่พบสินค้าในคลังต้นทาง หรือจำนวนสินค้าเป็น 0");
        }
    
        if ($product['quantity'] > $currentQuantity) {
            throw new Exception("ไม่สามารถโอนย้ายสินค้าได้ เนื่องจากจำนวนสินค้าในคลังไม่เพียงพอ (มี {$currentQuantity} ชิ้น, ต้องการโอน {$product['quantity']} ชิ้น)");
        }

        $key = $product['from_location_id'] . '-' . $product['to_location_id'];
        $transfer_header_id = $transferHeaders[$key]['id'];

        // Insert into d_transfer
        $stmt = $conn->prepare("INSERT INTO d_transfer (transfer_header_id, product_id, quantity, unit, user_id) VALUES (:transfer_header_id, :product_id, :quantity, :unit, :user_id)");
        $result = $stmt->execute([
            ':transfer_header_id' => $transfer_header_id,
            ':product_id' => $product['product_id'],
            ':quantity' => $product['quantity'],
            ':unit' => $product['unit'] ?? '',
            ':user_id' => $_SESSION['user_id'] ?? 1
        ]);

        if (!$result) {
            throw new Exception("Failed to insert into d_transfer: " . implode(", ", $stmt->errorInfo()));
        }

        // Update inventory for 'from' location
        $stmt = $conn->prepare("UPDATE inventory SET quantity = GREATEST(quantity - :quantity, 0), updated_at = NOW() WHERE product_id = :product_id AND location_id = :location_id");
        $result = $stmt->execute([
            ':quantity' => $product['quantity'],
            ':product_id' => $product['product_id'],
            ':location_id' => $product['from_location_id']
        ]);

        if (!$result) {
            throw new Exception("Failed to update inventory for 'from' location: " . implode(", ", $stmt->errorInfo()));
        }

        // Update or insert inventory for 'to' location
        $stmt = $conn->prepare("
            INSERT INTO inventory (product_id, location_id, quantity, user_id, updated_at) 
            VALUES (:product_id, :location_id, :quantity, :user_id, NOW())
            ON DUPLICATE KEY UPDATE 
                quantity = quantity + VALUES(quantity),
                updated_at = NOW()
        ");
        $result = $stmt->execute([
            ':product_id' => $product['product_id'],
            ':location_id' => $product['to_location_id'],
            ':quantity' => $product['quantity'],
            ':user_id' => $_SESSION['user_id'] ?? 1
        ]);

        if (!$result) {
            throw new Exception("Failed to update inventory for 'to' location: " . implode(", ", $stmt->errorInfo()));
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transfer saved successfully', 'bill_numbers' => array_column($transferHeaders, 'bill_number')]);
} catch (Exception $e) {
    $conn->rollBack();
    logError("Error in save_transfer.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error saving transfer: ' . $e->getMessage()]);
}
?>