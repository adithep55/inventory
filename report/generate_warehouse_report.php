<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_reports']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

// ตรวจสอบพารามิเตอร์ที่จำเป็น
if (!isset($_GET['warehouseId']) || !isset($_GET['endDate'])) {
    die('Required parameters are missing');
}

$endDate = $_GET['endDate'];
$startDate = date('Y-m-01', strtotime($endDate)); // First day of the end date month

// ปรับปรุงการประมวลผล warehouseId
if ($_GET['warehouseId'] === 'all') {
    // ดึงข้อมูลทุกคลังสินค้า
    $stmt = $conn->query("SELECT location_id FROM locations");
    $warehouseIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $warehouseIds = explode(',', $_GET['warehouseId']);
}

function formatThaiDate($date) {
    $timestamp = strtotime($date);
    $thai_year = date('Y', $timestamp) + 543;
    return date('d-m-', $timestamp) . $thai_year;
}

// Fetch website settings
function getWebsiteSettings($conn) {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM website_settings");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$settings = getWebsiteSettings($conn);

class PDF extends FPDF
{
    private $settings;
    public $startDate;
    public $locations;
    
    function __construct($settings, $locations)
    {
        parent::__construct();
        $this->settings = $settings;
        $this->locations = $locations;
    }

    private function getImageFileType($file) {
        $size = getimagesize($file);
        if ($size === false) {
            return false;
        }
        $extension = image_type_to_extension($size[2], false);
        return strtoupper($extension);
    }

    function Header()
    {
        $this->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $this->AddFont('THSarabunNew', 'B', 'THSarabunNew_b.php');
        $this->SetFont('THSarabunNew', 'B', 18);
        
        $logoPath = isset($this->settings['logo']) ? '../assets/img/' . $this->settings['logo'] : '../assets/img/logo.png';
        
        if (file_exists($logoPath)) {
            $imageType = $this->getImageFileType($logoPath);
            if ($imageType) {
                $this->Image($logoPath, 5, 6, 30, 0, $imageType);
            } else {
                $this->SetXY(5, 6);
                $this->Cell(30, 10, 'Logo Error', 1, 0, 'C');
            }
        } else {
            $this->SetXY(5, 6);
            $this->Cell(30, 10, 'No Logo', 1, 0, 'C');
        }
        
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_name'] ?? ''), 0, 1, 'C');
        $this->SetFont('THSarabunNew', '', 11);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_address'] ?? ''), 0, 1, 'C');
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_contact'] ?? ''), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('THSarabunNew', 'B', 14);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายงานการเคลื่อนไหวสินค้าตามคลังสินค้า'), 0, 1, 'C');
        $this->SetLineWidth(0.1);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 11);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'หน้า ').$this->PageNo().'/{nb}', 0, 0, 'C');
    }
    
    function WarehouseMovementTable($products, $movements)
    {
        $this->SetFillColor(240, 240, 240);
        $this->SetFont('THSarabunNew', 'B', 14);
        
        $pageHeight = $this->GetPageHeight();
        $pageBreakTrigger = $this->PageBreakTrigger;
        $currentY = $this->GetY();
    
        $totalLocations = count($this->locations);
    
        foreach ($this->locations as $locationIndex => $location) {
            $locationStartY = $currentY;
    
            // Add warehouse name at the top
            $this->SetFont('THSarabunNew', 'B', 14);
            $this->Cell(195, 8, iconv('UTF-8', 'cp874', $location), 1, 1, 'C', true);
            
            $this->SetFont('THSarabunNew', 'B', 12);
            $this->Cell(50, 5, iconv('UTF-8', 'cp874', 'สินค้า'), 1, 0, 'C', true);
            $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'วันที่'), 1, 0, 'C', true);
            $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'ยอดยกมา'), 1, 0, 'C', true);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'รับ'), 1, 0, 'C', true);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'เบิก'), 1, 0, 'C', true);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'โอนย้าย'), 1, 0, 'C', true);
            $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'คงเหลือ'), 1, 0, 'C', true);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 1, 'C', true);
    
            $this->SetFont('THSarabunNew', '', 12);
    
            $productsInLocation = array_filter($products, function($product) use ($movements, $location) {
                $productMovements = $movements[$product['product_id']] ?? [];
                $locationMovements = array_filter($productMovements, function($movement) use ($location) {
                    return $movement['from_location'] == $location || $movement['to_location'] == $location;
                });
                return !empty($locationMovements) || $product['opening_balance'] != 0;
            });

            $totalProductsInLocation = count($productsInLocation);
            $productCount = 0;
    
            foreach ($productsInLocation as $product) {
                $productCount++;
                $productMovements = $movements[$product['product_id']] ?? [];
                $locationMovements = array_filter($productMovements, function($movement) use ($location) {
                    return $movement['from_location'] == $location || $movement['to_location'] == $location;
                });

                $openingBalance = $product['opening_balance'];
                $balance = $openingBalance;
                $totalReceive = 0;
                $totalIssue = 0;
                $totalTransfer = 0;

                $lastDayPreviousMonth = date('Y-m-d', strtotime('-1 day', strtotime($this->startDate)));
                // Product name row
                $this->SetFont('THSarabunNew', 'B', 12);
                $this->Cell(195, 5, iconv('UTF-8', 'cp874', $product['name_th'] . ' (' . $product['product_id'] . ')'), 1, 1, 'L', true);
                $this->SetFont('THSarabunNew', '', 12);
    
                 // Opening balance row (only if not zero)
                 if ($openingBalance != 0) {
                    $this->Cell(50, 5, '', 1, 0, 'L');
                    $this->Cell(20, 5, formatThaiDate($lastDayPreviousMonth), 1, 0, 'C');
                    $this->Cell(20, 5, number_format($openingBalance, 2), 1, 0, 'R');
                    $this->Cell(15, 5, '-', 1, 0, 'C');
                    $this->Cell(15, 5, '-', 1, 0, 'C');
                    $this->Cell(15, 5, '-', 1, 0, 'C');
                    $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'ยอดยกมา'), 1, 0, 'C');
                    $this->Cell(15, 5, number_format($balance, 2), 1, 0, 'R');
                    $this->Cell(15, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
                }

    
                foreach ($locationMovements as $movement) {
                    $quantity = floatval($movement['quantity']);
                    $this->Cell(50, 5, '', 1, 0, 'L');
                    $this->Cell(20, 5, formatThaiDate($movement['date']), 1, 0, 'C');
                    $this->Cell(20, 5, '-', 1, 0, 'C');
    
                    switch($movement['type']) {
                        case 'receive':
                            $balance += $quantity;
                            $totalReceive += $quantity;
                            $this->Cell(15, 5, number_format($quantity, 2), 1, 0, 'R');
                            $this->Cell(15, 5, '-', 1, 0, 'C');
                            $this->Cell(15, 5, '-', 1, 0, 'C');
                            $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'รับสินค้า'), 1, 0, 'C');
                            break;
                        case 'issue':
                            $balance -= $quantity;
                            $totalIssue += $quantity;
                            $this->Cell(15, 5, '-', 1, 0, 'C');
                            $this->Cell(15, 5, number_format($quantity, 2), 1, 0, 'R');
                            $this->Cell(15, 5, '-', 1, 0, 'C');
                            $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'เบิกสินค้า'), 1, 0, 'C');
                            break;
                        case 'transfer':
                            $totalTransfer += $quantity;
                            if ($movement['from_location'] == $location) {
                                $balance -= $quantity;
                                $this->Cell(15, 5, '-', 1, 0, 'C');
                                $this->Cell(15, 5, '-', 1, 0, 'C');
                                $this->Cell(15, 5, number_format($quantity, 2), 1, 0, 'R');
                                $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'โอนย้ายไป ' . $movement['to_location']), 1, 0, 'C');
                            } else {
                                $balance += $quantity;
                                $this->Cell(15, 5, '-', 1, 0, 'C');
                                $this->Cell(15, 5, '-', 1, 0, 'C');
                                $this->Cell(15, 5, number_format($quantity, 2), 1, 0, 'R');
                                $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'รับโอนจาก ' . $movement['from_location']), 1, 0, 'C');
                            }
                            break;
                    }
                    $this->Cell(15, 5, number_format($balance, 2), 1, 0, 'R');
                    $this->Cell(15, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
                }
                
                // Product total row
                $this->SetFont('THSarabunNew', 'B', 12);
                $this->Cell(70, 5, iconv('UTF-8', 'cp874', 'รวม ' . $product['name_th']), 1, 0, 'R');
                $this->Cell(20, 5, number_format($openingBalance, 2), 1, 0, 'R');
                $this->Cell(15, 5, number_format($totalReceive, 2), 1, 0, 'R');
                $this->Cell(15, 5, number_format($totalIssue, 2), 1, 0, 'R');
                $this->Cell(15, 5, number_format($totalTransfer, 2), 1, 0, 'R');
                $this->Cell(30, 5, '-', 1, 0, 'C');
                $this->Cell(15, 5, number_format($balance, 2), 1, 0, 'R');
                $this->Cell(15, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
                $this->SetFont('THSarabunNew', '', 12);
                
                // Add an empty row after each product, except for the last product in the last location
                if ($productCount < $totalProductsInLocation) {
                    $this->Cell(195, 5, '', 1, 1, 'R');
                }
            }
    
            $currentY = $this->GetY();
    
            // Check if there's enough space for the next location
            if ($locationIndex < $totalLocations - 1) {
                $nextLocationHeight = $this->GetLocationHeight($products, $movements, $this->locations[$locationIndex + 1]);
                if ($currentY + $nextLocationHeight > $pageBreakTrigger) {
                    $this->AddPage();
                    $currentY = $this->GetHeaderHeight();
                } else {
                    // Add space between locations
                    $this->Ln(8);
                }
            }
        }
    }

    function GetHeaderHeight()
    {
        return 40; // Adjust this value based on your actual header height
    }

    function GetProductHeight($product, $movements)
    {
        $height = 10; // Height for product name row and total row
        $height += count($movements) * 5; // Height for each movement row
        return $height;
    }

    function GetLocationHeight($products, $movements, $location)
    {
        $height = 15; // Height for location header and column headers

        foreach ($products as $product) {
            $productMovements = $movements[$product['product_id']] ?? [];
            $locationMovements = array_filter($productMovements, function($movement) use ($location) {
                return $movement['from_location'] == $location || $movement['to_location'] == $location;
            });

            if (!empty($locationMovements)) {
                $height += $this->GetProductHeight($product, $locationMovements);
            }
        }

        return $height;
    }
}

