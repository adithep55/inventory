<?php
require_once '../config/connect.php';

$stmt = dd_q('SELECT * FROM prefixes ORDER BY prefix');
$prefixes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $prefixes]);
?>