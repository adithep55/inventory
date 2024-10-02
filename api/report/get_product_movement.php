<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../config/connect.php';

header('Content-Type: application/json');

// Function to log errors and debug information
function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '../../debug.log');
}

function formatThaiDate($date) {
    $timestamp = strtotime($date);
    $thai_year = date('Y', $timestamp) + 543;
    return date('d-m-', $timestamp) . $thai_year;
}

try {
    $productId = $_POST['productId'] ?? '';
    $endDate = $_POST['endDate'] ?? date('Y-m-d');

    if (empty($productId)) {
        throw new Exception('Product ID is required');
    }

    // Calculate the start date (first day of the month)
    $startDate = date('Y-m-01', strtotime($endDate));

    logMessage("Processing report for Product ID: $productId, End Date: $endDate, Start Date: $startDate");

    // Calculate opening balance
    $openingBalanceQuery = "
SELECT 
    COALESCE(
        (SELECT SUM(quantity) FROM d_receive dr
         JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
         WHERE dr.product_id = :productId AND hr.received_date < :startDate),
        0
    ) -
    COALESCE(
        (SELECT SUM(quantity) FROM d_issue di
         JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
         WHERE di.product_id = :productId AND hi.issue_date < :startDate),
        0
    ) +
    COALESCE(
        (SELECT SUM(
            CASE 
                WHEN ht.from_location_id = l.location_id THEN -dt.quantity
                WHEN ht.to_location_id = l.location_id THEN dt.quantity
                ELSE 0
            END
        ) FROM d_transfer dt
         JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
         CROSS JOIN locations l
         WHERE dt.product_id = :productId AND ht.transfer_date < :startDate),
        0
    ) AS opening_balance
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
        AND DATE(COALESCE(r.received_date, i.issue_date, t.transfer_date)) < :startDate
    ";

    $stmt = $conn->prepare($openingBalanceQuery);
    $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->execute();
    $openingBalance = $stmt->fetchColumn() ?: 0;

    logMessage("Opening Balance Query: " . $openingBalanceQuery);
    logMessage("Opening Balance: " . $openingBalance);

    // Calculate daily movements
    $query = "
WITH daily_movements AS (
    SELECT 
        DATE(COALESCE(r.received_date, i.issue_date, t.transfer_date)) as date,
        SUM(CASE WHEN r.received_date IS NOT NULL THEN dr.quantity ELSE 0 END) as receive,
        SUM(CASE WHEN i.issue_date IS NOT NULL THEN di.quantity ELSE 0 END) as issue,
        SUM(CASE 
            WHEN t.transfer_date IS NOT NULL AND t.from_location_id = l.location_id THEN -dt.quantity 
            WHEN t.transfer_date IS NOT NULL AND t.to_location_id = l.location_id THEN dt.quantity
            ELSE 0 
        END) as transfer,
        COALESCE(r.user_id, i.user_id, t.user_id) as user_id
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
        DATE(COALESCE(r.received_date, i.issue_date, t.transfer_date)),
        COALESCE(r.user_id, i.user_id, t.user_id)
)
SELECT 
    date,
    receive,
    issue,
    transfer,
    @running_total := @running_total + (receive - issue + transfer) as balance,
    user_id,
    CONCAT(u.fname, ' ', u.lname) as user_name
FROM 
    daily_movements
LEFT JOIN users u ON daily_movements.user_id = u.UserID,
    (SELECT @running_total := :openingBalance) as init
ORDER BY 
    date
    ";

    logMessage("Daily Movements Query: " . $query);
    logMessage("Parameters: productId=$productId, startDate=$startDate, endDate=$endDate, openingBalance=$openingBalance");

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->bindParam(':openingBalance', $openingBalance, PDO::PARAM_STR);
    $stmt->execute();

    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert string values to floats
    $movements = array_map(function($row) {
        $row['receive'] = floatval($row['receive']);
        $row['issue'] = floatval($row['issue']);
        $row['transfer'] = floatval($row['transfer']);
        $row['balance'] = floatval($row['balance']);
        return $row;
    }, $movements);

    // Add opening balance as the first row
    array_unshift($movements, [
        'date' => $startDate,
        'receive' => null,
        'issue' => null,
        'transfer' => null,
        'balance' => floatval($openingBalance),
        'user_id' => null,
        'user_name' => null,
        'entry_type' => 'opening_balance'
    ]);

    // Format dates and add entry_type
    foreach ($movements as &$movement) {
        $movement['date'] = formatThaiDate($movement['date']);
        if (!isset($movement['entry_type'])) {
            $movement['entry_type'] = 'transaction';
        }
    }

    logMessage("Final movements data: " . json_encode($movements));

    $response = [
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => count($movements),
        "recordsFiltered" => count($movements),
        "data" => $movements
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