// Fetch all locations
$locationsQuery = "SELECT location FROM locations WHERE location_id IN (" . implode(',', array_map(function($k) { return ':loc'.$k; }, array_keys($warehouseIds))) . ")";
$stmt = $conn->prepare($locationsQuery);
foreach ($warehouseIds as $k => $id) {
    $stmt->bindValue(':loc'.$k, $id);
}
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_COLUMN);

$pdf = new PDF($settings, $locations);
$pdf->startDate = $startDate;
$pdf->AliasNbPages();
$pdf->AddPage();

// Report header
$pdf->SetFont('THSarabunNew', 'B', 12);
$pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'วันสิ้นสุดรายงาน:'), 0);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->Cell(30, 5, formatThaiDate($endDate), 0);
$pdf->SetFont('THSarabunNew', 'B', 12);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์รายงาน:'), 0);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);

// Add warehouse names
$pdf->SetFont('THSarabunNew', 'B', 12);
$pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'คลังสินค้า:'), 0);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->MultiCell(0, 5, iconv('UTF-8', 'cp874', implode(', ', $locations)), 0);
$pdf->Ln(5);

// Fetch products and opening balances
// Fetch products and opening balances
$productsQuery = "
SELECT p.product_id, p.name_th, p.name_en, p.unit,
   COALESCE(SUM(
       CASE 
           WHEN m.type = 'receive' THEN m.quantity
           WHEN m.type = 'issue' THEN -m.quantity
           WHEN m.type = 'transfer_in' THEN m.quantity
           WHEN m.type = 'transfer_out' THEN -m.quantity
           ELSE 0
       END
   ), 0) AS opening_balance
