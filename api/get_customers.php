<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_customers' , 'manage_issue']);

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
            $customerId = $_GET['id'];
            $stmt = dd_q('SELECT c.customer_id, 
                                 c.prefix_id,
                                 p.prefix,
                                 c.name,
                                 c.customer_type_id,
                                 c.phone_number, 
                                 c.address,
                                 c.tax_id,
                                 c.contact_person,
                                 c.credit_limit,
                                 c.credit_terms,
                                 ct.name AS customer_type
                          FROM customers c
                          LEFT JOIN prefixes p ON c.prefix_id = p.prefix_id
                          LEFT JOIN customer_types ct ON c.customer_type_id = ct.type_id
                          WHERE c.customer_id = ?', [$customerId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                dd_return(true, "ดึงข้อมูลลูกค้าสำเร็จ", $customer);
            } else {
                dd_return(false, "ไม่พบข้อมูลลูกค้า");
            }
        } else {
            // ดึงข้อมูลลูกค้าทั้งหมด
            $stmt = dd_q('SELECT c.customer_id, 
                                 c.prefix_id,
                                 CONCAT(COALESCE(p.prefix, ""), " ", c.name) AS full_name, 
                                 c.customer_type_id,
                                 c.phone_number, 
                                 c.address,
                                 c.tax_id,
                                 c.contact_person,
                                 c.credit_limit,
                                 c.credit_terms,
                                 ct.name AS customer_type
                          FROM customers c
                          LEFT JOIN prefixes p ON c.prefix_id = p.prefix_id
                          LEFT JOIN customer_types ct ON c.customer_type_id = ct.type_id
                          ORDER BY c.name');
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($customers) {
                dd_return(true, "ดึงข้อมูลลูกค้าสำเร็จ", $customers);
            } else {
                dd_return(false, "ไม่พบข้อมูลลูกค้า");
            }
        }
    } catch (PDOException $e) {
        dd_return(false, "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>