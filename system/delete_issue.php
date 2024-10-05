<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_issue']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Issue ID is required']);
    exit;
}

$issueId = intval($_POST['id']);

try {
    $conn->beginTransaction();

    // First, get the items for this issue to update inventory
    $stmt = $conn->prepare("SELECT product_id, quantity, location_id FROM d_issue WHERE issue_header_id = ?");
    $stmt->execute([$issueId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update inventory for each item (add back the quantities)
    foreach ($items as $item) {
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE product_id = ? AND location_id = ?");
        $stmt->execute([$item['quantity'], $item['product_id'], $item['location_id']]);
    }

    // Delete from d_issue
    $stmt = $conn->prepare("DELETE FROM d_issue WHERE issue_header_id = ?");
    $stmt->execute([$issueId]);

    // Delete from h_issue
    $stmt = $conn->prepare("DELETE FROM h_issue WHERE issue_header_id = ?");
    $stmt->execute([$issueId]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Issue record deleted successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>