FROM products p
LEFT JOIN (
SELECT 'receive' as type, dr.product_id, dr.quantity, dr.location_id
FROM d_receive dr
JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
WHERE hr.received_date < :startDate

UNION ALL

SELECT 'issue' as type, di.product_id, di.quantity, di.location_id
FROM d_issue di
JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
WHERE hi.issue_date < :startDate

UNION ALL

SELECT 'transfer_out' as type, dt.product_id, dt.quantity, dt.from_location_id AS location_id
FROM d_transfer dt
JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
WHERE ht.transfer_date < :startDate

UNION ALL

SELECT 'transfer_in' as type, dt.product_id, dt.quantity, dt.to_location_id AS location_id
FROM d_transfer dt
JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
WHERE ht.transfer_date < :startDate
) AS m ON p.product_id = m.product_id AND m.location_id IN (" . implode(',', array_map(function($k) { return ':wh'.$k; }, array_keys($warehouseIds))) . ")
GROUP BY p.product_id, p.name_th, p.name_en, p.unit
HAVING opening_balance > 0 OR p.product_id IN (
SELECT DISTINCT product_id 
FROM (
    SELECT product_id FROM d_receive dr JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id WHERE hr.received_date BETWEEN :startDate AND :endDate AND dr.location_id IN (" . implode(',', array_map(function($k) { return ':whr'.$k; }, array_keys($warehouseIds))) . ")
    UNION
    SELECT product_id FROM d_issue di JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id WHERE hi.issue_date BETWEEN :startDate AND :endDate AND di.location_id IN (" . implode(',', array_map(function($k) { return ':whi'.$k; }, array_keys($warehouseIds))) . ")
    UNION
    SELECT product_id FROM d_transfer dt JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id WHERE ht.transfer_date BETWEEN :startDate AND :endDate AND (dt.from_location_id IN (" . implode(',', array_map(function($k) { return ':whf'.$k; }, array_keys($warehouseIds))) . ") OR dt.to_location_id IN (" . implode(',', array_map(function($k) { return ':wht'.$k; }, array_keys($warehouseIds))) . "))
) AS active_products
)
ORDER BY p.product_id
";

