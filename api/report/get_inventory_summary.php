<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!"]);
    exit;
}

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$subCategory = isset($_POST['subCategory']) ? $_POST['subCategory'] : '';
$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : date('Y-m-d');
$export = isset($_POST['export']) && $_POST['export'] === 'true';

try {
$query = "
WITH movements AS (
    SELECT 'receive' AS type, h.received_date AS date, d.product_id, d.location_id, d.quantity, h.bill_number
    FROM d_receive d
    JOIN h_receive h ON d.receive_header_id = h.receive_header_id
    WHERE h.received_date <= :selectedDate

    UNION ALL

    SELECT 'issue' AS type, h.issue_date AS date, d.product_id, d.location_id, -d.quantity AS quantity, h.bill_number
    FROM d_issue d
    JOIN h_issue h ON d.issue_header_id = h.issue_header_id
    WHERE h.issue_date <= :selectedDate

    UNION ALL

    SELECT 'transfer_out' AS type, h.transfer_date AS date, d.product_id, h.from_location_id AS location_id, -d.quantity AS quantity, h.bill_number
    FROM d_transfer d
    JOIN h_transfer h ON d.transfer_header_id = h.transfer_header_id
    WHERE h.transfer_date <= :selectedDate

    UNION ALL

    SELECT 'transfer_in' AS type, h.transfer_date AS date, d.product_id, h.to_location_id AS location_id, d.quantity, h.bill_number
    FROM d_transfer d
    JOIN h_transfer h ON d.transfer_header_id = h.transfer_header_id
    WHERE h.transfer_date <= :selectedDate
)
SELECT 
    p.product_id, 
    p.name_th, 
    p.name_en, 
    p.unit, 
    p.img, 
    pt.name AS category,
    l.location_id,
    l.location,
    COALESCE(SUM(m.quantity), 0) AS current_quantity,
    GROUP_CONCAT(
        CONCAT(
            '{',
            '\"type\":\"', m.type, '\",',
            '\"date\":\"', DATE_FORMAT(m.date, '%Y-%m-%d'), '\",',
            '\"quantity\":', m.quantity, ',',
            '\"bill_number\":\"', m.bill_number, '\",',
            '\"location\":\"', l.location, '\"',
            '}'
        )
    ) AS movements
FROM 
    products p
JOIN product_cate pc ON p.product_type_id = pc.category_id
JOIN product_types pt ON pc.product_category_id = pt.type_id
CROSS JOIN locations l
LEFT JOIN movements m ON p.product_id = m.product_id AND l.location_id = m.location_id
";

    $params = [':selectedDate' => $selectedDate];
    $conditions = [];

    if (!empty($search)) {
        $conditions[] = "(p.product_id LIKE :search OR p.name_th LIKE :search OR p.name_en LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if (!empty($location)) {
        $conditions[] = "l.location_id = :location";
        $params[':location'] = $location;
    }

    if (!empty($category)) {
        $conditions[] = "pt.type_id = :category";
        $params[':category'] = $category;
    }

    if (!empty($subCategory)) {
        $conditions[] = "pc.category_id = :subCategory";
        $params[':subCategory'] = $subCategory;
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " GROUP BY p.product_id, l.location_id";

    $countQuery = "SELECT COUNT(*) FROM ($query) AS counted_query";

    $stmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();

    if (!$export) {
        $query .= " LIMIT :start, :length";
    }

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    if (!$export) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedProducts = [];
    foreach ($products as $product) {
        $product['movements'] = $product['movements'] ? json_decode('[' . $product['movements'] . ']', true) : [];
        $formattedProducts[] = $product;
    }

    if ($export) {
        $exportData = [];
        foreach ($formattedProducts as $product) {
            $movementDetails = [];
            foreach ($product['movements'] as $movement) {
                $movementDetails[] = sprintf(
                    "%s: %s (%s) - จำนวน: %d",
                    $movement['date'],
                    $movement['type'],
                    $movement['bill_number'],
                    $movement['quantity']
                );
            }
            $exportData[] = [
                'รหัสสินค้า' => $product['product_id'],
                'ชื่อสินค้า (ไทย)' => $product['name_th'],
                'ชื่อสินค้า (อังกฤษ)' => $product['name_en'],
                'หมวดหมู่' => $product['category'],
                'คลังสินค้า' => $product['location'],
                'จำนวนคงเหลือ' => $product['current_quantity'],
                'หน่วย' => $product['unit'],
                'รายละเอียดการเคลื่อนไหว' => implode("\n", $movementDetails)
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $exportData, 'selectedDate' => $selectedDate]);
    } else {
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedProducts,
            "selectedDate" => $selectedDate
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}