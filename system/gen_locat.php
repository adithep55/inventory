<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_inventory']);
header('Content-Type: application/json');

function generate_location_id() {
    $prefix = 'LOC';
    $year = date('y') + 543; // Convert to Buddhist era year
    
    try {
        $stmt = dd_q('SELECT MAX(CAST(SUBSTRING(location_id, 8) AS UNSIGNED)) as max_id FROM locations WHERE location_id LIKE ?', ["{$prefix}{$year}%"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $max_id = $result['max_id'] ?? 0;
        $new_id = $max_id + 1;
        
        return "{$prefix}{$year}" . str_pad($new_id, 4, '0', STR_PAD_LEFT);
    } catch (PDOException $e) {
        error_log("Database error in generate_location_id: " . $e->getMessage());
        throw new Exception("Database error occurred");
    }
}

try {
    $generated_id = generate_location_id();
    echo json_encode(['status' => 'success', 'generated_id' => $generated_id]);
} catch (Exception $e) {
    error_log("Error in gen_locat.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>