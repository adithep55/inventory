<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_products']);

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
    exit;
}

$productId = $_GET['id'];

try {
    $query = "SELECT p.*, pt.name AS product_type_name, pc.name AS product_category_name, u.Username AS created_by
    FROM products p
    LEFT JOIN product_cate pc ON p.product_type_id = pc.category_id
    LEFT JOIN product_types pt ON pc.product_category_id = pt.type_id
    LEFT JOIN users u ON p.user_id = u.UserID
    WHERE p.product_id = :product_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(['status' => 'success', 'data' => $product]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>