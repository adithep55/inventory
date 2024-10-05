<?php
require_once '../config/connect.php';
require_once '../config/permission.php';
requirePermission(['manage_issue']);

header('Content-Type: application/json');
function exception_handler($exception)
{
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    exit;
}
set_exception_handler('exception_handler');



try {
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

    // Base query
    $query = "SELECT h.issue_header_id as issue_id, h.bill_number, h.issue_date, h.issue_type, 
    CASE 
        WHEN h.issue_type = 'sale' THEN c.name 
        WHEN h.issue_type = 'project' THEN p.project_name
        ELSE ''
    END AS customer_project,
    u.Username AS user_name, 
    'N/A' AS status,
    (SELECT COUNT(*) FROM d_issue WHERE issue_header_id = h.issue_header_id) as item_count
FROM h_issue h
LEFT JOIN customers c ON h.customer_id = c.customer_id
LEFT JOIN projects p ON h.project_id = p.project_id
JOIN users u ON h.user_id = u.UserID";

    // Search condition
    $searchCondition = "";
    $params = [];
    if (!empty($search)) {
        $searchCondition = " WHERE h.issue_date LIKE :search OR h.issue_type LIKE :search OR c.name LIKE :search OR p.project_name LIKE :search OR u.Username LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Count total records
    $countQuery = "SELECT COUNT(*) FROM ($query$searchCondition) as count_table";
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();

    // Fetch data
    $query .= "$searchCondition ORDER BY h.issue_date DESC LIMIT :start, :length";
    $stmt = $conn->prepare($query);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch items for each issue
    foreach ($issues as &$issue) {
        $itemQuery = "SELECT p.name_th, d.quantity, p.unit
                      FROM d_issue d
                      JOIN products p ON d.product_id = p.product_id
                      WHERE d.issue_header_id = :issue_id
                      LIMIT 3";
        $itemStmt = $conn->prepare($itemQuery);
        $itemStmt->execute([':issue_id' => $issue['issue_id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $itemsText = implode(', ', array_map(function ($item) {
            return "{$item['name_th']} ({$item['quantity']} {$item['unit']})";
        }, $items));

        if ($issue['item_count'] > 3) {
            $itemsText .= ' และอื่นๆ';
        }
        $issue['items'] = $itemsText;
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $issues
    ]);

} catch (Exception $e) {
    exception_handler($e);
}
?>