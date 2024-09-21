<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    
    if ($id) {
        // ตรวจสอบว่ามีลูกค้าที่ใช้ประเภทนี้อยู่หรือไม่
        $check = dd_q('SELECT COUNT(*) FROM customers WHERE customer_type_id = ?', [$id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบประเภทลูกค้าได้ เนื่องจากมีลูกค้าที่ใช้ประเภทนี้อยู่']);
            exit;
        }

        $stmt = dd_q('DELETE FROM customer_types WHERE type_id = ?', [$id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'ลบประเภทลูกค้าสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบประเภทลูกค้าได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ไม่ได้ระบุ ID ของประเภทลูกค้า']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>