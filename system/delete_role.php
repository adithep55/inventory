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
        // ตรวจสอบว่ามีผู้ใช้ที่ใช้บทบาทนี้อยู่หรือไม่
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE RoleID = :role_id");
        $checkStmt->bindParam(':role_id', $roleId);
        $checkStmt->execute();
        $userCount = $checkStmt->fetchColumn();

        if ($userCount > 0) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'ไม่สามารถลบบทบาทนี้ได้เนื่องจากมีผู้ใช้ที่ใช้บทบาทนี้อยู่ กรุณาเปลี่ยนบทบาทของผู้ใช้เหล่านั้นก่อนทำการลบ',
                'code' => 'ROLE_IN_USE'
            ]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM roles WHERE RoleID = :role_id");
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'ลบบทบาทเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบบทบาทที่ต้องการลบ']);
        }
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        if ($errorCode == '23000') {
            echo json_encode([
                'status' => 'error', 
                'message' => 'ไม่สามารถลบบทบาทนี้ได้เนื่องจากมีการใช้งานอยู่ในระบบ กรุณาตรวจสอบและลบการเชื่อมโยงก่อนทำการลบบทบาท',
                'code' => 'INTEGRITY_CONSTRAINT_VIOLATION'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}