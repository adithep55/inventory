<?php
function checkAccess($requiredPermissions = []) {
    global $conn;
    if (!isset($_SESSION['UserID'])) {
        return false;
    }
    
    $userId = $_SESSION['UserID'];
    $permissionQuery = "SELECT " . implode(", ", $requiredPermissions) . " 
                        FROM users u 
                        JOIN roles r ON u.RoleID = r.RoleID 
                        WHERE u.UserID = :userId";
    
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

// ฟังก์ชันสำหรับเช็คและแสดงข้อความหากไม่มีสิทธิ์
function requirePermission($requiredPermissions = []) {
    if (!checkAccess($requiredPermissions)) {
        // ถ้าเป็น AJAX request
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'เกิดข้อผิดพลาด', 'message' => 'คุณไม่มีสิทธิ์การเข้าถึง.']);
        } else {
            // ถ้าเป็น normal request ให้ redirect ไปยังหน้า error
            header("Location: /error.php?code=403");
        }
        exit;
    }
}
?>