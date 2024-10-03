<?php
error_reporting(0);
ini_set('display_errors', 1);

require_once '../../config/connect.php';

header('Content-Type: application/json');

function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '../../debug.log');
}

function formatThaiDate($date) {
    $timestamp = strtotime($date);
    $thai_year = date('Y', $timestamp) + 543;
    return date('d-m-', $timestamp) . $thai_year;
}

try {
    logMessage("Received POST data: " . json_encode($_POST));
    
    $productId = $_POST['productId'] ?? '';
    $endDate = $_POST['endDate'] ?? date('Y-m-d');
    $startDate = date('Y-m-01', strtotime($endDate));

    if (empty($productId)) {
        throw new Exception('Product ID is required');
    }

    logMessage("Processing report for Product ID: $productId, End Date: $endDate, Start Date: $startDate");

    // Calculate opening balance
    $openingBalanceQuery = "
    SELECT 
        COALESCE(SUM(
            CASE 
                WHEN type = 'receive' THEN quantity
                WHEN type = 'issue' THEN -quantity
                WHEN type = 'transfer_in' THEN quantity
                WHEN type = 'transfer_out' THEN -quantity
                ELSE 0
            END
        ), 0) AS opening_balance
    FROM (
        SELECT 'receive' as type, dr.quantity
        FROM d_receive dr
        JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
        WHERE dr.product_id = :productId AND hr.received_date < :startDate

        UNION ALL

        SELECT 'issue' as type, di.quantity
        FROM d_issue di
        JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
        WHERE di.product_id = :productId AND hi.issue_date < :startDate

        UNION ALL

        SELECT 
            CASE 
                WHEN ht.from_location_id = l.location_id THEN 'transfer_out'
                WHEN ht.to_location_id = l.location_id THEN 'transfer_in'
            END as type,
            dt.quantity
        FROM d_transfer dt
        JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
        CROSS JOIN locations l
        WHERE dt.product_id = :productId AND ht.transfer_date < :startDate
    ) AS combined_movements
    ";

    $stmt = dd_q($openingBalanceQuery, [':productId' => $productId, ':startDate' => $startDate]);
    $openingBalance = $stmt->fetchColumn() ?: 0;

    logMessage("Opening Balance: " . $openingBalance);
    
    $movementsQuery = "
    SELECT 
        DATE(movement_date) as date,
        type,
        SUM(quantity) as quantity,
        from_location,
        to_location
    FROM (
        SELECT hr.received_date as movement_date, 'receive' as type, dr.quantity, NULL as from_location, dr.location_id as to_location
        FROM d_receive dr
        JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
        WHERE dr.product_id = :productId AND hr.received_date BETWEEN :startDate AND :endDate

        UNION ALL

        SELECT hi.issue_date as movement_date, 'issue' as type, di.quantity, di.location_id as from_location, NULL as to_location
        FROM d_issue di
        JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
        WHERE di.product_id = :productId AND hi.issue_date BETWEEN :startDate AND :endDate

        UNION ALL

        SELECT 
            ht.transfer_date as movement_date, 
            'transfer' as type,
            dt.quantity, 
            ht.from_location_id as from_location,
            ht.to_location_id as to_location
        FROM d_transfer dt
        JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
        WHERE dt.product_id = :productId AND ht.transfer_date BETWEEN :startDate AND :endDate
    ) AS combined_movements
    GROUP BY DATE(movement_date), type, from_location, to_location
    ORDER BY DATE(movement_date), type
    ";

    $stmt = dd_q($movementsQuery, [':productId' => $productId, ':startDate' => $startDate, ':endDate' => $endDate]);
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logMessage("Raw movements data: " . json_encode($movements));

    // ดึงข้อมูลคลังสินค้าทั้งหมด
    $locationsQuery = "SELECT location_id, location FROM locations";
    $stmt = dd_q($locationsQuery);
    $locations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// ดึงข้อมูลหน่วยของสินค้า
$productUnitQuery = "SELECT unit FROM products WHERE product_id = :productId";
$stmt = dd_q($productUnitQuery, [':productId' => $productId]);
$productUnit = $stmt->fetchColumn();


   $processedMovements = [];
    $runningBalance = $openingBalance;

    foreach ($movements as $movement) {
        $quantity = floatval($movement['quantity']);
        $transferText = '';
        
        switch($movement['type']) {
            case 'receive':
                $runningBalance += $quantity;
                break;
            case 'issue':
                $runningBalance -= $quantity;
                break;
            case 'transfer':
                $fromLocation = $locations[$movement['from_location']] ?? 'ไม่ทราบ';
                $toLocation = $locations[$movement['to_location']] ?? 'ไม่ทราบ';
                $transferText = $quantity . ' (' . $fromLocation . ' --> ' . $toLocation . ')';
                break;
        }

        $processedMovements[] = [
            'date' => formatThaiDate($movement['date']),
            'receive' => $movement['type'] == 'receive' ? $quantity : 0,
            'issue' => $movement['type'] == 'issue' ? $quantity : 0,
            'transfer' => $transferText,
            'balance' => $runningBalance,
            'unit' => $productUnit,
            'entry_type' => 'transaction'
        ];
    }

    array_unshift($processedMovements, [
        'date' => formatThaiDate($startDate),
        'receive' => null,
        'issue' => null,
        'transfer' => null,
        'balance' => floatval($openingBalance),
        'unit' => $productUnit,
        'entry_type' => 'opening_balance'
    ]);

    logMessage("Final processed movements: " . json_encode($processedMovements));

    $response = [
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => count($processedMovements),
        "recordsFiltered" => count($processedMovements),
        "data" => $processedMovements
    ];

    echo json_encode($response);

} catch (Exception $e) {
    logMessage('Error in get_product_movement.php: ' . $e->getMessage());
    echo json_encode([
        'error' => $e->getMessage(),
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}
?>