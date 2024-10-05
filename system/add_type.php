<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_products']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $name = $_POST['name'];
    
    if ($name) {
        $check = dd_q('SELECT COUNT(*) FROM product_types WHERE name = ?', [$name]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'หมวดหมู่นี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = dd_q('INSERT INTO product_types (name) VALUES (?)', [$name]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มหมวดหมู่หลักสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มหมวดหมู่หลักได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกชื่อหมวดหมู่']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>