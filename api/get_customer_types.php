<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_customers']);

$stmt = dd_q('SELECT * FROM customer_types ORDER BY type_id');
$prefixes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $prefixes]);
?>