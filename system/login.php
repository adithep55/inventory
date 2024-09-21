<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';

function dd_return($status, $message)
{
  if ($status) {
    $json = ['status' => 'success', 'message' => $message];
    http_response_code(200);
    die(json_encode($json));
  } else {
    $json = ['status' => 'fail', 'message' => $message];
    http_response_code(200);
    die(json_encode($json));
  }
}

header('Content-Type: application/json; charset=utf-8;');


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'getUserInfo') {
    $username = $_GET['username'];
    $q = dd_q("SELECT fname, lname FROM `users` WHERE Username = ?", [$username]);
    if ($q->rowCount() == 1) {
        $user = $q->fetch(PDO::FETCH_ASSOC);
        dd_return(true, $user);
    } else {
        dd_return(false, "ไม่พบผู้ใช้");
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID'])) {
        $username = $_POST['user'];
        $password = $_POST['pass'];

        if ($username != "" && $password != "") {
            $q = dd_q("SELECT * FROM `users` WHERE Username = ?", [$username]);

            if ($q->rowCount() == 1) {
                $user = $q->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['Password'])) {
                    $_SESSION['UserID'] = $user['UserID'];
                    $_SESSION['Username'] = $user['Username'];
                    $_SESSION['fname'] = $user['fname'];
                    $_SESSION['lname'] = $user['lname'];
                    $_SESSION['RoleID'] = $user['RoleID'];

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