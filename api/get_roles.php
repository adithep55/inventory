<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_users']);

header('Content-Type: application/json');

try {
    $stmt = dd_q("
        SELECT 
                RoleID, 
                RoleName,
                manage_products,
                manage_receiving,
                manage_issue,
                manage_inventory,
                manage_projects,
                manage_customers,
                manage_transfers,
                manage_reports,
                manage_users,
                manage_settings
            FROM 
                roles
            ORDER BY 
                RoleName
    ");

    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
 // แปลงค่า 0 และ 1 เป็น boolean สำหรับสิทธิ์การใช้งาน

    foreach ($roles as &$role) {
        $permissions = [
            'manage_products', 'manage_receiving', 'manage_issue', 'manage_inventory',
            'manage_projects', 'manage_customers', 'manage_transfers',
            'manage_reports', 'manage_users', 'manage_settings'
        ];
        foreach ($permissions as $perm) {
            $role[$perm] = (bool)$role[$perm];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $roles
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}
?>