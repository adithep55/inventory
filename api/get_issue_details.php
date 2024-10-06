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

function formatFullDate($date)
{
    $dateTime = new DateTime($date);
    $thai_year = (int)$dateTime->format('Y') + 543;
    return $dateTime->format('d-m-') . $thai_year;
}

function formatFullDateTime($dateTime)
{
    $dateTimeObj = new DateTime($dateTime);
    $thai_year = (int)$dateTimeObj->format('Y') + 543;
    return $dateTimeObj->format('d-m-') . $thai_year . ' ' . $dateTimeObj->format('H:i:s');
}

try {
    // Fetch issue header information
    $headerQuery = "SELECT h.bill_number, h.issue_date, h.issue_type, h.customer_id, h.project_id,
                           u.Username as user_name, u.fname, u.lname,
                           h.updated_at
                    FROM h_issue h
                    JOIN users u ON h.user_id = u.UserID
                    WHERE h.issue_header_id = :id";

    $stmt = $conn->prepare($headerQuery);
    $stmt->bindParam(':id', $issueId, PDO::PARAM_INT);
    $stmt->execute();
    $header = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$header) {
        echo json_encode(['error' => 'Issue not found']);
        exit;
    }

    // Format the issue_date and updated_at timestamp
    $header['issue_date'] = formatFullDate($header['issue_date']);
    $header['updated_at'] = formatFullDateTime($header['updated_at']);

    // Fetch customer or project details based on issue type
    if ($header['issue_type'] === 'sale') {
        $customerQuery = "SELECT c.name, c.address, c.phone_number, c.tax_id, c.contact_person, c.credit_limit, c.credit_terms
                          FROM customers c
                          WHERE c.customer_id = :customer_id";
        $stmt = $conn->prepare($customerQuery);
        $stmt->bindParam(':customer_id', $header['customer_id'], PDO::PARAM_INT);
        $stmt->execute();
        $customerDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $header['customer_details'] = $customerDetails;
    } else {
        $projectQuery = "SELECT p.project_name, p.project_description, p.start_date, p.end_date
                         FROM projects p
                         WHERE p.project_id = :project_id";
        $stmt = $conn->prepare($projectQuery);
        $stmt->bindParam(':project_id', $header['project_id'], PDO::PARAM_INT);
        $stmt->execute();
        $projectDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $header['project_details'] = $projectDetails;
    }

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
            'full_name' => $header['fname'] . ' ' . $header['lname'],
            'updated_at' => $header['updated_at'],
            'items' => $items
        ]
    ];

    // Add customer or project details to the response
    if ($header['issue_type'] === 'sale') {
        $response['data']['customer_details'] = $header['customer_details'];
    } else {
        $response['data']['project_details'] = $header['project_details'];
        // Add project name to the response
        $response['data']['project_name'] = $header['project_details']['project_name'];
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>