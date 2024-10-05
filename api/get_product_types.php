<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_reports', 'manage_products']);

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT * FROM product_types ORDER BY name");
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $types]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}