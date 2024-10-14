<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_reports']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

// ตรวจสอบพารามิเตอร์ที่จำเป็น
if (!isset($_GET['reportType']) || !isset($_GET['endDate'])) {
    die('Required parameters are missing');
}

$reportType = $_GET['reportType'];
$endDate = $_GET['endDate'];
$startDate = date('Y-m-01', strtotime($endDate)); // First day of the end date month

if ($reportType === 'product') {
    if (!isset($_GET['startProductId']) || !isset($_GET['endProductId'])) {
        die('Product IDs are required for product report type');
    }
    $startProductId = $_GET['startProductId'];
    $endProductId = $_GET['endProductId'];
} elseif ($reportType === 'category') {
    if (!isset($_GET['categoryId'])) {
        die('Category ID is required for category report type');
    }
    $categoryId = $_GET['categoryId'];
    $typeId = $_GET['typeId'] ?? null;
} else {
    die('Invalid report type');
}

function formatThaiDate($date) {
    $timestamp = strtotime($date);
    $thai_year = date('Y', $timestamp) + 543;
    return date('d-m-', $timestamp) . $thai_year;
}

function swapIfNeeded(&$start, &$end) {
    if ($start > $end) {
        $temp = $start;
        $start = $end;
        $end = $temp;
    }
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

    function __construct($settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }

    // เพิ่มฟังก์ชันใหม่สำหรับตรวจสอบประเภทของไฟล์รูปภาพ
    private function getImageFileType($file) {
        $size = getimagesize($file);
        if ($size === false) {
            return false;
        }
        $extension = image_type_to_extension($size[2], false);
        return strtoupper($extension); // ส่งคืนนามสกุลไฟล์เป็นตัวพิมพ์ใหญ่ (JPG, PNG, GIF)
    }

    function Header()
    {
        $this->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $this->AddFont('THSarabunNew', 'B', 'THSarabunNew_b.php');
        $this->SetFont('THSarabunNew', 'B', 18);
        
        $logoPath = isset($this->settings['logo']) ? '../assets/img/' . $this->settings['logo'] : '../assets/img/logo.png';
        
        // ตรวจสอบว่าไฟล์มีอยู่จริง
        if (file_exists($logoPath)) {
            $imageType = $this->getImageFileType($logoPath);
            if ($imageType) {
                // ใช้ Image() แทน โดยระบุประเภทของไฟล์
                $this->Image($logoPath, 5, 6, 30, 0, $imageType);
            } else {
                // ถ้าไม่สามารถระบุประเภทของไฟล์ได้ ให้แสดงข้อความแทน
                $this->SetXY(5, 6);
                $this->Cell(30, 10, 'Logo Error', 1, 0, 'C');
            }
        } else {
            // ถ้าไม่พบไฟล์ ให้แสดงข้อความแทน
            $this->SetXY(5, 6);
            $this->Cell(30, 10, 'No Logo', 1, 0, 'C');
        }
        
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_name'] ?? ''), 0, 1, 'C');
        $this->SetFont('THSarabunNew', '', 11);
        $this->Cell(0, 7, iconv('UTF-8', 'cp874', $this->settings['company_address'] ?? ''), 0, 1, 'C');
        $this->Cell(0, 7, iconv('UTF-8', 'cp874', $this->settings['company_contact'] ?? ''), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('THSarabunNew', 'B', 14);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายงานการเคลื่อนไหวสินค้า'), 0, 1, 'C');
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

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function ProductTable($product, $movements, $openingBalances)
    {
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า: ' . $product['product_id'] . ' - ' . ($product['name_th'] ?? $product['name_en'])), 0, 1);
        $this->Ln(2);
    
        // Table header
        $this->SetFillColor(240, 240, 240);
        $this->SetFont('THSarabunNew', 'B', 11);
        $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'วันที่'), 1, 0, 'C', true);
        $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'ตำแหน่ง'), 1, 0, 'C', true);
        $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รายการ'), 1, 0, 'C', true);
        $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รับ'), 1, 0, 'C', true);
        $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'เบิก'), 1, 0, 'C', true);
        $this->Cell(35, 5, iconv('UTF-8', 'cp874', 'โอนย้าย'), 1, 0, 'C', true);
        $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'คงเหลือ'), 1, 0, 'C', true);
        $this->Cell(10, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 1, 'C', true);
    
        $this->SetFont('THSarabunNew', '', 11);
        $totalOpeningBalance = 0;
        $totalReceive = 0;
        $totalIssue = 0;
    
        // Opening balance rows for locations with non-zero balance
        foreach ($openingBalances as $locationId => $balance) {
            if ($balance != 0) {
                $this->Cell(20, 5, formatThaiDate($this->startDate), 1, 0, 'C');
                $this->Cell(25, 5, iconv('UTF-8', 'cp874', $this->locations[$locationId] ?? '-'), 1, 0, 'C');
                $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'ยอดยกมา'), 1, 0, 'C');
                $this->Cell(25, 5, '-', 1, 0, 'C');
                $this->Cell(25, 5, '-', 1, 0, 'C');
                $this->Cell(35, 5, '-', 1, 0, 'C');
                $this->Cell(25, 5, number_format($balance, 2), 1, 0, 'R');
                $this->Cell(10, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
                $totalOpeningBalance += $balance;
            }
        }
    
        // Total opening balance row (only if there's a non-zero opening balance)
        if ($totalOpeningBalance != 0) {
            $this->SetFont('THSarabunNew', 'B', 11);
            $this->Cell(70, 5, iconv('UTF-8', 'cp874', 'รวมยอดยกมา'), 1, 0, 'R');
            $this->Cell(25, 5, '-', 1, 0, 'C');
            $this->Cell(25, 5, '-', 1, 0, 'C');
            $this->Cell(35, 5, '-', 1, 0, 'C');
            $this->Cell(25, 5, number_format($totalOpeningBalance, 2), 1, 0, 'R');
            $this->Cell(10, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
        }
        
        $this->SetFont('THSarabunNew', '', 11);
        $runningBalance = $totalOpeningBalance;
    
        foreach ($movements as $movement) {
            $this->CheckPageBreak(5);
            
            $quantity = floatval($movement['quantity']);
            $transferText = '';
            
            $this->Cell(20, 5, formatThaiDate($movement['date']), 1, 0, 'C');
            
            $location = '-';
            switch($movement['type']) {
                case 'receive':
                    $runningBalance += $quantity;
                    $totalReceive += $quantity;
                    $location = $this->locations[$movement['to_location']] ?? '-';
                    $this->Cell(25, 5, iconv('UTF-8', 'cp874', $location), 1, 0, 'C');
                    $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รับสินค้า'), 1, 0, 'C');
                    $this->Cell(25, 5, number_format($quantity, 2), 1, 0, 'R');
                    $this->Cell(25, 5, '-', 1, 0, 'C');
                    $this->Cell(35, 5, '-', 1, 0, 'C');
                    break;
                case 'issue':
                    $runningBalance -= $quantity;
                    $totalIssue += $quantity;
                    $location = $this->locations[$movement['from_location']] ?? '-';
                    $this->Cell(25, 5, iconv('UTF-8', 'cp874', $location), 1, 0, 'C');
                    $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'เบิกสินค้า'), 1, 0, 'C');
                    $this->Cell(25, 5, '-', 1, 0, 'C');
                    $this->Cell(25, 5, number_format($quantity, 2), 1, 0, 'R');
                    $this->Cell(35, 5, '-', 1, 0, 'C');
                    break;
                case 'transfer':
                    $fromLocation = $this->locations[$movement['from_location']] ?? '-';
                    $toLocation = $this->locations[$movement['to_location']] ?? '-';
                    $transferText = $fromLocation . ' -> ' . $toLocation;
                    $this->Cell(25, 5, iconv('UTF-8', 'cp874', $fromLocation), 1, 0, 'C');
                    $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'โอนย้าย'), 1, 0, 'C');
                    $this->Cell(25, 5, '-', 1, 0, 'C');
                    $this->Cell(25, 5, '-', 1, 0, 'C');
                    $this->Cell(35, 5, iconv('UTF-8', 'cp874', $transferText), 1, 0, 'C');
                    break;
            }
    
            $this->Cell(25, 5, number_format($runningBalance, 2), 1, 0, 'R');
            $this->Cell(10, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
        }
    
        // Total row
        $this->SetFont('THSarabunNew', 'B', 11);
        $this->Cell(70, 5, iconv('UTF-8', 'cp874', 'รวมทั้งสิ้น'), 1, 0, 'R');
        $this->Cell(25, 5, number_format($totalReceive, 2), 1, 0, 'R');
        $this->Cell(25, 5, number_format($totalIssue, 2), 1, 0, 'R');
        $this->Cell(35, 5, '-', 1, 0, 'C');
        $this->Cell(25, 5, number_format($runningBalance, 2), 1, 0, 'R');
        $this->Cell(10, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 1, 'C');
    
        $this->Ln(5);
    }
}
$pdf = new PDF($settings);
$pdf->startDate = $startDate;
$pdf->AliasNbPages();
$pdf->AddPage();

