<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_inventory']);

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ฟังก์ชันสำหรับส่งคืน JSON response
function return_json($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// บันทึก error ลงใน log file
function log_error($message) {
    error_log(date('[Y-m-d H:i:s] ') . "Delete Location Error: " . $message . "\n", 3, '../logs/error.log');
}

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['UserID'])) {
        throw new Exception('Invalid request');
    }

    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('ไม่พบข้อมูลตำแหน่งคลัง');
    }

    log_error("Attempting to delete location with ID: " . $id);

    // ตรวจสอบการใช้งานในตารางที่เกี่ยวข้อง
    $tables_to_check = ['d_issue', 'd_receive', 'h_transfer', 'inventory'];
    foreach ($tables_to_check as $table) {
        $column = ($table == 'h_transfer') ? 'from_location_id' : 'location_id';
        $query = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        log_error("Executing query: $query with parameter: $id");
        
        $check = dd_q($query, [$id]);
        if ($check === false) {
            throw new Exception("เกิดข้อผิดพลาดในการตรวจสอบการใช้งานในตาราง $table");
        }
        $count = $check->fetchColumn();
        log_error("Count for table $table: $count");
        
        if ($count > 0) {
            throw new Exception("ไม่สามารถลบตำแหน่งคลังได้เนื่องจากมีการใช้งานในตาราง $table");
        }

        // ตรวจสอบ to_location_id สำหรับตาราง h_transfer
        if ($table == 'h_transfer') {
            $query = "SELECT COUNT(*) FROM $table WHERE to_location_id = ?";
            log_error("Executing query: $query with parameter: $id");
            
            $check = dd_q($query, [$id]);
            if ($check === false) {
                throw new Exception("เกิดข้อผิดพลาดในการตรวจสอบการใช้งานใน to_location_id ของตาราง $table");
            }
            $count = $check->fetchColumn();
            log_error("Count for table $table (to_location_id): $count");
            
            if ($count > 0) {
                throw new Exception("ไม่สามารถลบตำแหน่งคลังได้เนื่องจากมีการใช้งานใน to_location_id ของตาราง $table");
            }
        }
    }

    // ดำเนินการลบตำแหน่งคลัง
    $delete_query = 'DELETE FROM locations WHERE location_id = ?';
    log_error("Executing delete query: $delete_query with parameter: $id");
    
    $stmt = dd_q($delete_query, [$id]);
    if ($stmt === false) {
        throw new Exception('ไม่สามารถลบตำแหน่งคลังได้');
    }

    log_error("Location deleted successfully");
    return_json('success', 'ลบตำแหน่งคลังสำเร็จ');

} catch (PDOException $e) {
    log_error('Database error: ' . $e->getMessage());
    return_json('fail', 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage());
} catch (Exception $e) {
    log_error('Error: ' . $e->getMessage());
    return_json('fail', $e->getMessage());
}
?>