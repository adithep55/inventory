<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_products']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    
    if ($id) {
        // ตรวจสอบว่ามีสินค้าที่เชื่อมโยงกับประเภทย่อยนี้หรือไม่
        $check = dd_q('SELECT COUNT(*) FROM products WHERE product_type_id = ?', [$id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบประเภทย่อยได้ เนื่องจากมีสินค้าที่เชื่อมโยงอยู่']);
            exit;
        }

        $stmt = dd_q('DELETE FROM product_cate WHERE category_id = ?', [$id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'ลบประเภทย่อยสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถลบประเภทย่อยได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ไม่ได้ระบุ ID ของประเภทย่อย']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>