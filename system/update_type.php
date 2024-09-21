<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    
    if ($id && $name) {
        $check = dd_q('SELECT COUNT(*) FROM product_types WHERE name = ? AND type_id != ?', [$name, $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อหมวดหมู่นี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = dd_q('UPDATE product_types SET name = ? WHERE type_id = ?', [$name, $id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขหมวดหมู่หลักสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถแก้ไขหมวดหมู่หลักได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>