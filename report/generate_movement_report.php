<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_reports']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

if (!isset($_GET['startProductId']) || !isset($_GET['endProductId']) || !isset($_GET['endDate'])) {
    die('Required parameters are missing');
}

$startProductId = $_GET['startProductId'];
$endProductId = $_GET['endProductId'];
$endDate = $_GET['endDate'];
$startDate = date('Y-m-01', strtotime($endDate)); // First day of the end date month

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

    function __construct($settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }

    function Header()
    {
        $this->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $this->AddFont('THSarabunNew', 'B', 'THSarabunNew_b.php');
        $this->SetFont('THSarabunNew', 'B', 18);
        
        $logoPath = isset($this->settings['logo']) ? '../assets/img/' . $this->settings['logo'] : '../assets/img/logo.png';
        $this->Image($logoPath, 5, 6, 30);
        
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
}

$pdf = new PDF($settings);
$pdf->AliasNbPages();

// Fetch products
$productQuery = "SELECT product_id, name_th, name_en, unit FROM products WHERE product_id BETWEEN :startId AND :endId ORDER BY product_id";
$productStmt = $conn->prepare($productQuery);
$productStmt->execute([':startId' => $startProductId, ':endId' => $endProductId]);
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations
$locationsQuery = "SELECT location_id, location FROM locations";
$stmt = $conn->query($locationsQuery);
$locations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($products as $product) {
    $pdf->AddPage();
    
    // Product header
    $pdf->SetFont('THSarabunNew', 'B', 12);
    $pdf->Cell(0, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า: ' . $product['product_id'] . ' - ' . ($product['name_th'] ?? $product['name_en'])), 0, 1);

    // Report header
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'วันที่เริ่ม:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(50, 5, formatThaiDate($startDate), 0);
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'วันที่สิ้นสุด:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, formatThaiDate($endDate), 0, 1);

    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์รายงาน:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, formatThaiDate(date('Y-m-d')), 0, 1);

    $pdf->Ln(2);

    // Table header
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'วันที่'), 1, 0, 'C', true);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'รับ'), 1, 0, 'C', true);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'เบิก'), 1, 0, 'C', true);
    $pdf->Cell(50, 5, iconv('UTF-8', 'cp874', 'โอนย้าย'), 1, 0, 'C', true);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'คงเหลือ'), 1, 0, 'C', true);
    $pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
    $pdf->Ln();

    // Calculate opening balance
    $openingBalanceQuery = "
    SELECT 
        COALESCE(SUM(
            CASE 
                WHEN type = 'receive' THEN quantity
                WHEN type = 'issue' THEN -quantity
                WHEN type = 'transfer_in' THEN quantity
                WHEN type = 'transfer_out' THEN -quantity
                ELSE 0
            END
        ), 0) AS opening_balance
    FROM (
        SELECT 'receive' as type, dr.quantity
        FROM d_receive dr
        JOIN h_receive hr ON dr.receive_header_id = hr.receive_header_id
        WHERE dr.product_id = :productId AND hr.received_date < :startDate

        UNION ALL

        SELECT 'issue' as type, di.quantity
        FROM d_issue di
        JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
        WHERE di.product_id = :productId AND hi.issue_date < :startDate

        UNION ALL

        SELECT 
            CASE 
                WHEN ht.from_location_id = l.location_id THEN 'transfer_out'
                WHEN ht.to_location_id = l.location_id THEN 'transfer_in'
            END as type,
            dt.quantity
        FROM d_transfer dt
        JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
        CROSS JOIN locations l
        WHERE dt.product_id = :productId AND ht.transfer_date < :startDate
    ) AS combined_movements
    ";

    $stmt = $conn->prepare($openingBalanceQuery);
    $stmt->execute([':productId' => $product['product_id'], ':startDate' => $startDate]);
    $openingBalance = $stmt->fetchColumn() ?: 0;

    // Fetch movements
    $movementsQuery = "
    SELECT 
        DATE(movement_date) as date,
        type,
        SUM(quantity) as quantity,
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
            ht.from_location_id as from_location,
            ht.to_location_id as to_location
        FROM d_transfer dt
        JOIN h_transfer ht ON dt.transfer_header_id = ht.transfer_header_id
        WHERE dt.product_id = :productId AND ht.transfer_date BETWEEN :startDate AND :endDate
    ) AS combined_movements
    GROUP BY DATE(movement_date), type, from_location, to_location
    ORDER BY DATE(movement_date), type
    ";

    $stmt = $conn->prepare($movementsQuery);
    $stmt->execute([':productId' => $product['product_id'], ':startDate' => $startDate, ':endDate' => $endDate]);
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf->SetFont('THSarabunNew', '', 11);
    $runningBalance = $openingBalance;

    // Opening balance row
    $pdf->Cell(25, 5, formatThaiDate($startDate), 1, 0, 'C');
    $pdf->Cell(30, 5, '-', 1, 0, 'C');
    $pdf->Cell(30, 5, '-', 1, 0, 'C');
    $pdf->Cell(50, 5, iconv('UTF-8', 'cp874', 'ยอดยกมา'), 1, 0, 'C');
    $pdf->Cell(30, 5, number_format($openingBalance, 2), 1, 0, 'R');
    $pdf->Cell(25, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 0, 'C');
    $pdf->Ln();

    foreach ($movements as $movement) {
        $quantity = floatval($movement['quantity']);
        $transferText = '';
        
        switch($movement['type']) {
            case 'receive':
                $runningBalance += $quantity;
                $pdf->Cell(25, 5, formatThaiDate($movement['date']), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($quantity, 2), 1, 0, 'R');
                $pdf->Cell(30, 5, '-', 1, 0, 'C');
                $pdf->Cell(50, 5, '-', 1, 0, 'C');
                break;
            case 'issue':
                $runningBalance -= $quantity;
                $pdf->Cell(25, 5, formatThaiDate($movement['date']), 1, 0, 'C');
                $pdf->Cell(30, 5, '-', 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($quantity, 2), 1, 0, 'R');
                $pdf->Cell(50, 5, '-', 1, 0, 'C');
                break;
            case 'transfer':
                $fromLocation = $locations[$movement['from_location']] ?? 'ไม่ทราบ';
                $toLocation = $locations[$movement['to_location']] ?? 'ไม่ทราบ';
                $transferText = $quantity . ' (' . $fromLocation . ' --> ' . $toLocation . ')';
                $pdf->Cell(25, 5, formatThaiDate($movement['date']), 1, 0, 'C');
                $pdf->Cell(30, 5, '-', 1, 0, 'C');
                $pdf->Cell(30, 5, '-', 1, 0, 'C');
                $pdf->Cell(50, 5, iconv('UTF-8', 'cp874', $transferText), 1, 0, 'C');
                break;
        }

        $pdf->Cell(30, 5, number_format($runningBalance, 2), 1, 0, 'R');
        $pdf->Cell(25, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 0, 'C');
        $pdf->Ln();

        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
        }
    }

    $pdf->Ln(5);
}

$pdf->Output('I', 'product_movement_report.pdf');
?>