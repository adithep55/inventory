<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_issue']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!"]);
    exit;
}

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

try {
    // สร้าง query หลัก
    $query = "SELECT p.product_id, p.name_th, p.name_en, p.unit, p.img,
    GROUP_CONCAT(CONCAT(l.location_id, ':', l.location, ':', COALESCE(i.quantity, 0)) SEPARATOR '|') as locations
    FROM products p
    CROSS JOIN locations l
    LEFT JOIN inventory i ON p.product_id = i.product_id AND l.location_id = i.location_id
    WHERE EXISTS (
        SELECT 1 FROM inventory i2
        WHERE i2.product_id = p.product_id AND i2.quantity > 0
    )";
    $params = [];

    // เพิ่มเงื่อนไขการค้นหา
    if (!empty($search)) {
        $query .= " AND (p.product_id LIKE :search OR p.name_th LIKE :search OR p.name_en LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $query .= " GROUP BY p.product_id";

    // Query สำหรับนับจำนวนทั้งหมด
    $countQuery = "SELECT COUNT(DISTINCT p.product_id) 
                   FROM products p
                   WHERE EXISTS (
                       SELECT 1 FROM inventory i
                       WHERE i.product_id = p.product_id AND i.quantity > 0
                   )";
    if (!empty($search)) {
        $countQuery .= " AND (p.product_id LIKE :search OR p.name_th LIKE :search OR p.name_en LIKE :search)";
    }
    $stmt = $conn->prepare($countQuery);
    if ($stmt === false) {
        throw new Exception("Failed to prepare count query: " . print_r($conn->errorInfo(), true));
    }
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute count query: " . print_r($stmt->errorInfo(), true));
    }
    $totalRecords = $stmt->fetchColumn();

    // เพิ่ม LIMIT และ OFFSET สำหรับ pagination
    $query .= " LIMIT :length OFFSET :start";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare main query: " . print_r($conn->errorInfo(), true));
    }
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute main query: " . print_r($stmt->errorInfo(), true));
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // แปลงข้อมูลรูปภาพและจัดการกับข้อมูลคลังสินค้า
    foreach ($products as &$product) {
        $product['image'] = $product['img'] ? '../img/product/' . $product['img'] : '../assets/img/product.png';
        $locations = explode('|', $product['locations']);
        $product['locations'] = [];
        foreach ($locations as $location) {
            $locationData = explode(':', $location);
            if (count($locationData) == 3) {
                list($id, $name, $quantity) = $locationData;
                $quantity = (int)$quantity;
                if ($quantity > 0) {  // เพิ่มเงื่อนไขนี้เพื่อแสดงเฉพาะคลังที่มีสินค้า
                    $product['locations'][] = [
                        'id' => $id,
                        'name' => $name,
                        'quantity' => $quantity
                    ];
                }
            }
        }
        unset($product['img']);
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $products
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>