<?php
require_once '../config/connect.php';
header('Content-Type: application/json');

function dd_return($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['UserID'])) {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        try {
            // เริ่ม transaction
            $conn->beginTransaction();

            // ตรวจสอบการเชื่อมโยงกับตารางต่างๆ
            $tables_to_check = [
                'customers' => 'ลูกค้า',
                'h_issue' => 'การเบิกสินค้า',
                'h_receive' => 'การรับสินค้า',
                'h_transfer' => 'การโอนสินค้า',
                'inventory' => 'คลังสินค้า',
                'products' => 'สินค้า',
                'projects' => 'โครงการ'
            ];

            foreach ($tables_to_check as $table => $table_name) {
                $check = dd_q("SELECT COUNT(*) FROM $table WHERE user_id = ?", [$id]);
                if ($check->fetchColumn() > 0) {
                    dd_return('fail', "ไม่สามารถลบข้อมูลผู้ใช้ได้ เนื่องจากมีข้อมูลที่เชื่อมโยงในตาราง$table_name");
                }
            }

            // ลบข้อมูลผู้ใช้
            $stmt = dd_q('DELETE FROM users WHERE UserID = ?', [$id]);
            if ($stmt->rowCount() > 0) {
                $conn->commit();
                dd_return('success', 'ลบข้อมูลผู้ใช้สำเร็จ');
            } else {
                $conn->rollBack();
                dd_return('fail', 'ไม่พบข้อมูลผู้ใช้ที่ต้องการลบ');
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            dd_return('fail', 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage());
        }
    } else {
        dd_return('fail', 'ไม่ได้ระบุ ID ของผู้ใช้');
    }
} else {
    dd_return('fail', 'Invalid request');
}
?>