// Report header
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'วันที่เริ่ม:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(30, 5, formatThaiDate($startDate), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'วันที่สิ้นสุด:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(30, 5, formatThaiDate($endDate), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์รายงาน:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);
$pdf->Ln(5);

// Fetch products based on report type
if ($reportType === 'product') {
    swapIfNeeded($startProductId, $endProductId);
    $productQuery = "SELECT product_id, name_th, name_en, unit FROM products WHERE product_id BETWEEN :startId AND :endId ORDER BY product_id";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->execute([':startId' => $startProductId, ':endId' => $endProductId]);
} elseif ($reportType === 'category') {
    $productQuery = "SELECT p.product_id, p.name_th, p.name_en, p.unit 
                     FROM products p
                     JOIN product_cate pc ON p.product_type_id = pc.category_id
                     WHERE pc.product_category_id = :categoryId";
    if ($typeId) {
        $productQuery .= " AND pc.category_id = :typeId";
    }
    $productQuery .= " ORDER BY p.product_id";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->bindParam(':categoryId', $categoryId);
    if ($typeId) {
        $productStmt->bindParam(':typeId', $typeId);
    }
    $productStmt->execute();
}

$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations
$locationsQuery = "SELECT location_id, location FROM locations";
$stmt = $conn->query($locationsQuery);
$locations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$pdf->locations = $locations;

