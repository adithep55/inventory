<?php
require_once '../config/connect.php';

header('Content-Type: application/json');

// รับค่าพารามิเตอร์ type_name จาก query string
$type_id = isset($_GET['type_id']) ? $_GET['type_id'] : '';

if ($type_id) {
    $stmt = dd_q("
        SELECT 
            c.category_id, 
            c.name 
        FROM 
            product_cate c
        WHERE 
            c.product_category_id = ?
        ORDER BY 
            c.name
    ", [$type_id]);

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} else {
    echo json_encode([]);
}
?>