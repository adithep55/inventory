<?php
require_once '../config/connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Transfer ID is required']);
    exit;
}

$transferId = intval($_GET['id']);

try {
    // Fetch transfer header information
    $headerQuery = "SELECT h.transfer_header_id, h.bill_number, h.transfer_date, 
                           l1.location as from_location, l2.location as to_location,
                           u.Username as username
                    FROM h_transfer h
                    JOIN locations l1 ON h.from_location_id = l1.location_id
                    JOIN locations l2 ON h.to_location_id = l2.location_id
                    JOIN users u ON h.user_id = u.UserID
                    WHERE h.transfer_header_id = :id";
    
    $stmt = $conn->prepare($headerQuery);
    $stmt->bindParam(':id', $transferId, PDO::PARAM_INT);
    $stmt->execute();
    $header = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$header) {
        echo json_encode(['error' => 'Transfer not found']);
        exit;
    }

    // Fetch transfer items
    $itemsQuery = "SELECT d.product_id, p.name_th as product_name, d.quantity, p.unit,
                          l1.location as from_location, l2.location as to_location
                   FROM d_transfer d
                   JOIN products p ON d.product_id = p.product_id
                   JOIN h_transfer h ON d.transfer_header_id = h.transfer_header_id
                   JOIN locations l1 ON h.from_location_id = l1.location_id
                   JOIN locations l2 ON h.to_location_id = l2.location_id
                   WHERE d.transfer_header_id = :id";
    
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bindParam(':id', $transferId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine header and items data
    $result = array_merge($header, ['items' => $items]);

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>