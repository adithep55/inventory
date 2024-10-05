<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_transfers']);

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
        // Check current inventory for 'from' location
        $stmt = $conn->prepare("SELECT quantity FROM inventory 
                                WHERE product_id = :product_id AND location_id = :from_location_id");
        $stmt->execute([
            ':product_id' => $item['product_id'],
            ':from_location_id' => $item['from_location_id']
        ]);
        $fromQuantity = $stmt->fetchColumn();

        // Check current inventory for 'to' location
        $stmt = $conn->prepare("SELECT quantity FROM inventory 
                                WHERE product_id = :product_id AND location_id = :to_location_id");
        $stmt->execute([
            ':product_id' => $item['product_id'],
            ':to_location_id' => $item['to_location_id']
        ]);
        $toQuantity = $stmt->fetchColumn();

        // Calculate new quantities
        $newFromQuantity = $fromQuantity + $item['quantity'];
        $newToQuantity = $toQuantity - $item['quantity'];

        // Update or delete inventory for 'from' location
        if ($newFromQuantity > 0) {
            $stmt = $conn->prepare("INSERT INTO inventory (product_id, location_id, quantity) 
                                    VALUES (:product_id, :from_location_id, :quantity)
                                    ON DUPLICATE KEY UPDATE quantity = :quantity");
            $stmt->execute([
                ':product_id' => $item['product_id'],
                ':from_location_id' => $item['from_location_id'],
                ':quantity' => $newFromQuantity
            ]);
        } else {
            $stmt = $conn->prepare("DELETE FROM inventory 
                                    WHERE product_id = :product_id AND location_id = :from_location_id");
            $stmt->execute([
                ':product_id' => $item['product_id'],
                ':from_location_id' => $item['from_location_id']
            ]);
        }

        // Update or delete inventory for 'to' location
        if ($newToQuantity > 0) {
            $stmt = $conn->prepare("INSERT INTO inventory (product_id, location_id, quantity) 
                                    VALUES (:product_id, :to_location_id, :quantity)
                                    ON DUPLICATE KEY UPDATE quantity = :quantity");
            $stmt->execute([
                ':product_id' => $item['product_id'],
                ':to_location_id' => $item['to_location_id'],
                ':quantity' => $newToQuantity
            ]);
        } else {
            $stmt = $conn->prepare("DELETE FROM inventory 
                                    WHERE product_id = :product_id AND location_id = :to_location_id");
            $stmt->execute([
                ':product_id' => $item['product_id'],
                ':to_location_id' => $item['to_location_id']
            ]);
        }
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