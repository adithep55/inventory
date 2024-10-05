<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_issue', 'manage_projects']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

function dd_return($status, $message, $data = null) {
    $json = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    if ($data !== null) {
        $json['data'] = $data;
    }
    header('Content-Type: application/json');
    echo json_encode($json);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        if (isset($_GET['id'])) {
            // ดึงข้อมูลโครงการเดียว
            $project_id = $_GET['id'];
            $stmt = dd_q('SELECT p.project_id, p.project_name, p.project_description, p.start_date, p.end_date, 
                          p.user_id, CONCAT(u.fname, " ", u.lname) AS user_name
                          FROM projects p
                          LEFT JOIN users u ON p.user_id = u.UserID
                          WHERE p.project_id = ?', [$project_id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($project) {
                // Format dates
                if ($project['start_date']) {
                    $project['start_date'] = date('d/m/Y', strtotime($project['start_date']));
                }
                if ($project['end_date']) {
                    $project['end_date'] = date('d/m/Y', strtotime($project['end_date']));
                }
                dd_return(true, "ดึงข้อมูลโครงการสำเร็จ", $project);
            } else {
                dd_return(false, "ไม่พบข้อมูลโครงการ");
            }
        } else {
            // ดึงข้อมูลทุกโครงการ
            $stmt = dd_q('SELECT p.project_id, p.project_name, p.project_description, p.start_date, p.end_date, 
                          CONCAT(u.fname, " ", u.lname) AS user_name
                          FROM projects p
                          LEFT JOIN users u ON p.user_id = u.UserID
                          WHERE p.end_date >= CURDATE() OR p.end_date IS NULL 
                          ORDER BY p.start_date DESC');
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($projects) {
                // Format dates
                foreach ($projects as &$project) {
                    if ($project['start_date']) {
                        $project['start_date'] = date('d/m/Y', strtotime($project['start_date']));
                    }
                    if ($project['end_date']) {
                        $project['end_date'] = date('d/m/Y', strtotime($project['end_date']));
                    }
                }
                dd_return(true, "ดึงข้อมูลโครงการสำเร็จ", $projects);
            } else {
                dd_return(false, "ไม่พบข้อมูลโครงการ");
            }
        }
    } catch (PDOException $e) {
        dd_return(false, "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>