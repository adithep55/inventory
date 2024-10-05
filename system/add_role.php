<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_users']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = $_POST['role_name'] ?? '';
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    $manageProducts = isset($_POST['manage_products']) ? 1 : 0;

    if (empty($roleName)) {
        echo json_encode(['status' => 'error', 'message' => 'ชื่อบทบาทไม่สามารถเว้นว่างได้']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // ตรวจสอบชื่อซ้ำ
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roles WHERE RoleName = :role_name");
        $stmt->bindParam(':role_name', $roleName);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('ชื่อบทบาทนี้มีอยู่แล้ว กรุณาใช้ชื่ออื่น');
        }
        
        $stmt = $conn->prepare("INSERT INTO roles (RoleName, manage_products) VALUES (:role_name, :manage_products)");
        $stmt->bindParam(':role_name', $roleName);
        $stmt->bindParam(':manage_products', $manageProducts);
        $stmt->execute();

        $roleId = $conn->lastInsertId();

        foreach ($permissions as $permission) {
            $stmt = $conn->prepare("UPDATE roles SET $permission = 1 WHERE RoleID = :role_id");
            $stmt->bindParam(':role_id', $roleId);
            $stmt->execute();
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มบทบาทใหม่เรียบร้อยแล้ว']);
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