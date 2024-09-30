<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../config/connect.php';

header('Content-Type: application/json');

$productId = $_POST['productId'] ?? '';
$startProductId = $_POST['startProductId'] ?? '';
$endProductId = $_POST['endProductId'] ?? '';
$endDate = $_POST['endDate'] ?? date('Y-m-d');
$productId = $_POST['productId'] ?? '';
if (empty($productId)) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}
try {
    // คำนวณวันที่เริ่มต้นของเดือน
    $startDate = date('Y-m-01', strtotime($endDate));

    // คำนวณยอดยกมาก่อนวันที่เริ่มต้น
    $openingBalanceQuery = "
    SELECT SUM(
        CASE 
            WHEN r.received_date IS NOT NULL THEN dr.quantity 
            WHEN i.issue_date IS NOT NULL THEN -di.quantity
            WHEN t.transfer_date IS NOT NULL AND t.from_location_id = l.location_id THEN -dt.quantity
            WHEN t.transfer_date IS NOT NULL AND t.to_location_id = l.location_id THEN dt.quantity
            ELSE 0 
        END
    ) as opening_balance
    FROM products p
    CROSS JOIN locations l
    LEFT JOIN d_receive dr ON p.product_id = dr.product_id AND dr.location_id = l.location_id
    LEFT JOIN h_receive r ON dr.receive_header_id = r.receive_header_id AND r.received_date < :startDate
    LEFT JOIN d_issue di ON p.product_id = di.product_id AND di.location_id = l.location_id
    LEFT JOIN h_issue i ON di.issue_header_id = i.issue_header_id AND i.issue_date < :startDate
    LEFT JOIN d_transfer dt ON p.product_id = dt.product_id
    LEFT JOIN h_transfer t ON dt.transfer_header_id = t.transfer_header_id AND t.transfer_date < :startDate
    WHERE p.product_id = :productId
    ";

    $stmt = $conn->prepare($openingBalanceQuery);
    $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->execute();

    $openingBalance = $stmt->fetchColumn() ?: 0;

    $query = "
    WITH daily_movements AS (
        SELECT 
            DATE(COALESCE(r.received_date, i.issue_date, t.transfer_date)) as date,
            SUM(CASE WHEN r.received_date IS NOT NULL THEN dr.quantity ELSE 0 END) as receive,
            SUM(CASE WHEN i.issue_date IS NOT NULL THEN di.quantity ELSE 0 END) as issue,
            SUM(CASE 
                WHEN t.transfer_date IS NOT NULL AND t.from_location_id = l.location_id THEN dt.quantity 
                WHEN t.transfer_date IS NOT NULL AND t.to_location_id = l.location_id THEN -dt.quantity
                ELSE 0 
            END) as transfer
        FROM 
            products p
        CROSS JOIN locations l
        LEFT JOIN d_receive dr ON p.product_id = dr.product_id AND dr.location_id = l.location_id
        LEFT JOIN h_receive r ON dr.receive_header_id = r.receive_header_id
        LEFT JOIN d_issue di ON p.product_id = di.product_id AND di.location_id = l.location_id
        LEFT JOIN h_issue i ON di.issue_header_id = i.issue_header_id
        LEFT JOIN d_transfer dt ON p.product_id = dt.product_id
        LEFT JOIN h_transfer t ON dt.transfer_header_id = t.transfer_header_id
        WHERE 
            p.product_id = :productId
            AND DATE(COALESCE(r.received_date, i.issue_date, t.transfer_date)) BETWEEN :startDate AND :endDate
        GROUP BY 
            DATE(COALESCE(r.received_date, i.issue_date, t.transfer_date))
    )
    SELECT 
        date,
        receive,
        issue,
        transfer,
        @running_total := @running_total + (receive - issue + transfer) as balance
    FROM 
        daily_movements,
        (SELECT @running_total := :openingBalance) as init
    ORDER BY 
        date
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->bindParam(':openingBalance', $openingBalance, PDO::PARAM_STR);
    $stmt->execute();

    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // เพิ่มยอดยกมาในวันแรกของรายงาน
    array_unshift($movements, [
        'date' => $startDate,
        'receive' => 0,
        'issue' => 0,
        'transfer' => 0,
        'balance' => $openingBalance
    ]);

    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $movements
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>