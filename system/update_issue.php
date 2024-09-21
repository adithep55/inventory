<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', '../error.log');
require_once '../config/connect.php';

function dd_return($status, $message)
{
    $json = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    header('Content-Type: application/json');
    echo json_encode($json);
    exit();
}

function updateInventory($conn, $productId, $locationId, $quantityChange, $userId)
{
    $stmt = dd_q("UPDATE inventory SET quantity = quantity - ?, user_id = ? 
                  WHERE product_id = ? AND location_id = ?", [
        $quantityChange,
        $userId,
        $productId,
        $locationId
    ]);
    return $stmt !== false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID'])) {
        dd_return(false, "กรุณาเข้าสู่ระบบก่อนดำเนินการ");
    }

    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Received data: " . print_r($input, true));

    if (!isset($input['issueId'], $input['issueDate'], $input['issueType'], $input['items']) || empty($input['items'])) {
        dd_return(false, "ข้อมูลไม่ครบถ้วน");
    }

    $issueId = $input['issueId'];
    $issueDate = $input['issueDate'];
    $issueType = $input['issueType'];
    $items = $input['items'];
    $userId = $_SESSION['UserID'];

    // Validate issue date
    $currentDate = date('Y-m-d');
    if ($issueDate > $currentDate) {
        dd_return(false, "ไม่สามารถเลือกวันที่ในอนาคตได้");
    }

    try {
        $conn->beginTransaction();

        if (empty($issueId)) {
            throw new Exception("ไม่พบรหัสรายการเบิก");
        }

        // อัปเดตข้อมูลหลักของการเบิกสินค้า
        $stmt = dd_q("UPDATE h_issue SET issue_date = ?, issue_type = ?, customer_id = ?, project_id = ? WHERE bill_number = ?", [
            $issueDate,
            $issueType,
            ($issueType === 'sale' ? $input['customerId'] : null),
            ($issueType === 'project' ? $input['projectId'] : null),
            $issueId
        ]);
        if (!$stmt) {
            throw new Exception("ไม่สามารถอัปเดตข้อมูลหลักของการเบิกสินค้าได้");
        }

        // ดึงข้อมูลรายการเดิม
        $stmt = dd_q("SELECT product_id, quantity, location_id FROM d_issue WHERE issue_header_id = (SELECT issue_header_id FROM h_issue WHERE bill_number = ?)", [$issueId]);
        $originalItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Original items: " . print_r($originalItems, true));

        // สร้าง array เพื่อเก็บรายการที่จะอัปเดต
        $itemsToUpdate = [];
        foreach ($items as $item) {
            $key = $item['productId'] . '-' . $item['locationId'];
            $itemsToUpdate[$key] = $item;
        }
        error_log("Items to update: " . print_r($itemsToUpdate, true));

        // สร้าง array เพื่อเก็บรายการเดิม
        $originalItemMap = [];
        foreach ($originalItems as $item) {
            $key = $item['product_id'] . '-' . $item['location_id'];
            $originalItemMap[$key] = $item;
        }

        // อัปเดตรายการสินค้า
        foreach ($originalItems as $originalItem) {
            $key = $originalItem['product_id'] . '-' . $originalItem['location_id'];

            if (isset($itemsToUpdate[$key])) {
                $newItem = $itemsToUpdate[$key];
                $quantityDiff = $newItem['quantity'] - $originalItem['quantity'];

                if ($quantityDiff != 0) {
                    // ตรวจสอบจำนวนคงเหลือในคลัง
                    $stmt = dd_q("SELECT quantity FROM inventory WHERE product_id = ? AND location_id = ?", [
                        $originalItem['product_id'],
                        $originalItem['location_id']
                    ]);
                    $inventoryItem = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$inventoryItem || $inventoryItem['quantity'] < $quantityDiff) {
                        throw new Exception("สินค้า {$originalItem['product_id']} มีไม่เพียงพอในคลังสำหรับการเบิกเพิ่ม");
                    }

                    if (!updateInventory($conn, $originalItem['product_id'], $originalItem['location_id'], $quantityDiff, $userId)) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$originalItem['product_id']} ได้");
                    }

                    $stmt = dd_q("UPDATE d_issue SET quantity = ? WHERE issue_header_id = (SELECT issue_header_id FROM h_issue WHERE bill_number = ?) AND product_id = ? AND location_id = ?", [
                        $newItem['quantity'],
                        $issueId,
                        $originalItem['product_id'],
                        $originalItem['location_id']
                    ]);
                    if (!$stmt) {
                        throw new Exception("ไม่สามารถอัปเดตรายการสินค้า {$originalItem['product_id']} ได้");
                    }
                }

                unset($itemsToUpdate[$key]);
            } else {
                // ถ้าไม่มีในรายการใหม่ ให้ลบออก
                $stmt = dd_q("DELETE FROM d_issue WHERE issue_header_id = (SELECT issue_header_id FROM h_issue WHERE bill_number = ?) AND product_id = ? AND location_id = ?", [
                    $issueId,
                    $originalItem['product_id'],
                    $originalItem['location_id']
                ]);
                if (!$stmt) {
                    throw new Exception("ไม่สามารถลบรายการสินค้า {$originalItem['product_id']} ได้");
                }

                // คืนสินค้ากลับเข้าคลัง
                if (!updateInventory($conn, $originalItem['product_id'], $originalItem['location_id'], -$originalItem['quantity'], $userId)) {
                    throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$originalItem['product_id']} ได้");
                }
            }
        }

        // ตรวจสอบว่ายังมีรายการสินค้าเหลืออยู่หรือไม่
        $stmt = dd_q("SELECT COUNT(*) as count FROM d_issue WHERE issue_header_id = (SELECT issue_header_id FROM h_issue WHERE bill_number = ?)", [$issueId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] == 0) {
            throw new Exception("ไม่สามารถลบทุกรายการสินค้าได้ ต้องมีอย่างน้อย 1 รายการ");
        }

        $conn->commit();
        dd_return(true, "อัปเดตข้อมูลการเบิกสินค้าเรียบร้อยแล้ว");

    } catch (Exception $e) {
        $conn->rollBack();
        dd_return(false, $e->getMessage());
    }
} else {
    dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>