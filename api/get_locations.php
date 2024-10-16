<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_receiving' , 'manage_issue' , 'manage_inventory', 'manage_reports']);

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
        $stmt = dd_q('SELECT location_id, location FROM locations ORDER BY location');
        $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($locations) {
            dd_return(true, "ดึงข้อมูลสถานที่สำเร็จ", $locations);
        } else {
            dd_return(false, "ไม่พบข้อมูลสถานที่");
        }
    } catch (PDOException $e) {
        dd_return(false, "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>