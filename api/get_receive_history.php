<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_receiving']);

header('Content-Type: application/json');
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

function formatThaiDate($date) {
    $date = new DateTime($date);
    $year = $date->format('Y') + 543;
    return $date->format('d-m-') . $year;
}

try {
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

    // Base query
    $query = "SELECT h.receive_header_id, h.bill_number, h.received_date, 
                     u.Username AS user_name, 
                     CASE 
                         WHEN h.is_opening_balance = 1 THEN 'ยอดยกมา'
                         ELSE 'ปกติ'
                     END AS status,
                     (SELECT COUNT(*) FROM d_receive WHERE receive_header_id = h.receive_header_id) as item_count
              FROM h_receive h
              JOIN users u ON h.user_id = u.UserID";

    // Search condition
    $searchCondition = "";
    $params = [];
    if (!empty($search)) {
        $searchCondition = " WHERE h.bill_number LIKE :search OR h.received_date LIKE :search OR u.Username LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Count total records
    $countQuery = "SELECT COUNT(*) FROM ($query$searchCondition) as count_table";
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();

    // Fetch data
    $query .= "$searchCondition ORDER BY CAST(SUBSTRING(h.bill_number, 2) AS UNSIGNED) DESC, h.bill_number DESC LIMIT :start, :length";
    $stmt = $conn->prepare($query);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $receives = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch items for each receive and format date
    foreach ($receives as &$receive) {
        $receive['received_date'] = formatThaiDate($receive['received_date']);

        $itemQuery = "SELECT p.name_th, d.quantity, p.unit
                      FROM d_receive d
                      JOIN products p ON d.product_id = p.product_id
                      WHERE d.receive_header_id = :receive_header_id
                      LIMIT 3";
        $itemStmt = $conn->prepare($itemQuery);
        $itemStmt->execute([':receive_header_id' => $receive['receive_header_id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $itemsText = implode(', ', array_map(function($item) {
            return "{$item['name_th']} ({$item['quantity']} {$item['unit']})";
        }, $items));

        if ($receive['item_count'] > 3) {
            $itemsText .= ' และอื่นๆ';
        }
        $receive['items'] = $itemsText;
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $receives
    ]);

} catch (Exception $e) {
    exception_handler($e);
}
?>