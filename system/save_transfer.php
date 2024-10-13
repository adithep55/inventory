<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_transfers']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

function dd_return($status, $message)
{
    $json = ['status' => $status ? 'success' : 'fail', 'message' => $message];
    header('Content-Type: application/json');
    echo json_encode($json);
    exit();
}

function generateBillNumber($conn) {
    $thaiYear = (int)date('Y') + 543;
    $year = substr($thaiYear, -2);
    
    $stmt = dd_q("SELECT MAX(CAST(SUBSTRING(bill_number, 4) AS UNSIGNED)) as max_number FROM h_transfer WHERE bill_number LIKE 'T{$year}%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxNumber = $result['max_number'];
    $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
    return 'T' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['UserID'])) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['transfer_date'], $input['products'])) {
            $transferDate = $input['transfer_date'];
            $products = $input['products'];
            $userId = $_SESSION['UserID'];

            // Validate transfer date
            $currentDate = date('Y-m-d');
            if ($transferDate > $currentDate) {
                dd_return(false, "ไม่สามารถเลือกวันที่ในอนาคตได้");
            }

            try {
                $conn->beginTransaction();

                // Generate bill number only once for the entire batch
                $billNumber = generateBillNumber($conn);

                $stmt = dd_q("INSERT INTO h_transfer (bill_number, transfer_date, user_id) VALUES (?, ?, ?)", [
                    $billNumber,
                    $transferDate,
                    $userId
                ]);

                if (!$stmt) {
                    throw new Exception("ไม่สามารถบันทึกข้อมูลหลักของการโอนย้ายสินค้าได้");
                }

                $transferHeaderId = $conn->lastInsertId();

                foreach ($products as $product) {
                    // Validate quantity
                    if ($product['quantity'] <= 0) {
                        throw new Exception("จำนวนสินค้าต้องมากกว่า 0");
                    }

                    // Check inventory in source location
                    $stmtCheck = dd_q("SELECT quantity FROM inventory WHERE product_id = ? AND location_id = ?", [
                        $product['product_id'],
                        $product['from_location_id']
                    ]);
                    $currentQuantity = $stmtCheck->fetchColumn();

                    if ($currentQuantity === false || $product['quantity'] > $currentQuantity) {
                        throw new Exception("สินค้า {$product['product_id']} มีไม่เพียงพอในคลังต้นทาง (มี: {$currentQuantity}, ต้องการ: {$product['quantity']})");
                    }

                    $stmtDetail = dd_q("INSERT INTO d_transfer (transfer_header_id, product_id, from_location_id, to_location_id, quantity, unit, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                        $transferHeaderId,
                        $product['product_id'],
                        $product['from_location_id'],
                        $product['to_location_id'],
                        $product['quantity'],
                        $product['unit'],
                        $userId
                    ]);

                    if (!$stmtDetail) {
                        throw new Exception("ไม่สามารถบันทึกข้อมูลสินค้า {$product['product_id']} ได้");
                    }

                    // Update inventory for 'from' location
                    $stmtUpdateFrom = dd_q("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND location_id = ?", [
                        $product['quantity'],
                        $product['product_id'],
                        $product['from_location_id']
                    ]);

                    if (!$stmtUpdateFrom) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$product['product_id']} ในคลังต้นทางได้");
                    }

                    // Update or insert inventory for 'to' location
                    $stmtUpdateTo = dd_q("INSERT INTO inventory (product_id, location_id, quantity, user_id) 
                                          VALUES (?, ?, ?, ?) 
                                          ON DUPLICATE KEY UPDATE quantity = quantity + ?, user_id = ?", [
                        $product['product_id'],
                        $product['to_location_id'],
                        $product['quantity'],
                        $userId,
                        $product['quantity'],
                        $userId
                    ]);

                    if (!$stmtUpdateTo) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$product['product_id']} ในคลังปลายทางได้");
                    }
                }

                $conn->commit();
                dd_return(true, "บันทึกการโอนย้ายสินค้าสำเร็จ (เลขที่บิล: $billNumber)");

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