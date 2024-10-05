<?php
// File: api/report/get_inventory_movements.php
require_once '../../config/connect.php';
require_once '../../config/permission.php';
requirePermission(['manage_reports']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => "Method not allowed"]);
    exit;
}

$startDate = $_POST['startDate'] ?? date('Y-m-d');
$endDate = $_POST['endDate'] ?? date('Y-m-d');
$location = $_POST['location'] ?? '';
$category = $_POST['category'] ?? '';
$subCategory = $_POST['subCategory'] ?? '';
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;

try {
    $query = "
    SELECT 
        m.date,
        m.product_id,
        p.name_th AS product_name,
        m.movement_type,
        m.quantity,
        l.location,
        m.document_number
    FROM (
        SELECT h.received_date as date, d.product_id, d.location_id, d.quantity, 'receive' as movement_type, h.bill_number as document_number
        FROM d_receive d
        JOIN h_receive h ON d.receive_header_id = h.receive_header_id
        WHERE h.received_date BETWEEN :startDate AND :endDate

        UNION ALL

        SELECT h.issue_date as date, d.product_id, d.location_id, -d.quantity, 'issue' as movement_type, h.bill_number as document_number
        FROM d_issue d
        JOIN h_issue h ON d.issue_header_id = h.issue_header_id
        WHERE h.issue_date BETWEEN :startDate AND :endDate

        UNION ALL

        SELECT h.transfer_date as date, d.product_id, h.from_location_id, -d.quantity, 'transfer_out' as movement_type, h.bill_number as document_number
        FROM d_transfer d
        JOIN h_transfer h ON d.transfer_header_id = h.transfer_header_id
        WHERE h.transfer_date BETWEEN :startDate AND :endDate

        UNION ALL

        SELECT h.transfer_date as date, d.product_id, h.to_location_id, d.quantity, 'transfer_in' as movement_type, h.bill_number as document_number
        FROM d_transfer d
        JOIN h_transfer h ON d.transfer_header_id = h.transfer_header_id
        WHERE h.transfer_date BETWEEN :startDate AND :endDate
    ) m
    JOIN products p ON m.product_id = p.product_id
    JOIN locations l ON m.location_id = l.location_id
    JOIN product_cate pc ON p.product_type_id = pc.category_id
    JOIN product_types pt ON pc.product_category_id = pt.type_id
    WHERE 1=1
    ";

    $params = [
        ':startDate' => $startDate,
        ':endDate' => $endDate
    ];

    if (!empty($location)) {
        $query .= " AND l.location_id = :location";
        $params[':location'] = $location;
    }

    if (!empty($category)) {
        $query .= " AND pt.type_id = :category";
        $params[':category'] = $category;
    }

    if (!empty($subCategory)) {
        $query .= " AND pc.category_id = :subCategory";
        $params[':subCategory'] = $subCategory;
    }

    $countQuery = "SELECT COUNT(*) FROM ($query) as counted_query";
    $stmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();

    $query .= " ORDER BY m.date DESC, m.product_id LIMIT :start, :length";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $results
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>