<?php
require_once '../config/connect.php';

header('Content-Type: application/json');

$types = dd_q("SELECT type_id, name FROM product_types ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($types);
?>