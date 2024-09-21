<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $product_category_id = $_POST['product_category_id'];
    
    if ($id && $name && $product_category_id) {
        $check = dd_q('SELECT COUNT(*) FROM product_cate WHERE name = ? AND product_category_id = ? AND category_id != ?', [$name, $product_category_id, $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อประเภทย่อยนี้มีอยู่แล้วในหมวดหมู่นี้']);
            exit;
        }

        $stmt = dd_q('UPDATE product_cate SET name = ?, product_category_id = ? WHERE category_id = ?', [$name, $product_category_id, $id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขประเภทย่อยสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถแก้ไขประเภทย่อยได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>