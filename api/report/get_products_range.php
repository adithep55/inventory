<?php
require_once '../../config/connect.php';

header('Content-Type: application/json');

$startId = $_GET['startId'] ?? '';
$endId = $_GET['endId'] ?? '';

try {
    $query = "SELECT product_id FROM products WHERE product_id BETWEEN :startId AND :endId ORDER BY product_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startId', $startId, PDO::PARAM_STR);
    $stmt->bindParam(':endId', $endId, PDO::PARAM_STR);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['status' => 'success', 'products' => $products]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>