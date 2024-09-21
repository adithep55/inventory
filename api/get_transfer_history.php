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
$countQuery = "SELECT COUNT(DISTINCT h.transfer_header_id) as total FROM h_transfer h";
$stmt = $conn->query($countQuery);
$totalRecords = $stmt->fetchColumn();

// Main query with search
$query = "SELECT 
    h.transfer_header_id,
    h.bill_number,
    h.transfer_date,
    l1.location as from_location,
    l2.location as to_location,
    u.Username as username,
    COUNT(d.transfer_detail_id) as item_count,
    SUM(d.quantity) as total_quantity
FROM 
    h_transfer h
JOIN 
    d_transfer d ON h.transfer_header_id = d.transfer_header_id
JOIN 
    locations l1 ON h.from_location_id = l1.location_id
JOIN 
    locations l2 ON h.to_location_id = l2.location_id
JOIN 
    users u ON h.user_id = u.UserID";

$params = array();

if ($search !== '%') {
    $query .= " WHERE h.bill_number LIKE :search
                OR h.transfer_date LIKE :search
                OR l1.location LIKE :search
                OR l2.location LIKE :search
                OR u.Username LIKE :search";
    $params[':search'] = $search;
}

$query .= " GROUP BY h.transfer_header_id";
$query .= " ORDER BY h.transfer_date DESC LIMIT $start, $length";

// Prepare statement
$stmt = $conn->prepare($query);

// Execute the query
if (!$stmt->execute($params)) {
    error_log("Query execution failed: " . print_r($stmt->errorInfo(), true));
    echo json_encode(array("error" => "Database query failed"));
    exit;
}

$transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for response
$data = array();
foreach ($transfers as $transfer) {
    $data[] = array(
        "checkbox" => '<label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>',
        "bill_number" => $transfer['bill_number'],
        "transfer_date" => $transfer['transfer_date'],
        "from_location" => $transfer['from_location'],
        "to_location" => $transfer['to_location'],
        "username" => $transfer['username'],
        "item_count" => $transfer['item_count'],
        "total_quantity" => $transfer['total_quantity'],
        "actions" => '
    <a class="me-3" href="transfer_details.php?id=' . htmlspecialchars($transfer['transfer_header_id']) . '">
        <img src="../assets/img/icons/eye.svg" alt="img">
   <a class="me-3" href="edit_transfer.php?id=' . htmlspecialchars($transfer['transfer_header_id']) . '">
                <img src="../assets/img/icons/edit.svg" alt="Edit">
            </a>
                   <a class="me-3 delete-transfer" href="javascript:void(0);" data-id="' . htmlspecialchars($transfer['transfer_header_id']) . '">
                <img src="../assets/img/icons/delete.svg" alt="Delete">
            </a>'

    );
}

// Query to count filtered records
$filteredQuery = "SELECT COUNT(DISTINCT h.transfer_header_id) FROM h_transfer h
                  JOIN d_transfer d ON h.transfer_header_id = d.transfer_header_id
                  JOIN locations l1 ON h.from_location_id = l1.location_id
                  JOIN locations l2 ON h.to_location_id = l2.location_id
                  JOIN users u ON h.user_id = u.UserID";
$filteredParams = array();

if ($search !== '%') {
    $filteredQuery .= " WHERE h.bill_number LIKE :search
                        OR h.transfer_date LIKE :search
                        OR l1.location LIKE :search
                        OR l2.location LIKE :search
                        OR u.Username LIKE :search";
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