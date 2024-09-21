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

function updateInventory($conn, $productId, $locationId, $quantityChange, $userId) {
    $stmt = dd_q("INSERT INTO inventory (product_id, location_id, quantity, user_id) 
                  VALUES (?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE quantity = quantity + ?, user_id = ?", [
        $productId, $locationId, $quantityChange, $userId, $quantityChange, $userId
    ]);
    return $stmt !== false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID'])) {
        dd_return(false, "กรุณาเข้าสู่ระบบก่อนดำเนินการ");
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['receiveId'], $input['receiveDate'], $input['receiveType'], $input['items']) || empty($input['items'])) {
        dd_return(false, "ข้อมูลไม่ครบถ้วน");
    }

    $receiveId = $input['receiveId'];
    $receiveDate = $input['receiveDate'];
    $receiveType = $input['receiveType'];
    $items = $input['items'];
    $userId = $_SESSION['UserID'];

    // Validate receive date
    $currentDate = date('Y-m-d');
    if ($receiveDate > $currentDate) {
        dd_return(false, "ไม่สามารถเลือกวันที่ในอนาคตได้");
    }

    try {
        $conn->beginTransaction();

        // อัปเดตข้อมูลหลักของการรับสินค้า
        $stmt = dd_q("UPDATE h_receive SET received_date = ?, is_opening_balance = ? WHERE receive_header_id = ?", [
            $receiveDate, ($receiveType === 'opening' ? 1 : 0), $receiveId
        ]);
        if (!$stmt) {
            throw new Exception("ไม่สามารถอัปเดตข้อมูลหลักของการรับสินค้าได้");
        }

        // ดึงข้อมูลรายการเดิม
        $stmt = dd_q("SELECT product_id, quantity, location_id FROM d_receive WHERE receive_header_id = ?", [$receiveId]);
        $originalItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $originalItemMap = [];
        foreach ($originalItems as $item) {
            $key = $item['product_id'] . '-' . $item['location_id'];
            $originalItemMap[$key] = $item;
        }

        // อัปเดตหรือเพิ่มรายการสินค้า
        foreach ($items as $item) {
            $newKey = $item['productId'] . '-' . $item['locationId'];
            $oldKey = $item['productId'] . '-' . $item['originalLocationId'];

            if (isset($originalItemMap[$oldKey])) {
                $originalItem = $originalItemMap[$oldKey];
                
                // ถ้ามีการเปลี่ยนคลัง
                if ($item['locationId'] != $item['originalLocationId']) {
                    // ลบจำนวนออกจากคลังเดิม
                    if (!updateInventory($conn, $item['productId'], $item['originalLocationId'], -$originalItem['quantity'], $userId)) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังเดิมของสินค้า {$item['productId']} ได้");
                    }
                    
                    // เพิ่มจำนวนในคลังใหม่
                    if (!updateInventory($conn, $item['productId'], $item['locationId'], $item['newQuantity'], $userId)) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังใหม่ของสินค้า {$item['productId']} ได้");
                    }
                    
                    // อัปเดตข้อมูลในตาราง d_receive
                    $stmt = dd_q("UPDATE d_receive SET quantity = ?, location_id = ? WHERE receive_header_id = ? AND product_id = ? AND location_id = ?", [
                        $item['newQuantity'], $item['locationId'], $receiveId, $item['productId'], $item['originalLocationId']
                    ]);
                } else {
                    // ถ้าไม่มีการเปลี่ยนคลัง แค่ปรับจำนวน
                    $quantityDiff = $item['newQuantity'] - $originalItem['quantity'];
                    if (!updateInventory($conn, $item['productId'], $item['locationId'], $quantityDiff, $userId)) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$item['productId']} ได้");
                    }
                    
                    // อัปเดตข้อมูลในตาราง d_receive
                    $stmt = dd_q("UPDATE d_receive SET quantity = ? WHERE receive_header_id = ? AND product_id = ? AND location_id = ?", [
                        $item['newQuantity'], $receiveId, $item['productId'], $item['locationId']
                    ]);
                }
                
                if (!$stmt) {
                    throw new Exception("ไม่สามารถอัปเดตรายการสินค้า {$item['productId']} ได้");
                }
                
                unset($originalItemMap[$oldKey]);
            } else {
                // เพิ่มรายการใหม่
                $stmt = dd_q("INSERT INTO d_receive (receive_header_id, product_id, quantity, location_id, user_id) VALUES (?, ?, ?, ?, ?)", [
                    $receiveId, $item['productId'], $item['newQuantity'], $item['locationId'], $userId
                ]);
                if (!$stmt) {
                    throw new Exception("ไม่สามารถเพิ่มรายการสินค้า {$item['productId']} ได้");
                }

                if (!updateInventory($conn, $item['productId'], $item['locationId'], $item['newQuantity'], $userId)) {
                    throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$item['productId']} ได้");
                }
            }
        }

        // ลบรายการที่ไม่มีในข้อมูลใหม่
        foreach ($originalItemMap as $key => $item) {
            list($productId, $locationId) = explode('-', $key);
            
            $stmt = dd_q("DELETE FROM d_receive WHERE receive_header_id = ? AND product_id = ? AND location_id = ?", [
                $receiveId, $productId, $locationId
            ]);
            
            if (!$stmt) {
                throw new Exception("ไม่สามารถลบรายการสินค้า $productId ได้");
            }
            
            if (!updateInventory($conn, $productId, $locationId, -$item['quantity'], $userId)) {
                throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า $productId ได้");
            }
        }

        // ตรวจสอบว่ายังมีรายการสินค้าเหลืออยู่หรือไม่
        $stmt = dd_q("SELECT COUNT(*) as count FROM d_receive WHERE receive_header_id = ?", [$receiveId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] == 0) {
            throw new Exception("ไม่สามารถลบทุกรายการสินค้าได้ ต้องมีอย่างน้อย 1 รายการ");
        }

        $conn->commit();
        dd_return(true, "อัปเดตข้อมูลการรับสินค้าเรียบร้อยแล้ว");

    } catch (Exception $e) {
        $conn->rollBack();
        dd_return(false, $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>