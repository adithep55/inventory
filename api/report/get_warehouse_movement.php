<?php
require_once '../../config/connect.php';
require_once '../../config/permission.php';
requirePermission(['manage_reports']);

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

function formatThaiDate($date) {
    $timestamp = strtotime($date);
    $thai_year = date('Y', $timestamp) + 543;
    return date('d-m-', $timestamp) . $thai_year;
}

try {
    $warehouseIds = $_GET['warehouseId'] === 'all' ? null : explode(',', $_GET['warehouseId']);
    $endDate = $_GET['endDate'] ?? date('Y-m-d');
    $startDate = date('Y-m-01', strtotime($endDate));
    $lastDayPreviousMonth = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));

    // ดึงข้อมูลคลังสินค้า
    $warehouseQuery = "SELECT location_id, location FROM locations";
    if ($warehouseIds) {
        $warehouseQuery .= " WHERE location_id IN (" . implode(',', array_fill(0, count($warehouseIds), '?')) . ")";
    }
    $stmt = $conn->prepare($warehouseQuery);
    if ($warehouseIds) {
        $stmt->execute($warehouseIds);
    } else {
        $stmt->execute();
    }
    $warehouses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $report = [];

    foreach ($warehouses as $warehouse) {
        $warehouseReport = [
            'location' => $warehouse['location'],
            'products' => []
        ];

        // ดึงข้อมูลสินค้าและยอดยกมา
        $productsQuery = "
        SELECT p.product_id, p.name_th, p.unit,
           COALESCE(SUM(
               CASE 
                   WHEN m.type = 'receive' THEN m.quantity
                   WHEN m.type = 'issue' THEN -m.quantity
                   WHEN m.type = 'transfer_in' THEN m.quantity
                   WHEN m.type = 'transfer_out' THEN -m.quantity
                   ELSE 0
               END
           ), 0) AS opening_balance
        FROM products p
        LEFT JOIN (
            SELECT 'receive' as type, dr.product_id, dr.quantity, dr.location_id
            FROM d_receive dr
            JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
            WHERE hr.received_date <= :lastDayPreviousMonth

            UNION ALL

            SELECT 'issue' as type, di.product_id, di.quantity, di.location_id
            FROM d_issue di
            JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
            WHERE hi.issue_date <= :lastDayPreviousMonth

            UNION ALL

            SELECT 'transfer_out' as type, dt.product_id, dt.quantity, dt.from_location_id AS location_id
            FROM d_transfer dt
            JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
            WHERE ht.transfer_date <= :lastDayPreviousMonth

            UNION ALL

            SELECT 'transfer_in' as type, dt.product_id, dt.quantity, dt.to_location_id AS location_id
            FROM d_transfer dt
            JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
            WHERE ht.transfer_date <= :lastDayPreviousMonth
        ) AS m ON p.product_id = m.product_id AND m.location_id = :warehouseId
        GROUP BY p.product_id, p.name_th, p.unit
        HAVING opening_balance > 0 OR p.product_id IN (
            SELECT DISTINCT product_id 
            FROM (
                SELECT product_id FROM d_receive dr JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id WHERE hr.received_date BETWEEN :startDate AND :endDate AND dr.location_id = :warehouseId
                UNION
                SELECT product_id FROM d_issue di JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id WHERE hi.issue_date BETWEEN :startDate AND :endDate AND di.location_id = :warehouseId
                UNION
                SELECT product_id FROM d_transfer dt JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id WHERE ht.transfer_date BETWEEN :startDate AND :endDate AND (dt.from_location_id = :warehouseId OR dt.to_location_id = :warehouseId)
            ) AS active_products
        )
        ORDER BY p.product_id
        ";

        $stmt = $conn->prepare($productsQuery);
        $stmt->bindValue(':lastDayPreviousMonth', $lastDayPreviousMonth);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $stmt->bindValue(':warehouseId', $warehouse['location_id']);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            // ดึงข้อมูลการเคลื่อนไหวของสินค้า
            $movementsQuery = "
            SELECT 
                DATE(movement_date) as date,
                type,
                quantity,
                COALESCE(from_location.location, '') as from_location,
                COALESCE(to_location.location, '') as to_location
            FROM (
                SELECT hr.received_date as movement_date, 'receive' as type, dr.quantity, NULL as from_location_id, dr.location_id as to_location_id
                FROM d_receive dr
                JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
                WHERE dr.product_id = :productId AND hr.received_date BETWEEN :startDate AND :endDate AND dr.location_id = :warehouseId

                UNION ALL

                SELECT hi.issue_date as movement_date, 'issue' as type, di.quantity, di.location_id as from_location_id, NULL as to_location_id
                FROM d_issue di
                JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
                WHERE di.product_id = :productId AND hi.issue_date BETWEEN :startDate AND :endDate AND di.location_id = :warehouseId

                UNION ALL

                SELECT 
                    ht.transfer_date as movement_date, 
                    CASE WHEN dt.from_location_id = :warehouseId THEN 'transfer_out' ELSE 'transfer_in' END as type,
                    dt.quantity, 
                    dt.from_location_id,
                    dt.to_location_id
                FROM d_transfer dt
                JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
                WHERE dt.product_id = :productId AND ht.transfer_date BETWEEN :startDate AND :endDate 
                AND (dt.from_location_id = :warehouseId OR dt.to_location_id = :warehouseId)
            ) AS combined_movements
            LEFT JOIN locations as from_location ON combined_movements.from_location_id = from_location.location_id
            LEFT JOIN locations as to_location ON combined_movements.to_location_id = to_location.location_id
            ORDER BY date, FIELD(type, 'receive', 'issue', 'transfer_in', 'transfer_out')
            ";

            $stmt = $conn->prepare($movementsQuery);
            $stmt->bindValue(':productId', $product['product_id']);
            $stmt->bindValue(':startDate', $startDate);
            $stmt->bindValue(':endDate', $endDate);
            $stmt->bindValue(':warehouseId', $warehouse['location_id']);
            $stmt->execute();
            $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $productMovements = [];
            $openingBalance = $product['opening_balance'];
            $totalReceive = 0;
            $totalIssue = 0;
            $totalTransfer = 0;
            $closingBalance = $openingBalance;

            // เพิ่มยอดยกมาเฉพาะเมื่อไม่เท่ากับ 0
            if ($openingBalance != 0) {
                $productMovements[] = [
                    'date' => formatThaiDate($lastDayPreviousMonth),
                    'receive' => null,
                    'issue' => null,
                    'transfer' => null,
                    'details' => 'ยอดยกมา',
                    'balance' => $openingBalance,
                    'unit' => $product['unit']
                ];
            }

            foreach ($movements as $movement) {
                $quantity = floatval($movement['quantity']);
                
                $formattedMovement = [
                    'date' => formatThaiDate($movement['date']),
                    'receive' => $movement['type'] === 'receive' ? $quantity : null,
                    'issue' => $movement['type'] === 'issue' ? $quantity : null,
                    'transfer' => in_array($movement['type'], ['transfer_in', 'transfer_out']) ? $quantity : null,
                    'details' => $movement['type'] === 'transfer_in' ? 'รับโอนจาก ' . $movement['from_location'] : 
                                 ($movement['type'] === 'transfer_out' ? 'โอนออกไป ' . $movement['to_location'] : '-'),
                    'balance' => null,
                    'unit' => $product['unit']
                ];

                if ($movement['type'] === 'receive' || $movement['type'] === 'transfer_in') {
                    $closingBalance += $quantity;
                    if ($movement['type'] === 'receive') $totalReceive += $quantity;
                } elseif ($movement['type'] === 'issue' || $movement['type'] === 'transfer_out') {
                    $closingBalance -= $quantity;
                    if ($movement['type'] === 'issue') $totalIssue += $quantity;
                }

                if (in_array($movement['type'], ['transfer_in', 'transfer_out'])) {
                    $totalTransfer += $quantity;
                }

                $formattedMovement['balance'] = $closingBalance;
                $productMovements[] = $formattedMovement;
            }

            $warehouseReport['products'][] = [
                'name' => $product['name_th'],
                'opening_balance' => $openingBalance,
                'movements' => $productMovements,
                'total_receive' => $totalReceive,
                'total_issue' => $totalIssue,
                'total_transfer' => $totalTransfer,
                'closing_balance' => $closingBalance,
                'unit' => $product['unit']
            ];
        }

        $report[] = $warehouseReport;
    }

    echo json_encode(['data' => $report]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>