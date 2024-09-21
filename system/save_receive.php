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

function generateBillNumber($conn) {
    $thaiYear = (int)date('Y') + 543;
    $year = substr($thaiYear, -2);
    
    $stmt = dd_q("SELECT MAX(CAST(SUBSTRING(bill_number, 4) AS UNSIGNED)) as max_number FROM h_receive WHERE bill_number LIKE 'R{$year}%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxNumber = $result['max_number'];
    $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
    return 'R' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['UserID'])) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['receiveDate'], $input['receiveType'], $input['products'])) {
            $receiveDate = $input['receiveDate'];
            $receiveType = $input['receiveType'];
            $products = $input['products'];
            $userId = $_SESSION['UserID'];

            // Validate receive date
            $currentDate = date('Y-m-d');
            if ($receiveDate > $currentDate) {
                dd_return(false, "ไม่สามารถเลือกวันที่ในอนาคตได้");
            }

            try {
                $conn->beginTransaction();

                // Generate bill number only once for the entire batch
                $billNumber = generateBillNumber($conn);

                $stmt = dd_q("INSERT INTO h_receive (bill_number, received_date, user_id, is_opening_balance) VALUES (?, ?, ?, ?)", [
                    $billNumber,
                    $receiveDate,
                    $userId,
                    $receiveType == 'opening' ? 1 : 0
                ]);

                if (!$stmt) {
                    throw new Exception("ไม่สามารถบันทึกข้อมูลหลักของการรับสินค้าได้");
                }

                $receiveHeaderId = $conn->lastInsertId();

                foreach ($products as $product) {
                    // Validate quantity
                    if ($product['quantity'] <= 0) {
                        throw new Exception("จำนวนสินค้าต้องมากกว่า 0");
                    }

                    $stmtDetail = dd_q("INSERT INTO d_receive (receive_header_id, product_id, quantity, unit, location_id, user_id) VALUES (?, ?, ?, ?, ?, ?)", [
                        $receiveHeaderId,
                        $product['productId'],
                        $product['quantity'],
                        $product['unit'],
                        $product['locationId'],
                        $userId
                    ]);

                    if (!$stmtDetail) {
                        throw new Exception("ไม่สามารถบันทึกข้อมูลสินค้า {$product['productId']} ได้");
                    }

                    $stmtInventory = dd_q("INSERT INTO inventory (product_id, location_id, quantity, user_id) 
                                           VALUES (?, ?, ?, ?) 
                                           ON DUPLICATE KEY UPDATE quantity = quantity + ?, user_id = ?", [
                        $product['productId'],
                        $product['locationId'],
                        $product['quantity'],
                        $userId,
                        $product['quantity'],
                        $userId
                    ]);

                    if (!$stmtInventory) {
                        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้า {$product['productId']} ได้");
                    }
                }

                $conn->commit();
                dd_return(true, "บันทึกการรับสินค้าสำเร็จ (เลขที่บิล: $billNumber)");

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