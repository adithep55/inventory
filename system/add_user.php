<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_users']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $username = $_POST['username'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($username && $fname && $lname && $role && $password) {
        if (preg_match('/[\p{Thai}]/u', $username)) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อผู้ใช้รองรับแค่ภาษาอังกฤษและตัวเลขเท่านั้น']);
            exit;
        }
        try {
            // เริ่ม transaction
            $conn->beginTransaction();

            // ตรวจสอบว่ามีชื่อผู้ใช้นี้อยู่แล้วหรือไม่
            $check_stmt = dd_q('SELECT UserID FROM users WHERE Username = ?', [$username]);
            if ($check_stmt->rowCount() > 0) {
                echo json_encode(['status' => 'fail', 'message' => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว']);
                exit;
            }

            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // เพิ่มผู้ใช้ใหม่
            $stmt = dd_q('INSERT INTO users (Username, fname, lname, RoleID, Password) VALUES (?, ?, ?, ?, ?)', 
                         [$username, $fname, $lname, $role, $hashed_password]);

            // Commit transaction
            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            // Rollback ในกรณีที่เกิดข้อผิดพลาด
            $conn->rollBack();
            echo json_encode(['status' => 'fail', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>