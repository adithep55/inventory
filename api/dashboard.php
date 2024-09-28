<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';

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
        $data = [];

        // 1. จำนวนสินค้าทั้งหมด
        $result = dd_q("SELECT COUNT(*) as total FROM products");
        $data['total_products'] = $result->fetchColumn();

        // 2. จำนวนสินค้าในคลัง (รวมทุกรายการ)
        $result = dd_q("SELECT SUM(quantity) as total FROM inventory");
        $data['total_inventory'] = $result->fetchColumn();

        // จำนวนรายการสินค้าที่มีในคลัง (ไม่ซ้ำกัน)
        $result = dd_q("SELECT COUNT(DISTINCT product_id) as total FROM inventory WHERE quantity > 0");
        $data['total_inventory_items'] = $result->fetchColumn();

        // 3. จำนวนการเบิกสินค้า
        $result = dd_q("SELECT COUNT(*) as total FROM h_issue");
        $data['total_issues'] = $result->fetchColumn();

        // 4. จำนวนการรับสินค้า
        $result = dd_q("SELECT COUNT(*) as total FROM h_receive");
        $data['total_receives'] = $result->fetchColumn();

        // 5. สถิติการเบิกและรับสินค้า (12 เดือนล่าสุด)
        $result = dd_q("
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
        $data['inventory_stats'] = $result->fetchAll(PDO::FETCH_ASSOC);

        // 6. สินค้าที่มีการเคลื่อนไหวล่าสุด (เรียงตามรหัสสินค้า)
        $result = dd_q("
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
        $data['recent_products'] = $result->fetchAll(PDO::FETCH_ASSOC);

        // 7. รายการเบิก-รับล่าสุด
        $result = dd_q("
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
        $data['recent_transactions'] = $result->fetchAll(PDO::FETCH_ASSOC);

        // 8. สินค้าใกล้หมด
        $result = dd_q("
            SELECT 
                p.product_id,
                p.name_th,
                p.low_level,
                COALESCE(SUM(i.quantity), 0) as total_quantity
            FROM products p
            LEFT JOIN inventory i ON p.product_id = i.product_id
            GROUP BY p.product_id, p.name_th, p.low_level
            HAVING total_quantity <= p.low_level OR total_quantity = 0
            ORDER BY total_quantity ASC
            LIMIT 5
        ");
        $data['low_stock_products'] = $result->fetchAll(PDO::FETCH_ASSOC);

        // 9. จำนวนสินค้าใกล้หมดทั้งหมด
        $result = dd_q("
            SELECT COUNT(*) as count
            FROM (
                SELECT 
                    p.product_id,
                    COALESCE(SUM(i.quantity), 0) as total_quantity
                FROM products p
                LEFT JOIN inventory i ON p.product_id = i.product_id
                GROUP BY p.product_id, p.low_level
                HAVING total_quantity <= p.low_level OR total_quantity = 0
            ) as low_stock
        ");
        $data['low_stock_count'] = $result->fetchColumn();

        dd_return(true, "ดึงข้อมูล Dashboard สำเร็จ", $data);
    } catch (PDOException $e) {
        dd_return(false, "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}