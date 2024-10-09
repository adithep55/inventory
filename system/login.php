<?php
require_once '../config/connect.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

function dd_return($status, $message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status ? 'success' : 'fail', 'message' => $message]);
    exit;
}

header('Content-Type: application/json; charset=utf-8;');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID'])) {
        $username = $_POST['user'];
        $password = $_POST['pass'];

        if ($username != "" && $password != "") {
            $q = dd_q("SELECT u.*, r.* FROM `users` u 
                       JOIN `roles` r ON u.RoleID = r.RoleID 
                       WHERE u.Username = ?", [$username]);

            if ($q->rowCount() == 1) {
                $user = $q->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['Password'])) {
                    $_SESSION['UserID'] = $user['UserID'];
                    $_SESSION['Username'] = $user['Username'];
                    $_SESSION['fname'] = $user['fname'];
                    $_SESSION['lname'] = $user['lname'];
                    $_SESSION['RoleID'] = $user['RoleID'];

                    // เพิ่มสิทธิ์ต่างๆ ลงใน session
                    $permissions = [
                        'manage_products', 'manage_receiving', 'manage_inventory',
                        'manage_projects', 'manage_customers', 'manage_transfers',
                        'manage_reports', 'manage_users', 'manage_settings', 'manage_issue'
                    ];
                    foreach ($permissions as $perm) {
                        $_SESSION[$perm] = $user[$perm];
                    }

                    $_SESSION['last_activity'] = time();

                    dd_return(true, "เข้าสู่ระบบสำเร็จ");
                } else {
                    dd_return(false, "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
                }
            } else {
                dd_return(false, "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
            }
        } else {
            dd_return(false, "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
        }
    } else {
        dd_return(false, "คุณได้เข้าสู่ระบบแล้ว กรุณาออกจากระบบก่อนเข้าสู่ระบบใหม่");
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>