<?php
require_once '../config/connect.php';

function return_json($status, $message, $data = null) {
    header('Content-Type: application/json');
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['UserID'])) {
        throw new Exception('Invalid request');
    }

    $old_id = $_POST['old_location_id'] ?? null;
    $new_id = $_POST['editLocationId'] ?? null;
    $location = $_POST['editLocationName'] ?? null;
    $confirm_update = $_POST['confirm_update'] ?? false;

    if (!$old_id || !$new_id || !$location) {
        throw new Exception('ข้อมูลไม่ครบถ้วน');
    }

    error_log("Updating location: old_id=$old_id, new_id=$new_id, location=$location");

    // ตรวจสอบชื่อตำแหน่งซ้ำ
    $check_name = dd_q('SELECT COUNT(*) FROM locations WHERE location = ? AND location_id != ?', [$location, $old_id]);
    if ($check_name === false) {
        throw new Exception('เกิดข้อผิดพลาดในการตรวจสอบชื่อตำแหน่งคลัง');
    }
    if ($check_name->fetchColumn() > 0) {
        throw new Exception('ชื่อตำแหน่งคลังนี้มีอยู่แล้ว');
    }

    // ตรวจสอบว่ามีการเปลี่ยนแปลง ID หรือไม่
    if ($old_id !== $new_id) {
        // ตรวจสอบว่า ID ใหม่มีอยู่แล้วหรือไม่
        $check = dd_q('SELECT COUNT(*) FROM locations WHERE location_id = ?', [$new_id]);
        if ($check === false) {
            throw new Exception('เกิดข้อผิดพลาดในการตรวจสอบรหัสคลัง');
        }
        $count = $check->fetchColumn();
        if ($count > 0) {
            throw new Exception('รหัสคลังนี้มีอยู่แล้ว');
        }

        // ตรวจสอบการใช้งานใน h_transfer
        $fk_check = dd_q("SELECT COUNT(*) FROM h_transfer WHERE from_location_id = ? OR to_location_id = ?", [$old_id, $old_id]);
        if ($fk_check === false) {
            throw new Exception("เกิดข้อผิดพลาดในการตรวจสอบการใช้งานรหัสคลังในตาราง h_transfer");
        }
        $fk_count = $fk_check->fetchColumn();
        if ($fk_count > 0 && !$confirm_update) {
            return_json('confirm', "พบ $fk_count รายการในตาราง h_transfer ที่ใช้รหัสคลังนี้ คุณต้องการอัปเดตข้อมูลทั้งหมดเป็นรหัสคลังใหม่หรือไม่?", ['count' => $fk_count]);
        }

        // ถ้าผู้ใช้ยืนยันการอัปเดตหรือไม่มีข้อมูลที่เกี่ยวข้อง
        if ($confirm_update || $fk_count == 0) {
            // อัปเดตข้อมูลใน h_transfer ถ้าจำเป็น
            if ($fk_count > 0) {
                $update_transfer = dd_q("UPDATE h_transfer SET from_location_id = CASE WHEN from_location_id = ? THEN ? ELSE from_location_id END, to_location_id = CASE WHEN to_location_id = ? THEN ? ELSE to_location_id END WHERE from_location_id = ? OR to_location_id = ?", [$old_id, $new_id, $old_id, $new_id, $old_id, $old_id]);
                if ($update_transfer === false) {
                    throw new Exception("ไม่สามารถอัปเดตข้อมูลในตาราง h_transfer ได้");
                }
            }

            // อัพเดทรหัสคลังและชื่อ
            $stmt = dd_q('UPDATE locations SET location_id = ?, location = ? WHERE location_id = ?', [$new_id, $location, $old_id]);
        } else {
            throw new Exception("ไม่สามารถเปลี่ยนรหัสคลังได้เนื่องจากมีการใช้งานในตาราง h_transfer");
        }
    } else {
        // อัพเดทเฉพาะชื่อ
        $stmt = dd_q('UPDATE locations SET location = ? WHERE location_id = ?', [$location, $old_id]);
    }

    if ($stmt === false) {
        throw new Exception('ไม่สามารถแก้ไขตำแหน่งคลังได้');
    }

    error_log("Update successful");
    return_json('success', 'แก้ไขตำแหน่งคลังสำเร็จ');

} catch (PDOException $e) {
    error_log('Database error in update_location.php: ' . $e->getMessage());
    return_json('fail', 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage());
} catch (Exception $e) {
    error_log('Error in update_location.php: ' . $e->getMessage());
    return_json('fail', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
}
?>