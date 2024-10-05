<?php

require_once 'connect.php';

function checkPermission($requiredPermissions = []) {
    if (!isset($_SESSION['UserID'])) {
        return false;
    }

    $userId = $_SESSION['UserID'];
    $permissionQuery = "SELECT " . implode(", ", $requiredPermissions) . " 
                        FROM users u 
                        JOIN roles r ON u.RoleID = r.RoleID 
                        WHERE u.UserID = :userId";
    
    global $conn;
    $stmt = $conn->prepare($permissionQuery);
    $stmt->execute([':userId' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        return false;
    }
    
    foreach ($requiredPermissions as $permission) {
        if (!isset($result[$permission]) || $result[$permission] != 1) {
            return false;
        }
    }
    
    return true;
}

function requirePermission($requiredPermissions = []) {
    if (!checkPermission($requiredPermissions)) {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์การเข้าถึง.']);
            exit;
        } else {
            header("Location: /error-403");
            exit;
        }
    }
}

// สำหรับ API calls
// function apiRequirePermission($requiredPermissions = []) {
//     if (!checkPermission($requiredPermissions)) {
//         header('Content-Type: application/json');
//         echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์การเข้าถึง.']);
//         exit;
//     }
// }