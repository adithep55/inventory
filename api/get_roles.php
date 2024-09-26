<?php
require_once '../config/connect.php';

header('Content-Type: application/json');

try {
    $stmt = dd_q("
        SELECT 
            RoleID, 
            RoleName 
        FROM 
            roles
        ORDER BY 
            RoleName
    ");

    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

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