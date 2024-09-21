<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $prefix = $_POST['prefix'] ?? null;
    $name = $_POST['name'] ?? null;
    $customerType = $_POST['customerType'] ?? null;
    $address = $_POST['address'] ?? null;
    $phoneNumber = $_POST['phoneNumber'] ?? null;
    $taxId = $_POST['taxId'] ?? null;
    $contactPerson = $_POST['contactPerson'] ?? null;
    $creditLimit = $_POST['creditLimit'] ?? null;
    $creditTerms = $_POST['creditTerms'] ?? null;
    
    if ($name && $customerType) {
        // ตรวจสอบว่ามีเลขประจำตัวผู้เสียภาษีซ้ำหรือไม่ (ถ้ามีการกรอกมา)
        if ($taxId) {
            $check = dd_q('SELECT COUNT(*) FROM customers WHERE tax_id = ?', [$taxId]);
            if ($check->fetchColumn() > 0) {
                echo json_encode(['status' => 'fail', 'message' => 'เลขประจำตัวผู้เสียภาษีนี้มีอยู่ในระบบแล้ว']);
                exit;
            }
        }

        $stmt = dd_q('INSERT INTO customers (prefix_id, name, customer_type_id, address, phone_number, tax_id, contact_person, credit_limit, credit_terms, user_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                     [$prefix, $name, $customerType, $address, $phoneNumber, $taxId, $contactPerson, $creditLimit, $creditTerms, $_SESSION['UserID']]);
        
        if ($stmt) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มลูกค้าใหม่สำเร็จ']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'ไม่สามารถเพิ่มลูกค้าใหม่ได้']);
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'กรุณากรอกชื่อและประเภทลูกค้า']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>