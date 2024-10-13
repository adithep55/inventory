<?php

require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_inventory']);

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
    exit;
}

$productId = $_POST['product_id'];

$query = "
SELECT 
    p.*, 
    pt.name AS product_type_name, 
    pc.name AS product_category_name,
    l.location_id,
    l.location,
    i.quantity AS current_quantity,
    i.updated_at AS inventory_updated_at,
    COALESCE(hr.received_date, ht.transfer_date) AS last_received_date
FROM 
    products p
LEFT JOIN product_cate pc ON p.product_type_id = pc.category_id
LEFT JOIN product_types pt ON pc.product_category_id = pt.type_id
CROSS JOIN locations l
LEFT JOIN inventory i ON p.product_id = i.product_id AND i.location_id = l.location_id
LEFT JOIN (
    SELECT 
        dr.product_id,
        dr.location_id, 
        MAX(hr.received_date) AS received_date
    FROM 
        h_receive hr
    JOIN d_receive dr ON hr.receive_header_id = dr.receive_header_id
    WHERE dr.product_id = :product_id
    GROUP BY dr.product_id, dr.location_id
) hr ON p.product_id = hr.product_id AND l.location_id = hr.location_id
LEFT JOIN (
    SELECT 
        dt.product_id,
        dt.to_location_id AS location_id,
        MAX(ht.transfer_date) AS transfer_date
    FROM 
        h_transfer ht
    JOIN d_transfer dt ON ht.transfer_header_id = dt.transfer_header_id
    WHERE dt.product_id = :product_id
    GROUP BY dt.product_id, dt.to_location_id
) ht ON p.product_id = ht.product_id AND l.location_id = ht.location_id
WHERE p.product_id = :product_id
HAVING current_quantity > 0 
ORDER BY last_received_date DESC, i.updated_at DESC
";

$result = dd_q($query, [':product_id' => $productId]);
$productData = $result->fetchAll(PDO::FETCH_ASSOC);

if (empty($productData)) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

$product = $productData[0];
$imageUrl = $product['img'] ? '../img/product/' . $product['img'] : null;

$inventoryData = array_map(function($item) {
    return [
        'location' => $item['location'],
        'quantity' => $item['current_quantity'] ?? 0,
        'updated_at' => $item['inventory_updated_at'] ? date('Y-m-d H:i:s', strtotime($item['inventory_updated_at'])) : 'ไม่ระบุ',
        'last_received_date' => $item['last_received_date'] ? date('Y-m-d', strtotime($item['last_received_date'])) : 'ไม่ระบุ'
    ];
}, $productData);

$response = [
    'status' => 'success',
    'data' => [
        'product_id' => $product['product_id'],
        'name_th' => $product['name_th'],
        'name_en' => $product['name_en'],
        'product_type_name' => $product['product_type_name'],
        'product_category_name' => $product['product_category_name'],
        'size' => $product['size'],
        'unit' => $product['unit'],
        'low_level' => $product['low_level'],
        'image_url' => $imageUrl,
        'inventory' => $inventoryData
    ]
];
echo json_encode($response);
?>