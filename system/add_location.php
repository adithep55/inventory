<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $location_id = $_POST['locationId'];
    $location = $_POST['locationName'];
    
    if ($location_id && $location) {
        // ตรวจสอบรหัสคลังซ้ำ
        $check_id = dd_q('SELECT COUNT(*) FROM locations WHERE location_id = ?', [$location_id]);
        if ($check_id->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'รหัสคลังนี้มีอยู่แล้ว']);
            exit;
        }

        // ตรวจสอบชื่อคลังซ้ำ
        $check_name = dd_q('SELECT COUNT(*) FROM locations WHERE location = ?', [$location]);
        if ($check_name->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อตำแหน่งคลังนี้มีอยู่แล้ว']);
            exit;
        }

        // เพิ่มข้อมูลใหม่
        $stmt = dd_q('INSERT INTO locations (location_id, location) VALUES (?, ?)', [$location_id, $location]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มตำแหน่งคลังสำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มตำแหน่งคลังได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>