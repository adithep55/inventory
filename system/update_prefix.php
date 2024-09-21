<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    
    if ($id && $name) {
        $check = dd_q('SELECT COUNT(*) FROM prefixes WHERE prefix = ? AND prefix_id != ?', [$name, $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'คำนำหน้านี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = dd_q('UPDATE prefixes SET prefix = ? WHERE prefix_id = ?', [$name, $id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขคำนำหน้าสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถแก้ไขคำนำหน้าได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>