// Add report type information
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ประเภทรายงาน:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $reportType === 'product' ? 'ตามรหัสสินค้า' : 'ตามหมวดหมู่'), 0, 1);

if ($reportType === 'product') {
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ช่วงรหัสสินค้า:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, $startProductId . ' - ' . $endProductId, 0, 1);
} elseif ($reportType === 'category') {
    $categoryQuery = "SELECT name FROM product_types WHERE type_id = :categoryId";
    $stmt = $conn->prepare($categoryQuery);
    $stmt->execute([':categoryId' => $categoryId]);
    $categoryName = $stmt->fetchColumn();

    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'หมวดหมู่:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, iconv('UTF-8','cp874', $categoryName), 0, 1);
    if ($typeId) {
        $typeQuery = "SELECT name FROM product_cate WHERE category_id = :typeId";
        $stmt = $conn->prepare($typeQuery);
        $stmt->execute([':typeId' => $typeId]);
        $typeName = $stmt->fetchColumn();
    
        $pdf->SetFont('THSarabunNew', 'B', 11);
        $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ประเภทย่อย:'), 0);
        $pdf->SetFont('THSarabunNew', '', 11);
        $pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $typeName), 0, 1);
    }
}
$pdf->Ln(5);

foreach ($products as $product) {
    // Check if we need to start a new page
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    
    // Calculate opening balance per location
   // Calculate opening balance per location
$openingBalanceQuery = "
SELECT 
    l.location_id,
    l.location AS location_name,
    COALESCE(SUM(
        CASE 
            WHEN type = 'receive' THEN quantity
            WHEN type = 'issue' THEN -quantity
            WHEN type = 'transfer_in' THEN quantity
            WHEN type = 'transfer_out' THEN -quantity
            ELSE 0
        END
    ), 0) AS opening_balance
FROM locations l
LEFT JOIN (
    SELECT 'receive' as type, dr.quantity, dr.location_id
    FROM d_receive dr
    JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
    WHERE dr.product_id = :productId AND hr.received_date < :startDate

    UNION ALL

    SELECT 'issue' as type, di.quantity, di.location_id
    FROM d_issue di
    JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
    WHERE di.product_id = :productId AND hi.issue_date < :startDate

    UNION ALL

    SELECT 'transfer_out' as type, dt.quantity, dt.from_location_id AS location_id
    FROM d_transfer dt
    JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
    WHERE dt.product_id = :productId AND ht.transfer_date < :startDate

    UNION ALL

    SELECT 'transfer_in' as type, dt.quantity, dt.to_location_id AS location_id
    FROM d_transfer dt
    JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
    WHERE dt.product_id = :productId AND ht.transfer_date < :startDate
) AS combined_movements ON l.location_id = combined_movements.location_id
GROUP BY l.location_id, l.location
";

$stmt = $conn->prepare($openingBalanceQuery);
$stmt->execute([':productId' => $product['product_id'], ':startDate' => $startDate]);
$openingBalancesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$openingBalances = [];
foreach ($openingBalancesData as $balance) {
    $openingBalances[$balance['location_id']] = $balance['opening_balance'];
}

    // Fetch movements
    $movementsQuery = "
  SELECT 
    DATE(movement_date) as date,
    type,
    quantity,
    from_location,
    to_location
FROM (
    SELECT hr.received_date as movement_date, 'receive' as type, dr.quantity, NULL as from_location, dr.location_id as to_location
    FROM d_receive dr
    JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
    WHERE dr.product_id = :productId AND hr.received_date BETWEEN :startDate AND :endDate

    UNION ALL

    SELECT hi.issue_date as movement_date, 'issue' as type, di.quantity, di.location_id as from_location, NULL as to_location
    FROM d_issue di
    JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
    WHERE di.product_id = :productId AND hi.issue_date BETWEEN :startDate AND :endDate

    UNION ALL

    SELECT 
        ht.transfer_date as movement_date, 
        'transfer' as type,
        dt.quantity, 
        dt.from_location_id as from_location,
        dt.to_location_id as to_location
    FROM d_transfer dt
    JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
    WHERE dt.product_id = :productId AND ht.transfer_date BETWEEN :startDate AND :endDate
) AS combined_movements
ORDER BY date, type
    ";

    $stmt = $conn->prepare($movementsQuery);
    $stmt->execute([':productId' => $product['product_id'], ':startDate' => $startDate, ':endDate' => $endDate]);
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf->ProductTable($product, $movements, $openingBalances);
}

$pdf->Output('I', 'product_movement_report.pdf');
?>