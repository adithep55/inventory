<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
header('Content-Type: application/json');

// ตรวจสอบการล็อกอิน (แต่ไม่ต้องตรวจสอบสิทธิ์ manage_settings)
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// ดึงข้อมูลการตั้งค่า
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("SELECT setting_key, setting_value FROM website_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // เพิ่มเติม URL สำหรับรูปภาพ
        if (isset($settings['logo'])) {
            $settings['logo_url'] = '../assets/img/' . $settings['logo'];
        }
        if (isset($settings['small_logo'])) {
            $settings['small_logo_url'] = '../assets/img/' . $settings['small_logo'];
        }

        echo json_encode([
            'status' => 'success', 
            'data' => $settings,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid request method. Use GET to retrieve settings.'
    ]);
}
?>