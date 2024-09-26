<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : 'get_all_users';
$for_user_list = isset($_GET['for_user_list']) && $_GET['for_user_list'] === 'true';
$for_project_dropdown = isset($_GET['for_project_dropdown']) && $_GET['for_project_dropdown'] === 'true';

if ($action === 'get_single_user') {
    $userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($userId > 0) {
        $query = "SELECT u.UserID, u.Username, u.fname, u.lname, u.RoleID, r.RoleName, u.img
                  FROM users u 
                  LEFT JOIN roles r ON u.RoleID = r.RoleID 
                  WHERE u.UserID = :userId";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(['status' => 'success', 'data' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'รหัสผู้ใช้ไม่ถูกต้อง']);
    }
} elseif ($for_project_dropdown) {
    // สำหรับ dropdown ในหน้า Project
    $query = "SELECT UserID, CONCAT(fname, ' ', lname) AS full_name FROM users ORDER BY fname, lname";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $users]);
} else {
    // สำหรับหน้า User List หรือการดึงข้อมูลทั้งหมด
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? '%' . $_POST['search']['value'] . '%' : '%';

    $countQuery = "SELECT COUNT(*) as total FROM users";
    $stmt = $conn->query($countQuery);
    $totalRecords = $stmt->fetchColumn();

    $query = "SELECT u.UserID, u.Username, u.fname, u.lname, r.RoleName, u.img
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

    $query .= " ORDER BY u.UserID DESC LIMIT :start, :length";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($users as $user) {
        $data[] = array(
            "UserID" => $user['UserID'],
            "Username" => $user['Username'],
            "fname" => $user['fname'],
            "lname" => $user['lname'],
            "full_name" => $user['fname'] . ' ' . $user['lname'],
            "RoleName" => $user['RoleName'],
            "img" => $user['img'] ?? 'user.png'
        );
    }

    $filteredQuery = "SELECT COUNT(*) FROM users u LEFT JOIN roles r ON u.RoleID = r.RoleID";
    $filteredParams = $params;

    if ($search !== '%') {
        $filteredQuery .= " WHERE u.Username LIKE :search
                            OR u.fname LIKE :search
                            OR u.lname LIKE :search
                            OR r.RoleName LIKE :search";
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
}
?>