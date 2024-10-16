<?php
require_once '../../config/connect.php';
require_once '../../config/permission.php';
requirePermission(['manage_reports']);

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    $reportType = $_GET['reportType'] ?? '';
    $productIds = $_GET['productIds'] ?? '';
    $categoryId = $_GET['categoryId'] ?? '';
    $typeId = $_GET['typeId'] ?? '';

    $query = "SELECT p.product_id, p.name_th, p.name_en, p.unit
              FROM products p
              LEFT JOIN product_cate pc ON p.product_type_id = pc.category_id
              LEFT JOIN product_types pt ON pc.product_category_id = pt.type_id
              WHERE 1=1";

    $params = [];

    if ($reportType === 'product') {
        if ($productIds === 'all') {
            // ไม่ต้องเพิ่มเงื่อนไขเพิ่มเติม เพราะต้องการทุกสินค้า
        } elseif (!empty($productIds)) {
            $productIdArray = explode(',', $productIds);
            $placeholders = implode(',', array_fill(0, count($productIdArray), '?'));
            $query .= " AND p.product_id IN ($placeholders)";
            $params = array_merge($params, $productIdArray);
        } else {
            throw new Exception('No product selected');
        }
    } elseif ($reportType === 'category') {
        $query .= " AND pt.type_id = ?";
        $params[] = $categoryId;
        if ($typeId) {
            $query .= " AND pc.category_id = ?";
            $params[] = $typeId;
        }
    } else {
        throw new Exception('Invalid report type');
    }

    $query .= " ORDER BY p.product_id";

    $stmt = dd_q($query, $params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo json_encode(['data' => [], 'message' => 'No products found']);
    } else {
        echo json_encode(['data' => $products]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>