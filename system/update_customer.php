<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

function dd_return($status, $message, $data = null) {
    $response = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    try {
        $customerId = isset($_POST['customerId']) ? intval($_POST['customerId']) : null;
        $prefix = $_POST['prefix'] ?? null;
        $name = $_POST['name'] ?? null;
        $customerType = isset($_POST['customerType']) ? intval($_POST['customerType']) : null;
        $address = $_POST['address'] ?? null;
        $phoneNumber = $_POST['phoneNumber'] ?? null;
        $taxId = isset($_POST['taxId']) ? intval($_POST['taxId']) : null;
        $contactPerson = $_POST['contactPerson'] ?? null;
        $creditLimit = isset($_POST['creditLimit']) ? floatval($_POST['creditLimit']) : null;
        $creditTerms = $_POST['creditTerms'] ?? null;
        
        if (!$customerId || !$name) {
            dd_return(false, 'ข้อมูลไม่ครบถ้วน');
        }



        $stmt = dd_q('UPDATE customers SET 
                      prefix_id = ?, 
                      name = ?, 
                      customer_type_id = ?, 
                      address = ?, 
                      phone_number = ?, 
                      tax_id = ?, 
                      contact_person = ?, 
                      credit_limit = ?, 
                      credit_terms = ? 
                      WHERE customer_id = ?', 
                     [$prefix, $name, $customerType, $address, $phoneNumber, $taxId, $contactPerson, $creditLimit, $creditTerms, $customerId]);

        if ($stmt->rowCount() > 0) {
            dd_return(true, 'แก้ไขข้อมูลลูกค้าสำเร็จ');
        } else {
            dd_return(false, 'ไม่มีการเปลี่ยนแปลงข้อมูล');
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        dd_return(false, 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage());
    } catch (Exception $e) {
        error_log('General error: ' . $e->getMessage());
        dd_return(false, 'เกิดข้อผิดพลาดทั่วไป: ' . $e->getMessage());
    }
} else {
    dd_return(false, 'Invalid request');
}
?>