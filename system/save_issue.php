<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_issue']);

header('Content-Type: application/json');


function generateBillNumber() {
    $thaiYear = (int)date('Y') + 543;
    $year = substr($thaiYear, -2);
    
    $result = dd_q("SELECT MAX(CAST(SUBSTRING(bill_number, 4) AS UNSIGNED)) as max_number FROM h_issue WHERE bill_number LIKE 'D{$year}%'")->fetch();
    $maxNumber = $result['max_number'];
    $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
    return 'D' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

function insertHeaderRecord($billNumber, $issueDate, $issueType, $customerId, $projectId, $userId) {
    $result = dd_q("INSERT INTO h_issue (bill_number, issue_date, issue_type, customer_id, project_id, user_id) VALUES (?, ?, ?, ?, ?, ?)", 
        [$billNumber, $issueDate, $issueType, $customerId, $projectId, $userId]);
    
    if (!$result) {
        throw new Exception("ไม่สามารถบันทึกข้อมูลหลักของการเบิกสินค้าได้");
    }
    
    // หา issue_header_id ที่เพิ่งถูกเพิ่ม
    $lastInsertId = dd_q("SELECT LAST_INSERT_ID() as last_id")->fetch()['last_id'];
    return $lastInsertId;
}

function insertDetailRecord($issueHeaderId, $productId, $locationId, $quantity, $userId) {
    $result = dd_q("INSERT INTO d_issue (issue_header_id, product_id, location_id, quantity, user_id) VALUES (?, ?, ?, ?, ?)", 
        [$issueHeaderId, $productId, $locationId, $quantity, $userId]);
    
    if (!$result) {
        throw new Exception("ไม่สามารถบันทึกข้อมูลสินค้าได้");
    }
}

function checkInventory($productId, $locationId, $quantity) {
    $result = dd_q("SELECT quantity FROM inventory WHERE product_id = ? AND location_id = ?", 
        [$productId, $locationId])->fetch();
    
    if (!$result || $result['quantity'] < $quantity) {
        throw new Exception("สินค้าในคลังมีไม่เพียงพอ");
    }
}

function updateInventory($productId, $locationId, $quantity, $userId) {
    $result = dd_q("UPDATE inventory SET quantity = quantity - ?, user_id = ? WHERE product_id = ? AND location_id = ?", 
        [$quantity, $userId, $productId, $locationId]);
    
    if (!$result) {
        throw new Exception("ไม่สามารถปรับปรุงข้อมูลคงคลังของสินค้าได้");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID']) || empty($_SESSION['UserID'])) {
        echo json_encode(['status' => 'error', 'message' => "กรุณาเข้าสู่ระบบก่อนดำเนินการ"]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['issueDate'], $input['issueType'], $input['products']) || 
        empty($input['issueDate']) || empty($input['issueType']) || empty($input['products'])) {
        echo json_encode(['status' => 'error', 'message' => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
        exit;
    }

    $issueDate = $input['issueDate'];
    $issueType = $input['issueType'];
    $products = $input['products'];
    $userId = $_SESSION['UserID'];

    foreach ($products as $product) {
        if (empty($product['productId']) || empty($product['locationId']) || empty($product['quantity'])) {
            echo json_encode(['status' => 'error', 'message' => "ข้อมูลสินค้าไม่ถูกต้อง"]);
            exit;
        }
    }

    try {
        dd_q('START TRANSACTION');

        $billNumber = generateBillNumber();

        $issueHeaderId = insertHeaderRecord(
            $billNumber,
            $issueDate,
            $issueType,
            $issueType === 'sale' ? $input['customer'] : null,
            $issueType === 'project' ? $input['project'] : null,
            $userId
        );

        foreach ($products as $product) {
            checkInventory($product['productId'], $product['locationId'], $product['quantity']);

            insertDetailRecord(
                $issueHeaderId,
                $product['productId'],
                $product['locationId'],
                $product['quantity'],
                $userId
            );

            updateInventory(
                $product['productId'],
                $product['locationId'],
                $product['quantity'],
                $userId
            );
        }

        dd_q('COMMIT');
        echo json_encode(['status' => 'success', 'message' => "บันทึกการเบิกสินค้าสำเร็จ", 'billNumber' => $billNumber]);

    } catch (Exception $e) {
        dd_q('ROLLBACK');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => "Method not allowed"]);
}
?>