<?php
require_once '../../config/connect.php';
require_once '../../config/permission.php';
requirePermission(['manage_reports']);

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 1);

function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '../../debug.log');
}

function formatThaiDate($date, $isOpeningBalance = false) {
    $timestamp = strtotime($date);
    $thai_year = date('Y', $timestamp) + 543;
    
    if ($isOpeningBalance) {
        // Get the last day of the previous month
        $lastDayOfPreviousMonth = date('Y-m-t', strtotime($date . ' -1 month'));
        return date('d-m-', strtotime($lastDayOfPreviousMonth)) . $thai_year;
    }
    
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

    // ดึงข้อมูลยอดยกมาแยกตามตำแหน่ง
    $openingBalanceQuery = "
SELECT 
    l.location_id,
    l.location AS location_name,
    COALESCE(SUM(
        CASE 
            WHEN type = 'receive' THEN quantity
            WHEN type = 'issue' THEN -quantity
            WHEN type = 'transfer_in' THEN quantity
            WHEN type = 'transfer_out' THEN -quantity
            ELSE 0
        END
    ), 0) AS opening_balance
FROM locations l
LEFT JOIN (
    SELECT 'receive' as type, dr.quantity, dr.location_id
    FROM d_receive dr
    JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
    WHERE dr.product_id = :productId AND hr.received_date < :startDate

    UNION ALL

    SELECT 'issue' as type, di.quantity, di.location_id
    FROM d_issue di
    JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
    WHERE di.product_id = :productId AND hi.issue_date < :startDate

    UNION ALL

    SELECT 'transfer_out' as type, dt.quantity, dt.from_location_id AS location_id
    FROM d_transfer dt
    JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
    WHERE dt.product_id = :productId AND ht.transfer_date < :startDate

    UNION ALL

    SELECT 'transfer_in' as type, dt.quantity, dt.to_location_id AS location_id
    FROM d_transfer dt
    JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
    WHERE dt.product_id = :productId AND ht.transfer_date < :startDate
) AS combined_movements ON l.location_id = combined_movements.location_id
GROUP BY l.location_id, l.location
HAVING opening_balance != 0
";

    $stmt = dd_q($openingBalanceQuery, [':productId' => $productId, ':startDate' => $startDate]);
    $openingBalances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logMessage("Opening Balances: " . json_encode($openingBalances));
    
    // ดึงข้อมูลการเคลื่อนไหวแยกตามตำแหน่ง
    $movementsQuery = "
    SELECT 
        DATE(movement_date) as date,
        type,
        quantity,
        from_location,
        to_location,
        location_id
    FROM (
        SELECT hr.received_date as movement_date, 'receive' as type, dr.quantity, NULL as from_location, dr.location_id as to_location, dr.location_id
        FROM d_receive dr
        JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
        WHERE dr.product_id = :productId AND hr.received_date BETWEEN :startDate AND :endDate
    
        UNION ALL
    
        SELECT hi.issue_date as movement_date, 'issue' as type, di.quantity, di.location_id as from_location, NULL as to_location, di.location_id
        FROM d_issue di
        JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
        WHERE di.product_id = :productId AND hi.issue_date BETWEEN :startDate AND :endDate
    
        UNION ALL
    
        SELECT 
            ht.transfer_date as movement_date, 
            'transfer_out' as type,
            dt.quantity, 
            dt.from_location_id as from_location,
            dt.to_location_id as to_location,
            dt.from_location_id as location_id
        FROM d_transfer dt
        JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
        WHERE dt.product_id = :productId AND ht.transfer_date BETWEEN :startDate AND :endDate
    
        UNION ALL
    
        SELECT 
            ht.transfer_date as movement_date, 
            'transfer_in' as type,
            dt.quantity, 
            dt.from_location_id as from_location,
            dt.to_location_id as to_location,
            dt.to_location_id as location_id
        FROM d_transfer dt
        JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
        WHERE dt.product_id = :productId AND ht.transfer_date BETWEEN :startDate AND :endDate
    ) AS combined_movements
    ORDER BY date, 
        CASE 
            WHEN type = 'receive' THEN 1
            WHEN type = 'transfer_in' THEN 2
            WHEN type = 'issue' THEN 3
            WHEN type = 'transfer_out' THEN 4
            ELSE 5
        END
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
    $runningBalances = array_column($openingBalances, 'opening_balance', 'location_id');
    $totalBalance = array_sum($runningBalances);

    // เพิ่มยอดยกมาสำหรับแต่ละตำแหน่งที่ไม่เป็น 0
    foreach ($openingBalances as $balance) {
        if (floatval($balance['opening_balance']) != 0) {
            $processedMovements[] = [
                'date' => formatThaiDate($startDate, true),
                'location' => $locations[$balance['location_id']] ?? 'ไม่ทราบ',
                'receive' => null,
                'issue' => null,
                'transfer' => null,
                'balance' => floatval($balance['opening_balance']),
                'unit' => $productUnit,
                'entry_type' => 'opening_balance'
            ];
        }
    }

    foreach ($movements as $movement) {
        $quantity = floatval($movement['quantity']);
        $locationId = $movement['location_id'];
        $locationName = $locations[$locationId] ?? 'ไม่ทราบ';
        
        switch($movement['type']) {
            case 'receive':
            case 'transfer_in':
                $runningBalances[$locationId] = ($runningBalances[$locationId] ?? 0) + $quantity;
                $totalBalance += $quantity;
                $processedMovements[] = [
                    'date' => formatThaiDate($movement['date']),
                    'location' => $locationName,
                    'receive' => $movement['type'] == 'receive' ? $quantity : null,
                    'issue' => null,
                    'transfer' => $movement['type'] == 'transfer_in' ? "+ $quantity (จาก " . ($locations[$movement['from_location']] ?? 'ไม่ทราบ') . ")" : null,
                    'balance' => $runningBalances[$locationId],
                    'unit' => $productUnit,
                    'entry_type' => 'transaction'
                ];
                break;
            case 'issue':
            case 'transfer_out':
                $runningBalances[$locationId] = ($runningBalances[$locationId] ?? 0) - $quantity;
                $totalBalance -= $quantity;
                $processedMovements[] = [
                    'date' => formatThaiDate($movement['date']),
                    'location' => $locationName,
                    'receive' => null,
                    'issue' => $movement['type'] == 'issue' ? $quantity : null,
                    'transfer' => $movement['type'] == 'transfer_out' ? "- $quantity (ไปยัง " . ($locations[$movement['to_location']] ?? 'ไม่ทราบ') . ")" : null,
                    'balance' => $runningBalances[$locationId],
                    'unit' => $productUnit,
                    'entry_type' => 'transaction'
                ];
                break;
        }
    }

    // เพิ่มแถวรวมทั้งสิ้น
    $processedMovements[] = [
        'date' => '',
        'location' => 'รวมทั้งสิ้น',
        'receive' => null,
        'issue' => null,
        'transfer' => null,
        'balance' => $totalBalance,
        'unit' => $productUnit,
        'entry_type' => 'total'
    ];

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