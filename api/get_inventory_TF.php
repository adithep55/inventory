<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_transfers']);

header('Content-Type: application/json');

// Handle DataTables parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? '%' . $_POST['search']['value'] . '%' : '%';

// Query to count total records
$countQuery = "SELECT COUNT(DISTINCT p.product_id) as total FROM products p 
               INNER JOIN inventory i ON p.product_id = i.product_id 
               WHERE i.quantity > 0";
$stmt = $conn->query($countQuery);
$totalRecords = $stmt->fetchColumn();

// Main query with search
$query = "SELECT p.product_id, p.name_th, p.name_en, 
       pt.name AS product_type_name, pc.name AS product_category_name, 
       p.unit, p.img,
       (SELECT COUNT(DISTINCT location_id) FROM inventory WHERE product_id = p.product_id AND quantity > 0) AS available_locations,
       (SELECT GROUP_CONCAT(
            CONCAT(
                '{\"id\":', location_id, 
                ',\"name\":\"', (SELECT location FROM locations WHERE location_id = inventory.location_id), 
                '\",\"quantity\":', quantity, 
                '}'
            ) SEPARATOR ','
        ) 
        FROM inventory 
        WHERE product_id = p.product_id AND quantity > 0) AS locations
FROM products p
INNER JOIN inventory i ON p.product_id = i.product_id
LEFT JOIN product_cate pc ON p.product_type_id = pc.category_id
LEFT JOIN product_types pt ON pc.product_category_id = pt.type_id
WHERE i.quantity > 0";
$params = array();

if ($search !== '%') {
    $query .= " AND (p.name_th LIKE :search
                OR p.name_en LIKE :search
                OR p.product_id LIKE :search
                OR pt.name LIKE :search
                OR pc.name LIKE :search)";
    $params[':search'] = $search;
}

$query .= " GROUP BY p.product_id
           ORDER BY p.name_th ASC
           LIMIT $start, $length";

// Prepare statement
$stmt = $conn->prepare($query);

// Execute the query
if (!$stmt->execute($params)) {
    error_log("Query execution failed: " . print_r($stmt->errorInfo(), true));
    echo json_encode(array("error" => "Database query failed", "details" => $stmt->errorInfo()));
    exit;
}

$inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for response
$data = array();
foreach ($inventoryItems as $item) {
    $locations = json_decode('[' . $item['locations'] . ']', true) ?? [];
    $data[] = array(
        "product_id" => $item['product_id'] ?? 'N/A',
        "name_th" => $item['name_th'] ?? 'N/A',
        "name_en" => $item['name_en'] ?? 'N/A',
        "product_type_name" => $item['product_type_name'] ?: 'N/A',
        "product_category_name" => $item['product_category_name'] ?: 'N/A',
        "unit" => $item['unit'] ?? 'N/A',
        "image_url" => $item['img'] ? '../img/product/' . $item['img'] : '../assets/img/product.png',
        "available_locations" => $item['available_locations'] ?? 0,
        "locations" => $locations
    );
}

// Query to count filtered records
$filteredQuery = str_replace('SELECT p.product_id', 'SELECT COUNT(DISTINCT p.product_id)', $query);
$filteredQuery = preg_replace('/ORDER BY.*$/i', '', $filteredQuery);
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