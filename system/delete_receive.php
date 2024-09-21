<?php
require_once '../config/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Receive ID is required']);
    exit;
}

$receiveId = intval($_POST['id']);

try {
    $conn->beginTransaction();

    // First, get the items for this receive to update inventory
    $stmt = $conn->prepare("SELECT product_id, quantity, location_id FROM d_receive WHERE receive_header_id = ?");
    $stmt->execute([$receiveId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update inventory for each item
    foreach ($items as $item) {
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND location_id = ?");
        $stmt->execute([$item['quantity'], $item['product_id'], $item['location_id']]);
    }

    // Delete from d_receive
    $stmt = $conn->prepare("DELETE FROM d_receive WHERE receive_header_id = ?");
    $stmt->execute([$receiveId]);

    // Delete from h_receive
    $stmt = $conn->prepare("DELETE FROM h_receive WHERE receive_header_id = ?");
    $stmt->execute([$receiveId]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Receive record deleted successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>