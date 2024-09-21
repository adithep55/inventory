<?php
require_once '../../config/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => "Method not allowed"]);
    exit;
}

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';
$productId = isset($_POST['productId']) ? $_POST['productId'] : '';
$export = isset($_POST['export']) && $_POST['export'] === 'true';

try {
    $query = "
    (SELECT 
        h.received_date AS date,
        d.product_id,
        p.name_th AS product_name,
        'รับเข้า' AS movement_type,
        d.quantity,
        l.location,
        u.Username AS user,
        h.bill_number
    FROM 
        d_receive d
    JOIN h_receive h ON d.receive_header_id = h.receive_header_id
    JOIN products p ON d.product_id = p.product_id
    JOIN locations l ON d.location_id = l.location_id
    JOIN users u ON h.user_id = u.UserID)

    UNION ALL

    (SELECT 
        h.issue_date AS date,
        d.product_id,
        p.name_th AS product_name,
        'เบิกออก' AS movement_type,
        d.quantity,
        l.location,
        u.Username AS user,
        h.bill_number
    FROM 
        d_issue d
    JOIN h_issue h ON d.issue_header_id = h.issue_header_id
    JOIN products p ON d.product_id = p.product_id
    JOIN locations l ON d.location_id = l.location_id
    JOIN users u ON h.user_id = u.UserID)

    UNION ALL

    (SELECT 
        h.transfer_date AS date,
        d.product_id,
        p.name_th AS product_name,
        'โอนย้าย' AS movement_type,
        d.quantity,
        CONCAT(l_from.location, ' -> ', l_to.location) AS location,
        u.Username AS user,
        h.bill_number
    FROM 
        d_transfer d
    JOIN h_transfer h ON d.transfer_header_id = h.transfer_header_id
    JOIN products p ON d.product_id = p.product_id
    JOIN locations l_from ON h.from_location_id = l_from.location_id
    JOIN locations l_to ON h.to_location_id = l_to.location_id
    JOIN users u ON h.user_id = u.UserID)
    ";

    $whereConditions = [];
    $params = [];

    if (!empty($startDate)) {
        $whereConditions[] = "date >= :startDate";
        $params[':startDate'] = $startDate;
    }

    if (!empty($endDate)) {
        $whereConditions[] = "date <= :endDate";
        $params[':endDate'] = $endDate;
    }

    if (!empty($productId)) {
        $whereConditions[] = "product_id = :productId";
        $params[':productId'] = $productId;
    }

    if (!empty($whereConditions)) {
        $query = "SELECT * FROM (" . $query . ") AS combined WHERE " . implode(" AND ", $whereConditions);
    } else {
        $query = "SELECT * FROM (" . $query . ") AS combined";
    }

    $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as count_table";

    $stmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();

    $query .= " ORDER BY date DESC";

    if (!$export) {
        $query .= " LIMIT :start, :length";
    }

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    if (!$export) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($export) {
        echo json_encode(['status' => 'success', 'data' => $movements]);
    } else {
        echo json_encode([
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $movements
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>