<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_projects']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $project_name = isset($_POST['project_name']) ? trim($_POST['project_name']) : '';
    $project_description = isset($_POST['project_description']) ? trim($_POST['project_description']) : '';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($project_name && $start_date && $end_date) {
        // ตรวจสอบชื่อโครงการซ้ำ
        $check_name = dd_q('SELECT COUNT(*) FROM projects WHERE project_name = ?', [$project_name]);
        if ($check_name->fetchColumn() > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'ชื่อโครงการนี้มีอยู่แล้ว']);
            exit;
        }

        // เพิ่มข้อมูลโครงการใหม่
        $stmt = dd_q('INSERT INTO projects (project_name, project_description, start_date, end_date, user_id) VALUES (?, ?, ?, ?, ?)', 
                     [$project_name, $project_description, $start_date, $end_date, $user_id]);
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มโครงการใหม่สำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มโครงการได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>