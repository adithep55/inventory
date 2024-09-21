<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    
    if ($id) {
        // ตรวจสอบว่ามีประเภทย่อยที่เชื่อมโยงกับหมวดหมู่หลักนี้หรือไม่
        $check = dd_q('SELECT COUNT(*) FROM product_cate WHERE product_category_id = ?', [$id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบหมวดหมู่หลักได้ เนื่องจากมีประเภทย่อยที่เชื่อมโยงอยู่']);
            exit;
        }

        $stmt = dd_q('DELETE FROM product_types WHERE type_id = ?', [$id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'ลบหมวดหมู่หลักสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบหมวดหมู่หลักได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ไม่ได้ระบุ ID ของหมวดหมู่หลัก']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>