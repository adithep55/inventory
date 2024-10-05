<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_receiving', 'manage_products', 'manage_reports']);

header('Content-Type: application/json');

// Handle DataTables parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? '%' . $_POST['search']['value'] . '%' : '%';

// Query to count total records
$countQuery = "SELECT COUNT(*) as total FROM products";
$stmt = $conn->query($countQuery);
$totalRecords = $stmt->fetchColumn();

// Main query with search
$query = "SELECT p.*, pc.name AS product_category_name, pt.name AS product_type_name, u.Username AS created_by
FROM products p
LEFT JOIN product_cate pc ON p.product_type_id = pc.category_id
LEFT JOIN product_types pt ON pc.product_category_id = pt.type_id
LEFT JOIN users u ON p.user_id = u.UserID";
$params = array();

if ($search !== '%') {
    $query .= " WHERE p.name_th LIKE :search
                OR p.name_en LIKE :search
                OR p.product_id LIKE :search
                OR pt.name LIKE :search
                OR pc.name LIKE :search
                OR p.size LIKE :search
                OR p.unit LIKE :search
                OR u.Username LIKE :search";
    $params[':search'] = $search;
}

// Calculate LIMIT and OFFSET
$query .= " LIMIT $start, $length";

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

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for response
$data = array();
foreach ($products as $product) {
    $data[] = array(
        "checkbox" => '<label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>',
        "image" => $product['img'] ? '../img/product/' . $product['img'] : '../img/product/default-image.jpg',
        "name_th" => $product['name_th'] ?? 'N/A',
        "name_en" => $product['name_en'] ?? 'N/A',
        "product_id" => $product['product_id'] ?? 'N/A',
        "product_type_name" => $product['product_type_name'] ?? 'N/A',
        "product_category_name" => $product['product_category_name'] ?? 'N/A',
        "size" => $product['size'] ?? 'N/A',
        "unit" => $product['unit'] ?? 'N/A',
        "low_level" => $product['low_level'] ?? 'N/A',
        "created_by" => $product['created_by'] ?? 'N/A',
        "actions" => '
    <a class="me-3" href="product_details.php?id=' . htmlspecialchars($product['product_id']) . '">
        <img src="../assets/img/icons/eye.svg" alt="img">
    </a>
    <a class="me-3" href="edit_product.php?id=' . htmlspecialchars($product['product_id']) . '">
        <img src="../assets/img/icons/edit.svg" alt="img">
    </a>
    <a class="confirm-text" href="javascript:void(0);" onclick="deleteProduct(\'' . $product['product_id'] . '\')">
        <img src="../assets/img/icons/delete.svg" alt="img">
    </a>'
    );
}

// Query to count filtered records
$filteredQuery = "SELECT COUNT(*) FROM products";
$filteredParams = array();

if ($search !== '%') {
    $filteredQuery .= " WHERE name_th LIKE :search OR product_id LIKE :search";
    $filteredParams[':search'] = $search;
}

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