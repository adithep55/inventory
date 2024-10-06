<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_receiving']);
function exception_handler($exception) {
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

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Receive ID is required');
    }

    $receiveId = intval($_GET['id']);

    $headerQuery = "SELECT h.receive_header_id, h.bill_number, DATE_FORMAT(h.received_date, '%Y-%m-%d') AS formatted_received_date, 
                           u.Username AS user_name, u.fname, u.lname, h.is_opening_balance,
                           CASE 
                               WHEN h.is_opening_balance = 1 THEN 'ยอดยกมา'
                               ELSE 'ปกติ'
                           END AS status,
                           h.updated_at
                    FROM h_receive h
                    JOIN users u ON h.user_id = u.UserID
                    WHERE h.receive_header_id = :receive_id";

    $stmt = $conn->prepare($headerQuery);
    $stmt->bindParam(':receive_id', $receiveId, PDO::PARAM_INT);
    $stmt->execute();
    $headerDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$headerDetails) {
        throw new Exception('Receive record not found');
    }

    $itemsQuery = "SELECT p.product_id, p.name_th AS product_name, d.quantity, p.unit, l.location AS location_name, d.location_id
                   FROM d_receive d
                   JOIN products p ON d.product_id = p.product_id
                   JOIN locations l ON d.location_id = l.location_id
                   WHERE d.receive_header_id = :receive_id";

    $stmt = $conn->prepare($itemsQuery);
    $stmt->bindParam(':receive_id', $receiveId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'data' => [
            'receive_header_id' => $headerDetails['receive_header_id'],
            'bill_number' => $headerDetails['bill_number'],
            'received_date' => $headerDetails['formatted_received_date'],
            'user_name' => $headerDetails['user_name'],
            'full_name' => $headerDetails['fname'] . ' ' . $headerDetails['lname'],
            'status' => $headerDetails['status'],
            'is_opening_balance' => $headerDetails['is_opening_balance'],
            'updated_at' => $headerDetails['updated_at'],
            'items' => $items
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    exception_handler($e);
}
?>