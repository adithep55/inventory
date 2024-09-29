<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config/connect.php';

function dd_return($status, $message) {
    $json = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    header('Content-Type: application/json');
    echo json_encode($json);
    exit();
}

function randtext($range) {
    $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ123456789';
    $start = rand(1, (strlen($char) - $range));
    $shuffled = str_shuffle($char);
    return substr($shuffled, $start, $range);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a product ID check request
    if (isset($_POST['check_product_id'])) {
        $product_id = $_POST['product_id'];
        $stmt = dd_q('SELECT COUNT(*) FROM products WHERE product_id = ?', [$product_id]);
        $count = $stmt->fetchColumn();
        echo json_encode(['exists' => $count > 0]);
        exit();
    }

    // Regular product addition process
    if (isset($_SESSION['UserID'])) {
        $product_id = $_POST['product_id'];
        $name_th = $_POST['name_th'];
        $name_en = $_POST['name_en'];
        $size = $_POST['size'];
        $type_id = $_POST['type_id'];
        $category_id = $_POST['category_id'];
        $unit = $_POST['unit'];
        $low_level = (int) $_POST['low_level'];
        
        // Validate product_id format
        if (!preg_match('/^[a-zA-Z0-9]+$/', $product_id)) {
            dd_return(false, "รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น");
        }
  
        // Check if all required fields are filled
        if ($product_id && $name_th && $name_en && $size && $type_id && $category_id && $unit && $low_level) {
            // Check if product_id already exists
            $check = dd_q('SELECT COUNT(*) FROM products WHERE product_id = ?', [$product_id]);
            if ($check->fetchColumn() > 0) {
                dd_return(false, "รหัสสินค้านี้มีอยู่แล้ว");
            }

            $img_path = false;  // Set to false by default

            // Upload image only if a file was uploaded
            if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
                $allowed = array('jpeg', 'png', 'jpg', 'webp', 'gif');
                $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $newfile = randtext(3) . time() . randtext(5) . "." . $ext;
                    $uploadPath = "../img/product/";
                    if (move_uploaded_file($_FILES['img']['tmp_name'], $uploadPath . $newfile)) {
                        $img_path = $newfile;
                    } else {
                        dd_return(false, "ไม่สามารถอัปโหลดไฟล์ได้");
                    }
                } else {
                    dd_return(false, "ไฟล์ที่อัปโหลดต้องเป็นรูปภาพเท่านั้น");
                }
            }

            // Prepare SQL query and values
            $sql = "INSERT INTO products (product_id, name_th, name_en, size, product_type_id, product_category_id, unit, user_id, low_level";
            $values = [$product_id, $name_th, $name_en, $size, $type_id, $category_id, $unit, $_SESSION['UserID'], $low_level];

            // Add img to query only if an image was uploaded
            if ($img_path !== false) {
                $sql .= ", img";
                $values[] = $img_path;
            }

            $sql .= ") VALUES (" . str_repeat("?,", count($values) - 1) . "?)";

            // Insert product into database
            $stmt = dd_q($sql, $values);

            if ($stmt) {
                dd_return(true, "เพิ่มสินค้าสำเร็จ");
            } else {
                $error = $stmt->errorInfo();
                dd_return(false, "SQL ผิดพลาด: " . $error[2]);
            }
        } else {
            dd_return(false, "กรุณากรอกข้อมูลให้ครบ");
        }
    } else {
        dd_return(false, "เข้าสู่ระบบก่อนดำเนินการครับ");
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>