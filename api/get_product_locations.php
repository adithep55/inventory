<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';

header('Content-Type: application/json');

if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
    exit;
}

$productId = $_POST['product_id'];

$query = "SELECT l.location_id, l.location, i.quantity
          FROM inventory i
          JOIN locations l ON i.location_id = l.location_id
          WHERE i.product_id = :product_id AND i.quantity > 0";

$stmt = $conn->prepare($query);
$stmt->execute([':product_id' => $productId]);
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all locations
$allLocationsQuery = "SELECT location_id, location FROM locations";
$allLocationsStmt = $conn->query($allLocationsQuery);
$allLocations = $allLocationsStmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success', 
    'locations' => $locations,
    'all_locations' => $allLocations
]);
?>