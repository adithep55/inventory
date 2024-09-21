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
$countQuery = "SELECT COUNT(*) as total FROM users";
$stmt = $conn->query($countQuery);
$totalRecords = $stmt->fetchColumn();

// Main query with search
$query = "SELECT u.UserID, u.Username, u.fname, u.lname, r.RoleName
          FROM users u
          LEFT JOIN roles r ON u.RoleID = r.RoleID";
$params = array();

if ($search !== '%') {
    $query .= " WHERE u.Username LIKE :search
                OR u.fname LIKE :search
                OR u.lname LIKE :search
                OR r.RoleName LIKE :search";
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

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for response
$data = array();
foreach ($users as $user) {
    $data[] = array(
        "checkbox" => '<label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>',
        "UserID" => $user['UserID'] ?? 'N/A',
        "Username" => $user['Username'] ?? 'N/A',
        "fname" => $user['fname'] ?? 'N/A',
        "lname" => $user['lname'] ?? 'N/A',
        "full_name" => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
        "RoleName" => $user['RoleName'] ?? 'N/A',
        "actions" => '
            <a class="me-3" href="user_details.php?id=' . htmlspecialchars($user['UserID']) . '">
                <img src="../assets/img/icons/eye.svg" alt="img">
            </a>
            <a class="me-3" href="edit_user.php?id=' . htmlspecialchars($user['UserID']) . '">
                <img src="../assets/img/icons/edit.svg" alt="img">
            </a>
            <a class="confirm-text" href="javascript:void(0);" onclick="deleteUser(\'' . $user['UserID'] . '\')">
                <img src="../assets/img/icons/delete.svg" alt="img">
            </a>'
    );
}

// Query to count filtered records
$filteredQuery = "SELECT COUNT(*) FROM users u LEFT JOIN roles r ON u.RoleID = r.RoleID";
$filteredParams = array();

if ($search !== '%') {
    $filteredQuery .= " WHERE u.Username LIKE :search
                        OR u.fname LIKE :search
                        OR u.lname LIKE :search
                        OR r.RoleName LIKE :search";
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