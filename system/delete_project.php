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
        // รับ ID ของโครงการที่ต้องการลบ
        $project_id = $_POST['project_id'];  // เปลี่ยนจาก projectId เป็น project_id

        if (empty($project_id)) {
            dd_return(false, "ไม่ได้ระบุ ID ของโครงการที่ต้องการลบ");
        }

        // ลบข้อมูลจากฐานข้อมูล
        $stmt = dd_q('DELETE FROM projects WHERE project_id = ?', [$project_id]);

        if ($stmt->rowCount() > 0) {
            dd_return(true, "ลบข้อมูลโครงการเรียบร้อยแล้ว");
        } else {
            dd_return(false, "ไม่พบโครงการที่ต้องการลบ");
        }
    } catch (PDOException $e) {
        dd_return(false, "เกิดข้อผิดพลาดในการลบข้อมูล: " . $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>