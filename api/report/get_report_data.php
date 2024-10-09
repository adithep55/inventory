<?php
require_once '../../config/connect.php';
require_once '../../config/permission.php';
requirePermission(['manage_reports']);

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    $reportType = $_GET['reportType'] ?? '';
    $startId = $_GET['startId'] ?? '';
    $endId = $_GET['endId'] ?? '';
    $categoryId = $_GET['categoryId'] ?? '';
    $typeId = $_GET['typeId'] ?? '';

    $query = "SELECT p.product_id, p.name_th, p.name_en, p.unit
              FROM products p
              JOIN product_cate pc ON p.product_type_id = pc.category_id
              JOIN product_types pt ON pc.product_category_id = pt.type_id
              WHERE 1=1";

    $params = [];

    if ($reportType === 'product') {
        if ($startId > $endId) {
            $temp = $startId;
            $startId = $endId;
            $endId = $temp;
        }
        $query .= " AND p.product_id BETWEEN :startId AND :endId";
        $params[':startId'] = $startId;
        $params[':endId'] = $endId;
    } elseif ($reportType === 'category') {
        $query .= " AND pt.type_id = :categoryId";
        $params[':categoryId'] = $categoryId;
        if ($typeId) {
            $query .= " AND pc.category_id = :typeId";
            $params[':typeId'] = $typeId;
        }
    }

    $query .= " ORDER BY p.product_id";

    $stmt = dd_q($query, $params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $products]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>