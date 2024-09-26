<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    echo json_encode(['error' => "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!"]);
    exit;
}

try {
    $data = [];

    // 1. จำนวนสินค้าทั้งหมด
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $data['total_products'] = $stmt->fetchColumn();

 // 2. จำนวนสินค้าในคลัง (รวมทุกรายการ)
$stmt = $conn->prepare("SELECT SUM(quantity) as total FROM inventory");
$stmt->execute();
$data['total_inventory'] = $stmt->fetchColumn();

// เพิ่มข้อมูลจำนวนรายการสินค้าที่มีในคลัง (ไม่ซ้ำกัน)
$stmt = $conn->prepare("SELECT COUNT(DISTINCT product_id) as total FROM inventory WHERE quantity > 0");
$stmt->execute();
$data['total_inventory_items'] = $stmt->fetchColumn();

    // 3. จำนวนการเบิกสินค้า
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM h_issue");
    $stmt->execute();
    $data['total_issues'] = $stmt->fetchColumn();

    // 4. จำนวนการรับสินค้า
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM h_receive");
    $stmt->execute();
    $data['total_receives'] = $stmt->fetchColumn();

// 5. สถิติการเบิกและรับสินค้า (12 เดือนล่าสุด)
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(t.date, '%Y-%m') as month,
        SUM(CASE WHEN t.type = 'issue' THEN t.count ELSE 0 END) as issue_count,
        SUM(CASE WHEN t.type = 'receive' THEN t.count ELSE 0 END) as receive_count
    FROM (
        SELECT 
            issue_date as date,
            'issue' as type,
            COUNT(*) as count
        FROM h_issue
        WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
        UNION ALL
        SELECT 
            received_date as date,
            'receive' as type,
            COUNT(*) as count
        FROM h_receive
        WHERE received_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(received_date, '%Y-%m')
    ) t
    GROUP BY DATE_FORMAT(t.date, '%Y-%m')
    ORDER BY month
");
$stmt->execute();
$data['inventory_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. สินค้าที่มีการเคลื่อนไหวล่าสุด (เรียงตามรหัสสินค้า)
$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.name_th,
        COALESCE(SUM(i.quantity), 0) as quantity,
        CASE 
            WHEN COALESCE(SUM(i.quantity), 0) <= p.low_level THEN 'ใกล้หมด'
            ELSE 'ปกติ'
        END as status
    FROM products p
    LEFT JOIN inventory i ON p.product_id = i.product_id
    GROUP BY p.product_id, p.name_th, p.low_level
    ORDER BY p.product_id ASC
    LIMIT 5
");
$stmt->execute();
$data['recent_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. รายการเบิก-รับล่าสุด
    $stmt = $conn->prepare("
        (SELECT 
            h.bill_number,
            'เบิก' as type,
            h.issue_date as date,
            d.product_id,
            d.quantity,
            u.Username as user
        FROM h_issue h
        JOIN d_issue d ON h.issue_header_id = d.issue_header_id
        JOIN users u ON h.user_id = u.UserID
        ORDER BY h.issue_date DESC
        LIMIT 5)
        UNION ALL
        (SELECT 
            h.bill_number,
            'รับ' as type,
            h.received_date as date,
            d.product_id,
            d.quantity,
            u.Username as user
        FROM h_receive h
        JOIN d_receive d ON h.receive_header_id = d.receive_header_id
        JOIN users u ON h.user_id = u.UserID
        ORDER BY h.received_date DESC
        LIMIT 5)
        ORDER BY date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $data['recent_transactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>