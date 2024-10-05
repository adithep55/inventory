<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_products']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $name = $_POST['name'];
    $product_category_id = $_POST['product_category_id'];
    
    if ($name && $product_category_id) {
        $check = dd_q('SELECT COUNT(*) FROM product_cate WHERE name = ? AND product_category_id = ?', [$name, $product_category_id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ประเภทย่อยนี้มีอยู่แล้วในหมวดหมู่นี้']);
            exit;
        }

        $stmt = dd_q('INSERT INTO product_cate (name, product_category_id) VALUES (?, ?)', [$name, $product_category_id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มประเภทย่อยสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มประเภทย่อยได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>