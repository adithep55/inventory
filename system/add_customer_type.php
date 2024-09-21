<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $name = $_POST['name'];
    $discount_rate = $_POST['discount_rate'];
    
    if ($name && $discount_rate !== '') {
        $check = dd_q('SELECT COUNT(*) FROM customer_types WHERE name = ?', [$name]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อประเภทลูกค้านี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = dd_q('INSERT INTO customer_types (name, discount_rate) VALUES (?, ?)', [$name, $discount_rate]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มประเภทลูกค้าสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มประเภทลูกค้าได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>