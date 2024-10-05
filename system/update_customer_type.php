<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_customers']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $discount_rate = $_POST['discount_rate'];
    
    if ($id && $name && $discount_rate !== '') {
        $check = dd_q('SELECT COUNT(*) FROM customer_types WHERE name = ? AND type_id != ?', [$name, $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อประเภทลูกค้านี้มีอยู่แล้ว']);
            exit;
        }

        $stmt = dd_q('UPDATE customer_types SET name = ?, discount_rate = ? WHERE type_id = ?', [$name, $discount_rate, $id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขประเภทลูกค้าสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถแก้ไขประเภทลูกค้าได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>