<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error.log');

require_once '../config/connect.php';

header('Content-Type: application/json');

function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'custom-error.log');
}

function sendJsonResponse($data) {
    logMessage("Sending response: " . json_encode($data));
    echo json_encode($data);
    exit;
}

function checkRelatedRecords($productIds) {
    global $conn;
    if (!is_array($productIds)) {
        $productIds = [$productIds];
    }
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    
    $relatedTables = [
        'd_receive' => 'รายการรับสินค้า',
        'd_issue' => 'รายการเบิกสินค้า',
        'd_transfer' => 'รายการโอนสินค้า',
        // เพิ่มตารางอื่นๆ ที่เกี่ยวข้องตามต้องการ
    ];
    
    $relatedRecords = [];
    
    foreach ($relatedTables as $table => $tableName) {
        $query = "SELECT COUNT(*) as count FROM $table WHERE product_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->execute($productIds);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            $relatedRecords[$tableName] = $result['count'];
        }
    }
    
    return $relatedRecords;
}

function deleteProducts($ids) {
    global $conn;
    logMessage("Attempting to delete products: " . json_encode($ids));
    
    if (!is_array($ids)) {
        $ids = [$ids]; 
    }

    $relatedRecords = checkRelatedRecords($ids);
    if (!empty($relatedRecords)) {
        $message = "ไม่สามารถลบสินค้าได้เนื่องจากมีข้อมูลที่เกี่ยวข้อง:\n";
        foreach ($relatedRecords as $table => $count) {
            $message .= "- $table: $count รายการ\n";
        }
        return [
            'status' => 'error',
            'message' => $message,
            'relatedRecords' => $relatedRecords
        ];
    }

    try {
        $conn->beginTransaction();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "DELETE FROM products WHERE product_id IN ($placeholders)";
        logMessage("Executing query: $query");
        logMessage("With parameters: " . json_encode($ids));
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute($ids);
        
        if ($result === false) {
            $errorInfo = $stmt->errorInfo();
            logMessage("Query execution failed. Error info: " . json_encode($errorInfo));
            $conn->rollBack();
            return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบสินค้า: ' . $errorInfo[2]];
        }
        
        $deletedCount = $stmt->rowCount();
        logMessage("Deleted count: $deletedCount");
        
        $conn->commit();
        
        if ($deletedCount > 0) {
            return ['status' => 'success', 'message' => "ลบสินค้าสำเร็จ $deletedCount รายการ"];
        } else {
            return ['status' => 'error', 'message' => 'ไม่พบสินค้าที่ต้องการลบ'];
        }
        
    } catch (PDOException $e) {
        $conn->rollBack();
        logMessage("PDOException: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบสินค้า: ' . $e->getMessage()];
    } catch (Exception $e) {
        $conn->rollBack();
        logMessage("General Exception: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดที่ไม่คาดคิด: ' . $e->getMessage()];
    }
}

logMessage("Script started. Request method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    logMessage("POST data: " . json_encode($_POST));
    
    if (isset($_POST['id'])) {
        $result = deleteProducts($_POST['id']);
        sendJsonResponse($result);
    } elseif (isset($_POST['ids']) && is_array($_POST['ids'])) {
        $result = deleteProducts($_POST['ids']);
        sendJsonResponse($result);
    } else {
        sendJsonResponse(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง']);
    }
} else {
    sendJsonResponse(['status' => 'error', 'message' => 'Method ไม่ถูกต้อง']);
}