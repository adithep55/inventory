<?php
require_once '../config/connect.php';
// require_once '../config/permission.php';
// requirePermission(['manage_transfers']);

header('Content-Type: application/json');

function exception_handler($exception)
{
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    exit;
}
set_exception_handler('exception_handler');

function formatFullDate($date)
{
    $dateTime = new DateTime($date);
    $thai_year = (int)$dateTime->format('Y') + 543;
    return $dateTime->format('d-m-') . $thai_year;
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Transfer ID is required']);
    exit;
}

$transferId = intval($_GET['id']);

try {
    // Fetch transfer header information
    $headerQuery = "SELECT h.transfer_header_id, h.bill_number, h.transfer_date, 
                           u.Username as username, h.updated_at
                    FROM h_transfer h
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
    $itemsQuery = "SELECT d.product_id, p.name_th as product_name_th, p.name_en as product_name_en, 
                          d.quantity, p.unit,
                          l1.location as from_location, l2.location as to_location
                   FROM d_transfer d
                   JOIN products p ON d.product_id = p.product_id
                   JOIN locations l1 ON d.from_location_id = l1.location_id
                   JOIN locations l2 ON d.to_location_id = l2.location_id
                   WHERE d.transfer_header_id = :id";
    
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bindParam(':id', $transferId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates
    $header['transfer_date'] = formatFullDate($header['transfer_date']);
    $header['updated_at'] = date('Y-m-d H:i:s', strtotime($header['updated_at']));

    // Calculate total quantity
    $totalQuantity = array_sum(array_column($items, 'quantity'));

    // Prepare the response
    $response = [
        'transfer_id' => $header['transfer_header_id'],
        'bill_number' => $header['bill_number'],
        'transfer_date' => $header['transfer_date'],
        'username' => $header['username'],
        'updated_at' => $header['updated_at'],
        'item_count' => count($items),
        'total_quantity' => $totalQuantity,
        'items' => $items
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    exception_handler($e);
}
?>