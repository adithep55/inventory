<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleId = $_POST['id'] ?? '';

    if (empty($roleId)) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัสบทบาท']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM roles WHERE RoleID = :role_id");
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'ลบบทบาทเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบบทบาทที่ต้องการลบ']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}