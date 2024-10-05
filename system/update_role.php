<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_users']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleId = $_POST['role_id'] ?? '';
    $roleName = $_POST['role_name'] ?? '';
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

    if (empty($roleId) || empty($roleName)) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // ตรวจสอบว่ามีชื่อบทบาทซ้ำกันหรือไม่ (ยกเว้นบทบาทปัจจุบัน)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roles WHERE RoleName = :role_name AND RoleID != :role_id");
        $stmt->bindParam(':role_name', $roleName);
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('ชื่อบทบาทนี้มีอยู่แล้ว กรุณาใช้ชื่ออื่น');
        }

        $stmt = $conn->prepare("UPDATE roles SET RoleName = :role_name WHERE RoleID = :role_id");
        $stmt->bindParam(':role_name', $roleName);
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();

// Reset all permissions
$stmt = $conn->prepare("UPDATE roles SET 
    manage_products = 0, manage_receiving = 0, manage_issue = 0, manage_inventory = 0,
    manage_projects = 0, manage_customers = 0, manage_transfers = 0,
    manage_reports = 0, manage_users = 0, manage_settings = 0
    WHERE RoleID = :role_id");
$stmt->bindParam(':role_id', $roleId);
$stmt->execute();

        // Set new permissions
        foreach ($permissions as $permission) {
            $stmt = $conn->prepare("UPDATE roles SET $permission = 1 WHERE RoleID = :role_id");
            $stmt->bindParam(':role_id', $roleId);
            $stmt->execute();
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'อัปเดตบทบาทเรียบร้อยแล้ว']);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}