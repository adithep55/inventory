<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';

function dd_return($status, $message, $data = null) {
    $json = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    if ($data !== null) {
        $json['data'] = $data;
    }
    header('Content-Type: application/json');
    echo json_encode($json);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // รับข้อมูลจากฟอร์ม
        $project_id = $_POST['projectId'];
        $project_name = $_POST['projectName'];
        $project_description = $_POST['projectDescription'];
        $start_date = $_POST['startDate'];
        $end_date = $_POST['endDate'];
        $user_id = $_POST['userId'];

        // ตรวจสอบข้อมูลที่จำเป็น
        if (empty($project_id) || empty($project_name) || empty($start_date) || empty($end_date)) {
            dd_return(false, "กรุณากรอกข้อมูลให้ครบถ้วน");
        }

        // อัปเดตข้อมูลในฐานข้อมูล
        $stmt = dd_q('UPDATE projects SET project_name = ?, project_description = ?, start_date = ?, end_date = ?, user_id = ? WHERE project_id = ?',
                     [$project_name, $project_description, $start_date, $end_date, $user_id, $project_id]);

        if ($stmt->rowCount() > 0) {
            dd_return(true, "อัปเดตข้อมูลโครงการเรียบร้อยแล้ว");
        } else {
            dd_return(false, "ไม่มีการเปลี่ยนแปลงข้อมูล หรือไม่พบโครงการที่ต้องการอัปเดต");
        }
    } catch (PDOException $e) {
        dd_return(false, "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>