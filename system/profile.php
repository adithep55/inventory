<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';

function dd_return($status, $message, $data = null)
{
    if ($status) {
        $json = ['status' => 'success', 'message' => $message];
        if ($data !== null) {
            $json['data'] = $data;
        }
    } else {
        $json = ['status' => 'fail', 'message' => $message];
    }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    die(json_encode($json));
}

function randtext($range) {
    $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ123456789';
    $start = rand(1, (strlen($char) - $range));
    $shuffled = str_shuffle($char);
    return substr($shuffled, $start, $range);
}

function uploadImage($file) {
    $allowed = array('jpeg', 'png', 'jpg', 'webp', 'gif');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $newfile = randtext(3) . time() . randtext(5) . "." . $ext;
        $uploadPath = "../img/profile/";
        if (move_uploaded_file($file['tmp_name'], $uploadPath . $newfile)) {
            return $newfile;
        }
    }
    return false;
}

function getRoleName($roleId) {
    $q = dd_q("SELECT RoleName FROM roles WHERE RoleID = ?", [$roleId]);
    if ($q->rowCount() == 1) {
        $row = $q->fetch(PDO::FETCH_ASSOC);
        return $row['RoleName'];
    } else {
        return 'Unknown Role';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'getUserInfo') {
    if (!isset($_SESSION['UserID'])) {
        dd_return(false, "ไม่พบข้อมูลผู้ใช้ในระบบ");
    }

    $userId = $_SESSION['UserID'];
    $q = dd_q("SELECT UserID, Username, fname, lname, img, RoleID FROM users WHERE UserID = ?", [$userId]);

    if ($q->rowCount() == 1) {
        $user = $q->fetch(PDO::FETCH_ASSOC);
        $user['role'] = getRoleName($user['RoleID']);
        unset($user['RoleID']);

        $imagePath = "../img/profile/" . $user['img'];
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            $user['img'] = "default.jpg";
        }

        dd_return(true, "โหลดข้อมูลผู้ใช้สำเร็จ", $user);
    } else {
        dd_return(false, "ไม่พบข้อมูลผู้ใช้");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'updateProfile') {
    if (!isset($_SESSION['UserID'])) {
        dd_return(false, "ไม่พบข้อมูลผู้ใช้ในระบบ");
    }

    $userId = $_SESSION['UserID'];
    $fname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
    $lname = isset($_POST['lname']) ? trim($_POST['lname']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($fname) || empty($lname)) {
        dd_return(false, "กรุณากรอกชื่อและนามสกุล");
    }

    $updateFields = [];
    $params = [];

    if ($fname !== $_SESSION['fname']) {
        $updateFields[] = "fname = ?";
        $params[] = $fname;
    }

    if ($lname !== $_SESSION['lname']) {
        $updateFields[] = "lname = ?";
        $params[] = $lname;
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateFields[] = "Password = ?";
        $params[] = $hashedPassword;
    }

    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $img_path = uploadImage($_FILES['img']);
        if ($img_path) {
            $updateFields[] = "img = ?";
            $params[] = $img_path;
        } else {
            dd_return(false, "ไม่สามารถอัปโหลดรูปภาพได้");
        }
    }

    if (empty($updateFields)) {
        dd_return(true, "ไม่มีข้อมูลที่ต้องอัพเดต");
    }

    $params[] = $userId;
    $updateQuery = "UPDATE `users` SET " . implode(", ", $updateFields) . " WHERE UserID = ?";

    try {
        $q = dd_q($updateQuery, $params);
        if ($q->rowCount() > 0) {
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            dd_return(true, "อัพเดตข้อมูลผู้ใช้สำเร็จ");
        } else {
            dd_return(false, "ไม่สามารถอัพเดตข้อมูลผู้ใช้ได้");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        dd_return(false, "เกิดข้อผิดพลาดในการอัพเดตข้อมูล");
    }
}

dd_return(false, "คำขอไม่ถูกต้อง");
?>