$stmt = $conn->prepare($productsQuery);
$stmt->bindValue(':startDate', $startDate);
$stmt->bindValue(':endDate', $endDate);
foreach ($warehouseIds as $k => $id) {
    $stmt->bindValue(':wh'.$k, $id);
    $stmt->bindValue(':whr'.$k, $id);
    $stmt->bindValue(':whi'.$k, $id);
    $stmt->bindValue(':whf'.$k, $id);
    $stmt->bindValue(':wht'.$k, $id);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch movements
$movementsQuery = "
SELECT 
p.product_id,
DATE(movement_date) as date,
type,
quantity,
COALESCE(from_location.location, '') as from_location,
COALESCE(to_location.location, '') as to_location
FROM (
SELECT hr.received_date as movement_date, 'receive' as type, dr.quantity, NULL as from_location_id, dr.location_id as to_location_id, dr.product_id
FROM d_receive dr
JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
WHERE hr.received_date BETWEEN :startDate AND :endDate AND dr.location_id IN (" . implode(',', array_map(function($k) { return ':whm'.$k; }, array_keys($warehouseIds))) . ")

UNION ALL

SELECT hi.issue_date as movement_date, 'issue' as type, di.quantity, di.location_id as from_location_id, NULL as to_location_id, di.product_id
FROM d_issue di
JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
WHERE hi.issue_date BETWEEN :startDate AND :endDate AND di.location_id IN (" . implode(',', array_map(function($k) { return ':whi'.$k; }, array_keys($warehouseIds))) . ")

UNION ALL

SELECT 
    ht.transfer_date as movement_date, 
    'transfer' as type,
    dt.quantity, 
    dt.from_location_id,
    dt.to_location_id,
    dt.product_id
FROM d_transfer dt
JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
WHERE ht.transfer_date BETWEEN :startDate AND :endDate 
AND (dt.from_location_id IN (" . implode(',', array_map(function($k) { return ':whf'.$k; }, array_keys($warehouseIds))) . ")
     OR dt.to_location_id IN (" . implode(',', array_map(function($k) { return ':wht'.$k; }, array_keys($warehouseIds))) . "))
) AS combined_movements
JOIN products p ON combined_movements.product_id = p.product_id
LEFT JOIN locations as from_location ON combined_movements.from_location_id = from_location.location_id
LEFT JOIN locations as to_location ON combined_movements.to_location_id = to_location.location_id
WHERE (combined_movements.from_location_id IN (" . implode(',', array_map(function($k) { return ':whcf'.$k; }, array_keys($warehouseIds))) . ")
   OR combined_movements.to_location_id IN (" . implode(',', array_map(function($k) { return ':whct'.$k; }, array_keys($warehouseIds))) . "))
ORDER BY p.product_id, date, type
";

$stmt = $conn->prepare($movementsQuery);
$stmt->bindValue(':startDate', $startDate);
$stmt->bindValue(':endDate', $endDate);
foreach ($warehouseIds as $k => $id) {
    $stmt->bindValue(':whm'.$k, $id);
    $stmt->bindValue(':whi'.$k, $id);
    $stmt->bindValue(':whf'.$k, $id);
    $stmt->bindValue(':wht'.$k, $id);
    $stmt->bindValue(':whcf'.$k, $id);
    $stmt->bindValue(':whct'.$k, $id);
}
$stmt->execute();
$allMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group movements by product
$movements = [];
foreach ($allMovements as $movement) {
    $productId = $movement['product_id'];
    if (!isset($movements[$productId])) {
        $movements[$productId] = [];
    }
    $movements[$productId][] = $movement;
}

// Generate report
$pdf->WarehouseMovementTable($products, $movements);

$pdf->Output('I', 'warehouse_movement_report.pdf');