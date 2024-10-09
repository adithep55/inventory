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
$location_id = isset($_POST['location_id']) ? $_POST['location_id'] : '';

// Base query
$query = "SELECT p.product_id, p.name_th, p.name_en, 
       pt.name AS product_type_name, pc.name AS product_category_name, 
       COALESCE(SUM(i.quantity), 0) AS total_quantity, p.unit, l.location, i.updated_at,
       hr.received_date, p.img, hr.bill_number
FROM products p
LEFT JOIN inventory i ON p.product_id = i.product_id
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

if (!empty($location_id)) {
    $query .= " AND i.location_id = :location_id";
    $params[':location_id'] = $location_id;
}

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

$query .= " GROUP BY p.product_id";

if (!empty($location_id)) {
    $query .= " HAVING total_quantity > 0";
} else {
    $query .= " HAVING total_quantity > 0 OR total_quantity IS NULL";
}

$query .= " ORDER BY p.product_id ASC
           LIMIT $start, $length";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->execute($params);
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

// Count total and filtered records
$countQuery = "SELECT COUNT(DISTINCT p.product_id) as total FROM products p";
$filteredQuery = $countQuery . " LEFT JOIN inventory i ON p.product_id = i.product_id WHERE 1=1";

if (!empty($location_id)) {
    $filteredQuery .= " AND (i.location_id = :location_id OR i.location_id IS NULL)";
}

if ($search !== '%') {
    $filteredQuery .= " AND (p.name_th LIKE :search OR p.name_en LIKE :search OR p.product_id LIKE :search)";
}

if (!empty($location_id)) {
    $filteredQuery .= " GROUP BY p.product_id HAVING SUM(CASE WHEN i.location_id = :location_id2 THEN i.quantity ELSE 0 END) > 0";
}

$countStmt = $conn->query($countQuery);
$totalRecords = $countStmt->fetchColumn();

$filteredStmt = $conn->prepare($filteredQuery);
if (!empty($location_id)) {
    $filteredStmt->bindValue(':location_id', $location_id, PDO::PARAM_STR);
    $filteredStmt->bindValue(':location_id2', $location_id, PDO::PARAM_STR);
}
if ($search !== '%') {
    $filteredStmt->bindValue(':search', $search, PDO::PARAM_STR);
}
$filteredStmt->execute();
$filteredRecords = $filteredStmt->rowCount();

echo json_encode(array(
    "draw" => $draw,
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($filteredRecords),
    "data" => $data
));
?>