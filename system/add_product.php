<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

function dd_return($status, $message) {
    echo json_encode(['status' => $status ? 'success' : 'fail', 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $product_id = $_POST['product_id'];
    $name_th = $_POST['name_th'];
    $name_en = $_POST['name_en'];
    $size = $_POST['size'];
    $category_id = $_POST['category_id'];
    $unit = $_POST['unit'];
    $low_level = $_POST['low_level'];
    $user_id = $_SESSION['UserID'];

    if ($product_id && $name_th && $name_en && $category_id && $unit && $low_level) {
        // Check if product_id already exists
        $check = dd_q('SELECT COUNT(*) FROM products WHERE product_id = ?', [$product_id]);
        if ($check->fetchColumn() > 0) {
            dd_return(false, 'รหัสสินค้านี้มีอยู่แล้ว');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $product_id)) {
            dd_return(false, "รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น");
        }

        // Handle file upload
        $img = 'product.png'; // Default image
        if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
            $allowed = ["jpg", "jpeg", "png", "gif"];
            $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $img = $product_id . '.' . $ext;
                if (!move_uploaded_file($_FILES['img']['tmp_name'], "../img/product/" . $img)) {
                    dd_return(false, "ไม่สามารถอัปโหลดไฟล์ได้");
                }
            } else {
                dd_return(false, "ไฟล์ที่อัปโหลดต้องเป็นรูปภาพเท่านั้น");
            }
        }

        // Insert new product
        $stmt = dd_q('INSERT INTO products (product_id, name_th, name_en, size, product_type_id, unit, user_id, img, low_level) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                     [$product_id, $name_th, $name_en, $size, $category_id, $unit, $user_id, $img, $low_level]);

        if ($stmt) {
            dd_return(true, 'เพิ่มสินค้าสำเร็จ');
        } else {
            dd_return(false, 'ไม่สามารถเพิ่มสินค้าได้');
        }
    } else {
        dd_return(false, 'กรุณากรอกข้อมูลให้ครบถ้วน');
    }
} else {
    dd_return(false, 'Invalid request');
}
?>