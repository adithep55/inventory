<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_settings']);

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// ตรวจสอบการล็อกอินและสิทธิ์
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['status' => 'เกิดข้อผิดพลาด', 'message' => 'กรุณาเข้าสู่ระบบก่อนใช้งาน']);
    exit;
}
requirePermission(['manage_settings']);

// ฟังก์ชันสำหรับอัปโหลดไฟล์
function uploadFile($file, $uploadDir, $existingFileName = null) {
    $fileName = $existingFileName ?: basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (file_exists($targetPath)) {
        unlink($targetPath); // ลบไฟล์เดิมถ้ามีอยู่
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $updateStmt = $conn->prepare("UPDATE website_settings SET setting_value = :value WHERE setting_key = :key");

        // อัปเดตข้อมูลทั่วไป
        $textFields = ['company_name', 'company_address', 'company_contact'];
        foreach ($textFields as $field) {
            if (isset($_POST[$field])) {
                $updateStmt->execute([
                    ':key' => $field,
                    ':value' => $_POST[$field]
                ]);
            }
        }

// อัปโหลดและอัปเดตโลโก้
$uploadDir = '../assets/img/';
$imageFields = ['logo', 'small_logo'];
foreach ($imageFields as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
        // ดึงชื่อไฟล์เดิมจากฐานข้อมูล
        $stmt = $conn->prepare("SELECT setting_value FROM website_settings WHERE setting_key = :key");
        $stmt->execute([':key' => $field]);
        $existingFileName = $stmt->fetchColumn();

        $fileName = uploadFile($_FILES[$field], $uploadDir, $existingFileName);
        if ($fileName) {
            $updateStmt->execute([
                ':key' => $field,
                ':value' => $fileName
            ]);
        } else {
            throw new Exception("Failed to upload {$field}");
        }
    }
}

        // บันทึกข้อมูลผู้ที่ทำการอัปเดต
        $updateStmt->execute([
            ':key' => 'last_updated_by',
            ':value' => $_SESSION['UserID']
        ]);
        $updateStmt->execute([
            ':key' => 'last_updated_at',
            ':value' => date('Y-m-d H:i:s')
        ]);

        $conn->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => 'อัปเดตการตั้งค่าเรียบร้อยแล้ว',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>