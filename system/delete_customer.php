<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

function dd_return($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        try {
            // ตรวจสอบว่ามีการเชื่อมโยงกับตาราง h_issue หรือไม่
            $check = dd_q('SELECT COUNT(*) FROM h_issue WHERE customer_id = ?', [$id]);
            if ($check->fetchColumn() > 0) {
                dd_return('fail', 'ไม่สามารถลบข้อมูลลูกค้าได้ เนื่องจากมีประวัติการทำรายการที่เชื่อมโยงอยู่');
            }

            $stmt = dd_q('DELETE FROM customers WHERE customer_id = ?', [$id]);
            if ($stmt->rowCount() > 0) {
                dd_return('success', 'ลบข้อมูลลูกค้าสำเร็จ');
            } else {
                dd_return('fail', 'ไม่พบข้อมูลลูกค้าที่ต้องการลบ');
            }
        } catch (PDOException $e) {
            dd_return('fail', 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage());
        }
    } else {
        dd_return('fail', 'ไม่ได้ระบุ ID ของลูกค้า');
    }
} else {
    dd_return('fail', 'Invalid request');
}
?>