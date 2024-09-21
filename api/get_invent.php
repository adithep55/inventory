<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';

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
$query = "SELECT i.product_id, p.name_th, p.name_en, l.location, i.quantity, p.unit, pt.name AS product_type_name, pc.name AS product_category_name, u.Username AS created_by
          FROM inventory i
          JOIN products p ON i.product_id = p.product_id
          JOIN locations l ON i.location_id = l.location_id
          LEFT JOIN product_types pt ON p.product_type_id = pt.type_id
          LEFT JOIN product_cate pc ON pt.type_id = pc.category_id
          LEFT JOIN users u ON p.user_id = u.UserID";
$params = array();

if ($search !== '%') {
    $query .= " WHERE p.name_th LIKE :search
                OR p.name_en LIKE :search
                OR i.product_id LIKE :search
                OR l.location LIKE :search
                OR pt.name LIKE :search
                OR pc.name LIKE :search
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

$inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for response
$data = array();
foreach ($inventoryItems as $item) {
    $data[] = array(
        "checkbox" => '<label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>',
        "product_id" => $item['product_id'] ?? 'N/A',
        "name_th" => $item['name_th'] ?? 'N/A',
        "name_en" => $item['name_en'] ?? 'N/A',
        "location" => $item['location'] ?? 'N/A',
        "quantity" => $item['quantity'] ?? 'N/A',
        "unit" => $item['unit'] ?? 'N/A',
        "product_type_name" => $item['product_type_name'] ?? 'N/A',
        "product_category_name" => $item['product_category_name'] ?? 'N/A',
        "created_by" => $item['created_by'] ?? 'N/A',
        "actions" => '
    <a class="me-3" href="inventory-details.php?id=' . htmlspecialchars($item['product_id']) . '">
        <img src="../assets/img/icons/eye.svg" alt="img">
    </a>
    <a class="me-3" href="edit-inventory.php?id=' . htmlspecialchars($item['product_id']) . '">
        <img src="../assets/img/icons/edit.svg" alt="img">
    </a>
    <a class="confirm-text" href="javascript:void(0);" onclick="deleteInventoryItem(\'' . $item['product_id'] . '\')">
        <img src="../assets/img/icons/delete.svg" alt="img">
    </a>'
    );
}

// Query to count filtered records
$filteredQuery = "SELECT COUNT(*) FROM inventory i
                  JOIN products p ON i.product_id = p.product_id
                  JOIN locations l ON i.location_id = l.location_id";
$filteredParams = array();

if ($search !== '%') {
    $filteredQuery .= " WHERE p.name_th LIKE :search 
                        OR p.name_en LIKE :search 
                        OR i.product_id LIKE :search 
                        OR l.location LIKE :search";
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