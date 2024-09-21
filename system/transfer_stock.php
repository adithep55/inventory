<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';

function dd_return($status, $message)
{
    $json = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    header('Content-Type: application/json');
    echo json_encode($json);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['UserID'])) {

        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['transferDate'], $input['fromLocation'], $input['toLocation'], $input['products'])) {
            $transferDate = $input['transferDate'];
            $fromLocationId = $input['fromLocation'];
            $toLocationId = $input['toLocation'];
            $products = $input['products'];
            $userId = $_SESSION['UserID'];
            error_log("Received data: " . print_r($input, true));
            try {
                $conn->beginTransaction();

                error_log("Inserting into h_transfer: Date: $transferDate, From: $fromLocationId, To: $toLocationId, User: $userId");

                $stmt = dd_q("INSERT INTO h_transfer (transfer_date, from_location_id, to_location_id, user_id) VALUES (?, ?, ?, ?)", [
                    $transferDate,
                    $fromLocationId,
                    $toLocationId,
                    $userId
                ]);

                if (!$stmt) {
                    error_log("Error inserting into h_transfer: " . print_r($conn->errorInfo(), true));
                    throw new Exception("ไม่สามารถบันทึกข้อมูลหลักของการโอนย้ายสินค้าได้");
                }

                $transferHeaderId = $conn->lastInsertId();

                foreach ($products as $product) {
                    // ตรวจสอบว่ามีสินค้าเพียงพอในคลังต้นทางหรือไม่
                    $stmtCheck = dd_q("SELECT quantity FROM inventory WHERE product_id = ? AND location_id = ?", [
                        $product['productId'],
                        $fromLocationId
                    ]);
                    $currentQuantity = $stmtCheck->fetchColumn();

                    error_log("Current quantity for product {$product['productId']} in location {$fromLocationId}: {$currentQuantity}");

                    if ($currentQuantity < $product['quantity']) {
                        throw new Exception("สินค้า {$product['productId']} มีไม่เพียงพอในคลังต้นทาง");
                    }

                    $stmtReduce = dd_q("UPDATE inventory 
                    SET quantity = GREATEST(quantity - ?, 0),
                        user_id = ?,
                        updated_at = NOW()
                    WHERE product_id = ? AND location_id = ?", [
                        $product['quantity'],
                        $userId,
                        $product['productId'],
                        $fromLocationId
                    ]);

                    if ($stmtReduce->rowCount() == 0) {
                        error_log("Failed to reduce quantity for product {$product['productId']} in location {$fromLocationId}");
                        throw new Exception("ไม่สามารถลดจำนวนสินค้า {$product['productId']} จากคลังต้นทางได้");
                    }

                    error_log("Reduced quantity for product {$product['productId']} in location {$fromLocationId} by {$product['quantity']}");

                    $stmtAdd = dd_q("INSERT INTO inventory (product_id, location_id, quantity, user_id, updated_at) 
                    VALUES (?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    quantity = quantity + ?,
                    user_id = ?,
                    updated_at = NOW()", [
                        $product['productId'],
                        $toLocationId,
                        $product['quantity'],
                        $userId,
                        $product['quantity'],
                        $userId
                    ]);

                    if ($stmtAdd->rowCount() == 0) {
                        error_log("Failed to add quantity for product {$product['productId']} in location {$toLocationId}");
                        throw new Exception("ไม่สามารถเพิ่มจำนวนสินค้า {$product['productId']} ในคลังปลายทางได้");
                    }

                    error_log("Added quantity for product {$product['productId']} in location {$toLocationId} by {$product['quantity']}");

                    // บันทึกรายละเอียดการโอนย้าย
                    $stmtDetail = dd_q("INSERT INTO d_transfer (transfer_header_id, product_id, quantity, unit, user_id) VALUES (?, ?, ?, ?, ?)", [
                        $transferHeaderId,
                        $product['productId'],
                        $product['quantity'],
                        $product['unit'],
                        $userId
                    ]);
                    $stmtCleanup = dd_q("DELETE FROM inventory WHERE quantity = 0");
                    
                    if (!$stmtDetail) {
                        error_log("Failed to insert transfer detail for product {$product['productId']}");
                        throw new Exception("ไม่สามารถบันทึกรายละเอียดการโอนย้ายสินค้า {$product['productId']} ได้");
                    }

                    error_log("Inserted transfer detail for product {$product['productId']}");
                }

                $conn->commit();
                dd_return(true, "บันทึกการโอนย้ายสินค้าสำเร็จ");

            } catch (Exception $e) {
                $conn->rollBack();
                dd_return(false, $e->getMessage());
            }
        } else {
            dd_return(false, "ข้อมูลไม่ครบถ้วน");
        }
    } else {
        dd_return(false, "กรุณาเข้าสู่ระบบก่อนดำเนินการ");
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>