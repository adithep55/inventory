<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_settings']);

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// ตรวจสอบการล็อกอินและสิทธิ์
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อนใช้งาน']);
    exit;
}

function isValidPNG($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }

    $f = fopen($filePath, 'rb');
    if (!$f) {
        return false;
    }

    $header = fread($f, 8);
    fclose($f);

    return $header === "\x89PNG\r\n\x1a\n";
}
function getFileMimeType($filePath) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->file($filePath);
}

function uploadFile($file, $uploadDir, $allowedTypes, $maxFileSize, $existingFileName = null) {
    $fileName = $existingFileName ?: bin2hex(random_bytes(8)) . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $targetPath = $uploadDir . $fileName;
    
    // ตรวจสอบขนาดไฟล์
    if ($file['size'] > $maxFileSize) {
        throw new Exception("ไฟล์มีขนาดใหญ่เกินไป ขนาดสูงสุดคือ " . ($maxFileSize / 1024 / 1024) . "MB");
    }

    // ตรวจสอบประเภทไฟล์
    $mimeType = getFileMimeType($file['tmp_name']);
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ " . implode(', ', $allowedTypes));
    }

    // ตรวจสอบเพิ่มเติมสำหรับ PNG
    if ($mimeType === 'image/png') {
        if (!isValidPNG($file['tmp_name'])) {
            throw new Exception("ไฟล์ PNG ไม่ถูกต้องหรือเสียหาย");
        }
        
        // ตรวจสอบโครงสร้าง PNG เพิ่มเติม
        $pngContents = file_get_contents($file['tmp_name']);
        if (strpos($pngContents, 'IHDR') === false || strpos($pngContents, 'IEND') === false) {
            throw new Exception("โครงสร้างไฟล์ PNG ไม่ถูกต้อง");
        }
    }

    if (file_exists($targetPath)) {
        unlink($targetPath); // ลบไฟล์เดิมถ้ามีอยู่
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // ตรวจสอบอีกครั้งหลังจากย้ายไฟล์
        if ($mimeType === 'image/png' && !isValidPNG($targetPath)) {
            unlink($targetPath); // ลบไฟล์ที่ไม่ถูกต้อง
            throw new Exception("ไฟล์ PNG ไม่ถูกต้องหลังจากอัปโหลด");
        }
        return $fileName;
    }
    throw new Exception("ไม่สามารถอัปโหลดไฟล์ได้");
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
        $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $imageFields = ['logo', 'small_logo'];
        foreach ($imageFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                // ดึงชื่อไฟล์เดิมจากฐานข้อมูล
                $stmt = $conn->prepare("SELECT setting_value FROM website_settings WHERE setting_key = :key");
                $stmt->execute([':key' => $field]);
                $existingFileName = $stmt->fetchColumn();

                $fileName = uploadFile($_FILES[$field], $uploadDir, $allowedTypes, $maxFileSize, $existingFileName);
                $updateStmt->execute([
                    ':key' => $field,
                    ':value' => $fileName
                ]);
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
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>