<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_users']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($user_id && $username && $fname && $lname && $role) {
        if (preg_match('/[\p{Thai}]/u', $username)) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อผู้ใช้รองรับแค่ภาษาอังกฤษและตัวเลขเท่านั้น']);
            exit;
        }
        try {
            // เริ่ม transaction
            $conn->beginTransaction();

            // อัปเดตข้อมูลผู้ใช้
            $stmt = dd_q('UPDATE users SET Username = ?, fname = ?, lname = ?, RoleID = ? WHERE UserID = ?', 
                         [$username, $fname, $lname, $role, $user_id]);

            $session_destroyed = false;

            // หากมีการกรอกรหัสผ่านใหม่
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $password_stmt = dd_q('UPDATE users SET Password = ? WHERE UserID = ?', 
                                      [$hashed_password, $user_id]);
                
                // ตรวจสอบว่าผู้ใช้ที่ถูกอัปเดตเป็นผู้ใช้ปัจจุบันหรือไม่
                if ($user_id == $_SESSION['UserID']) {
                    // Destroy session
                    session_destroy();
                    $session_destroyed = true;
                }
            }

            // Commit transaction
            $conn->commit();

            $response = ['status' => 'success', 'message' => 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว'];
            if ($session_destroyed) {
                $response['session_destroyed'] = true;
            }
            echo json_encode($response);
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