<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_customers']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $name = $_POST['name'];
    
    if ($name) {
        $check = dd_q('SELECT COUNT(*) FROM prefixes WHERE prefix = ?', [$name]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'คำนำหน้านี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = dd_q('INSERT INTO prefixes (prefix) VALUES (?)', [$name]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มคำนำหน้าสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มคำนำหน้าได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกคำนำหน้า']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>