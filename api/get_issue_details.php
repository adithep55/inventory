<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_issue']);

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Issue ID is required']);
    exit;
}

$issueId = intval($_GET['id']);

try {
    // Fetch issue header information
    $headerQuery = "SELECT h.bill_number, h.issue_date, h.issue_type,
                           u.Username as user_name,
                           h.updated_at,
                           CASE 
                               WHEN h.issue_type = 'sale' THEN c.name
                               ELSE p.project_name
                           END as customer_project_name
                    FROM h_issue h
                    JOIN users u ON h.user_id = u.UserID
                    LEFT JOIN customers c ON h.customer_id = c.customer_id
                    LEFT JOIN projects p ON h.project_id = p.project_id
                    WHERE h.issue_header_id = :id";

    $stmt = $conn->prepare($headerQuery);
    $stmt->bindParam(':id', $issueId, PDO::PARAM_INT);
    $stmt->execute();
    $header = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$header) {
        echo json_encode(['error' => 'Issue not found']);
        exit;
    }

    // Format the updated_at timestamp
    $header['updated_at'] = date('Y-m-d H:i:s', strtotime($header['updated_at']));

    // Fetch issue items
    $itemsQuery = "SELECT d.product_id, p.name_th as product_name, d.quantity, p.unit, 
    l.location_id, l.location as location_name,
    (SELECT GROUP_CONCAT(CONCAT(l2.location_id, ':', l2.location, ':', COALESCE(i.quantity, 0)) SEPARATOR '|')
     FROM locations l2
     LEFT JOIN inventory i ON i.product_id = d.product_id AND i.location_id = l2.location_id
    ) as all_locations
    FROM d_issue d
    JOIN products p ON d.product_id = p.product_id
    JOIN locations l ON d.location_id = l.location_id
    WHERE d.issue_header_id = :id";

    $stmt = $conn->prepare($itemsQuery);
    $stmt->bindParam(':id', $issueId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process items to include location information
    foreach ($items as &$item) {
        $locations = [];
        $allLocations = explode('|', $item['all_locations']);
        foreach ($allLocations as $loc) {
            list($locId, $locName, $qty) = explode(':', $loc);
            $locations[] = [
                'id' => $locId,
                'name' => $locName,
                'quantity' => (int) $qty
            ];
        }
        $item['locations'] = $locations;
        unset($item['all_locations']);
    }

    // Prepare the response
    $response = [
        'status' => 'success',
        'data' => [
            'bill_number' => $header['bill_number'],
            'issue_date' => $header['issue_date'],
            'issue_type' => $header['issue_type'],
            'user_name' => $header['user_name'],
            'updated_at' => $header['updated_at'],
            'customer_name' => $header['issue_type'] === 'sale' ? $header['customer_project_name'] : null,
            'project_name' => $header['issue_type'] === 'project' ? $header['customer_project_name'] : null,
            'items' => $items
        ]
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>