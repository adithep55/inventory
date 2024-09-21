<?php
require_once '../config/connect.php';

function return_json($status, $message, $data = null) {
    header('Content-Type: application/json');
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['UserID'])) {
        throw new Exception('Invalid request');
    }

    // ตรวจสอบว่าเป็นการตรวจสอบรหัสสินค้าหรือไม่
    if (isset($_POST['check_product_id'])) {
        $product_id = $_POST['product_id'];
        $check = dd_q('SELECT COUNT(*) FROM products WHERE product_id = ?', [$product_id]);
        $exists = $check->fetchColumn() > 0;
        return_json('success', '', ['exists' => $exists]);
    }

    // ถ้าไม่ใช่การตรวจสอบรหัสสินค้า ดำเนินการอัปเดตสินค้า
    $old_product_id = $_POST['oldProductId'] ?? null;  // แก้ชื่อตัวแปรให้ตรงกับ form
    $new_product_id = $_POST['product_id'] ?? null;
    $name_th = $_POST['nameTh'] ?? null;
    $name_en = $_POST['nameEn'] ?? null;
    $type_id = $_POST['type_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $size = $_POST['size'] ?? null;
    $unit = $_POST['unit'] ?? null;
    $low_level = $_POST['lowLevel'] ?? null;
    
    error_log("Old Product ID: $old_product_id");
    error_log("New Product ID: $new_product_id");
    error_log("Name TH: $name_th");
    error_log("Type ID: $type_id");
    error_log("Category ID: $category_id");
    error_log("Unit: $unit");

    if (!$old_product_id || !$new_product_id || !$name_th || !$type_id || !$category_id || !$unit) {
        throw new Exception('ข้อมูลไม่ครบถ้วน');
    }

    // ตรวจสอบรูปแบบรหัสสินค้า
    if (!preg_match('/^[a-zA-Z0-9]+$/', $new_product_id)) {
        throw new Exception('รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น');
    }

    // ตรวจสอบรหัสสินค้าซ้ำ (ถ้ามีการเปลี่ยนแปลง)
    if ($old_product_id !== $new_product_id) {
        $check = dd_q('SELECT COUNT(*) FROM products WHERE product_id = ?', [$new_product_id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception('รหัสสินค้านี้มีอยู่ในระบบแล้ว');
        }
    }

    // อัปเดตข้อมูลสินค้า
  // อัปเดตข้อมูลสินค้า
$stmt = dd_q('UPDATE products SET 
product_id = ?, name_th = ?, name_en = ?, 
product_type_id = ?, size = ?, unit = ?, low_level = ?
WHERE product_id = ?', 
[$new_product_id, $name_th, $name_en, $category_id, $size, $unit, $low_level, $old_product_id]);

$updateOccurred = false;

if ($stmt !== false && $stmt->rowCount() > 0) {
    $updateOccurred = true;
}

// อัปเดตรูปภาพถ้ามีการอัปโหลด
if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['productImage']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        throw new Exception('ไฟล์รูปภาพไม่ถูกต้อง');
    }

    $newname = $new_product_id . '.' . $ext;
    $destination = '../img/product/' . $newname;

    if (move_uploaded_file($_FILES['productImage']['tmp_name'], $destination)) {
        $stmt = dd_q('UPDATE products SET img = ? WHERE product_id = ?', [$newname, $new_product_id]);
        if ($stmt === false) {
            throw new Exception('ไม่สามารถอัปเดตข้อมูลรูปภาพได้');
        }
        $updateOccurred = true;
    } else {
        throw new Exception('ไม่สามารถอัปโหลดรูปภาพได้');
    }
}

if (!$updateOccurred) {
    throw new Exception('ไม่มีการเปลี่ยนแปลงข้อมูลสินค้า');
}

return_json('success', 'อัปเดตข้อมูลสินค้าสำเร็จ');

} catch (PDOException $e) {
    error_log('Database error in update_product.php: ' . $e->getMessage());
    return_json('fail', 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage());
} catch (Exception $e) {
    error_log('Error in update_product.php: ' . $e->getMessage());
    return_json('fail', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
}
?>