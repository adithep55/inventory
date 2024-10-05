<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_inventory']);

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Handle DataTables parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? '%' . $_POST['search']['value'] . '%' : '%';

// Query to count total records
$countQuery = "SELECT COUNT(*) as total FROM inventory";
$stmt = $conn->query($countQuery);
$totalRecords = $stmt->fetchColumn();

// Main query with search
$query = "SELECT p.product_id, p.name_th, p.name_en, 
       pt.name AS product_type_name, pc.name AS product_category_name, 
       SUM(i.quantity) AS total_quantity, p.unit, l.location, i.updated_at,
       hr.received_date, p.img, hr.bill_number
FROM inventory i
INNER JOIN products p ON i.product_id = p.product_id
LEFT JOIN product_types pt ON p.product_type_id = pt.type_id
LEFT JOIN product_cate pc ON pt.type_id = pc.category_id
LEFT JOIN locations l ON i.location_id = l.location_id
LEFT JOIN (
    SELECT dr.product_id, MIN(hr.received_date) as received_date, dr.location_id, MIN(hr.bill_number) as bill_number
    FROM h_receive hr
    INNER JOIN d_receive dr ON hr.receive_header_id = dr.receive_header_id
    GROUP BY dr.product_id, dr.location_id
) hr ON p.product_id = hr.product_id AND i.location_id = hr.location_id
WHERE 1=1";
$params = array();

if ($search !== '%') {
    $query .= " AND (p.name_th LIKE :search
                OR p.name_en LIKE :search
                OR p.product_id LIKE :search
                OR pt.name LIKE :search
                OR pc.name LIKE :search
                OR l.location LIKE :search
                OR hr.bill_number LIKE :search)";
    $params[':search'] = $search;
}

$query .= " GROUP BY p.product_id
           ORDER BY p.name_th ASC
           LIMIT $start, $length";

// Prepare statement
$stmt = $conn->prepare($query);

// Debug: Log the query and parameters
error_log("Query: " . $query);
error_log("Params: " . print_r($params, true));

// Execute the query
if (!$stmt->execute($params)) {
    error_log("Query execution failed: " . print_r($stmt->errorInfo(), true));
    echo json_encode(array("error" => "Database query failed"));
    exit;
}

$inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for response
$data = array();
foreach ($inventoryItems as $item) {
    $data[] = array(
        "product_id" => $item['product_id'] ?? 'N/A',
        "name_th" => $item['name_th'] ?? 'N/A',
        "name_en" => $item['name_en'] ?? 'N/A',
        "product_type_name" => $item['product_type_name'] ?? 'N/A',
        "product_category_name" => $item['product_category_name'] ?? 'N/A',
        "total_quantity" => $item['total_quantity'] ?? '0',
        "unit" => $item['unit'] ?? 'N/A',
        "location" => $item['location'] ?? 'N/A',
        "received_date" => $item['received_date'] ? date('Y-m-d', strtotime($item['received_date'])) : 'N/A',
        "updated_at" => $item['updated_at'] ? date('Y-m-d H:i:s', strtotime($item['updated_at'])) : 'N/A',
        "image_url" => $item['img'] ? '../img/product/' . $item['img'] : '../assets/img/product.png',
        "bill_number" => $item['bill_number'] ?? 'N/A',
        "actions" => '
            <a class="btn btn-sm btn-warning view-details me-2" data-product-id="' . htmlspecialchars($item['product_id']) . '">
                <i class="fas fa-eye"></i>
            </a>'
    );
}

// Query to count filtered records
$filteredQuery = "SELECT COUNT(*) FROM (
    $query
) AS filtered_query";
$filteredParams = $params;

$stmt = $conn->prepare($filteredQuery);
$stmt->execute($filteredParams);
$filteredRecords = $stmt->fetchColumn();

echo json_encode(array(
    "draw" => $draw,
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($filteredRecords),
    "data" => $data
));
?>