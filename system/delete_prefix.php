<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_customers']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    
    if ($id) {
        // ตรวจสอบว่ามีลูกค้าที่ใช้คำนำหน้านี้อยู่หรือไม่
        $check = dd_q('SELECT COUNT(*) FROM customers WHERE prefix_id = ?', [$id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบคำนำหน้าได้ เนื่องจากมีลูกค้าที่ใช้คำนำหน้านี้อยู่']);
            exit;
        }

        $stmt = dd_q('DELETE FROM prefixes WHERE prefix_id = ?', [$id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'ลบคำนำหน้าสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบคำนำหน้าได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ไม่ได้ระบุ ID ของคำนำหน้า']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>