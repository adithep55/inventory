<?php
require_once '../config/connect.php';

header('Content-Type: application/json');


if (!isset($_POST['transfer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Transfer ID is required']);
    exit;
}

$transferId = intval($_POST['transfer_id']);

try {
    $conn->beginTransaction();

    // Fetch transfer details
    $stmt = $conn->prepare("SELECT h.transfer_header_id, h.from_location_id, h.to_location_id,
                                   d.product_id, d.quantity
                            FROM h_transfer h
                            JOIN d_transfer d ON h.transfer_header_id = d.transfer_header_id
                            WHERE h.transfer_header_id = :transfer_id");
    $stmt->execute([':transfer_id' => $transferId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception("No transfer found with ID: $transferId");
    }

    foreach ($items as $item) {
        // Revert inventory for 'from' location
        $stmt = $conn->prepare("UPDATE inventory 
                                SET quantity = quantity + :quantity 
                                WHERE product_id = :product_id AND location_id = :from_location_id");
        $stmt->execute([
            ':quantity' => $item['quantity'],
            ':product_id' => $item['product_id'],
            ':from_location_id' => $item['from_location_id']
        ]);

        // Revert inventory for 'to' location
        $stmt = $conn->prepare("UPDATE inventory 
                                SET quantity = quantity - :quantity 
                                WHERE product_id = :product_id AND location_id = :to_location_id");
        $stmt->execute([
            ':quantity' => $item['quantity'],
            ':product_id' => $item['product_id'],
            ':to_location_id' => $item['to_location_id']
        ]);
    }

    // Delete transfer details
    $stmt = $conn->prepare("DELETE FROM d_transfer WHERE transfer_header_id = :transfer_id");
    $stmt->execute([':transfer_id' => $transferId]);

    // Delete transfer header
    $stmt = $conn->prepare("DELETE FROM h_transfer WHERE transfer_header_id = :transfer_id");
    $stmt->execute([':transfer_id' => $transferId]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transfer deleted successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error in delete_transfer.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error deleting transfer: ' . $e->getMessage()]);
}
?>