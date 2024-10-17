<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_issue']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Issue ID is required and must not be empty');
}

$issueId = intval($_GET['id']); 

if ($issueId <= 0) {
    die('Invalid Issue ID');
}

// Fetch website settings
function getWebsiteSettings($conn) {
    try {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM website_settings");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        return [];
    }
}

$settings = getWebsiteSettings($conn);

$query = "
SELECT 
    hi.bill_number,
    hi.issue_date,
    hi.issue_type,
    u.fname AS issuer_fname,
    u.lname AS issuer_lname,
    CASE 
        WHEN hi.issue_type = 'sale' THEN c.name
        ELSE p.project_name
    END AS customer_project_name,
    CASE
        WHEN hi.issue_type = 'sale' THEN c.address
        ELSE p.project_description
    END AS customer_address_project_description,
    c.phone_number AS customer_phone,
    c.tax_id AS customer_tax_id,
    c.contact_person AS customer_contact,
    c.credit_limit AS customer_credit_limit,
    c.credit_terms AS customer_credit_terms,
    p.start_date AS project_start_date,
    p.end_date AS project_end_date,
    di.product_id,
    pr.name_th AS product_name,
    di.quantity,
    pr.unit,
    l.location AS location_name,
    i.quantity AS current_quantity
FROM 
    h_issue hi
JOIN d_issue di ON hi.issue_header_id = di.issue_header_id
JOIN products pr ON di.product_id = pr.product_id
JOIN users u ON hi.user_id = u.UserID
JOIN locations l ON di.location_id = l.location_id
LEFT JOIN customers c ON hi.customer_id = c.customer_id
LEFT JOIN projects p ON hi.project_id = p.project_id
LEFT JOIN inventory i ON di.product_id = i.product_id AND di.location_id = i.location_id
WHERE hi.issue_header_id = :issue_id
";

$result = dd_q($query, [':issue_id' => $issueId]);
$issueData = $result->fetchAll(PDO::FETCH_ASSOC);

if (empty($issueData)) {
    die('Issue data not found');
}

class PDF extends FPDF
{
    private $settings;

    function __construct($settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }

    private function getImageFileType($file) {
        if (!file_exists($file)) {
            return false;
        }
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
                $this->SetFont('THSarabunNew', '', 10);
                $this->Cell(30, 10, iconv('UTF-8', 'cp874', 'โลโก้ไม่ถูกต้อง'), 1, 0, 'C');
            }
        } else {
            $this->SetXY(5, 6);
            $this->SetFont('THSarabunNew', '', 10);
            $this->Cell(30, 10, iconv('UTF-8', 'cp874', 'ไม่พบโลโก้'), 1, 0, 'C');
        }
        
        $this->SetFont('THSarabunNew', 'B', 18);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_name'] ?? ''), 0, 1, 'C');
        $this->SetFont('THSarabunNew', '', 11);
        $this->Cell(0, 7, iconv('UTF-8', 'cp874', $this->settings['company_address'] ?? ''), 0, 1, 'C');
        $this->Cell(0, 7, iconv('UTF-8', 'cp874', $this->settings['company_contact'] ?? ''), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('THSarabunNew', 'B', 14);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'เอกสารเบิกสินค้า'), 0, 1, 'C');
        $this->SetLineWidth(0.1);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        if ($this->PageNo() > 1) {
            $this->SetFont('THSarabunNew', 'B', 11);
            $this->SetFillColor(240, 240, 240);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
            $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
            $this->Cell(60, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
            $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'คลัง'), 1, 0, 'C', true);
            $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
            $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
            $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'คงเหลือ'), 1, 0, 'C', true);
            $this->Ln();
        }
    }
}

$pdf = new PDF($settings);
$pdf->AliasNbPages();
$pdf->AddPage();

// ส่วนหัวของเอกสาร
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'เลขที่เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, $issueData[0]['bill_number'], 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่เบิก:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y', strtotime($issueData[0]['issue_date'])), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ผู้เบิกสินค้า:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $issueData[0]['issuer_fname'] . ' ' . $issueData[0]['issuer_lname']), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ประเภทการเบิก:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', ($issueData[0]['issue_type'] == 'sale' ? 'เบิกเพื่อขาย' : 'เบิกเพื่อโครงการ')), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', ($issueData[0]['issue_type'] == 'sale' ? 'ลูกค้า:' : 'โครงการ:')), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $issueData[0]['customer_project_name']), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', ($issueData[0]['issue_type'] == 'sale' ? 'ที่อยู่:' : 'รายละเอียด:')), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->MultiCell(0, 5, iconv('UTF-8', 'cp874', $issueData[0]['customer_address_project_description']), 0);

if ($issueData[0]['issue_type'] == 'sale') {
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'เบอร์โทร:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $issueData[0]['customer_phone'] ?? '-'), 0);
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'เลขประจำตัวผู้เสียภาษี:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $issueData[0]['customer_tax_id'] ?? '-'), 0, 1);

    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ผู้ติดต่อ:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $issueData[0]['customer_contact'] ?? '-'), 0);
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วงเงินเครดิต:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, iconv('UTF-8', 'cp874', ($issueData[0]['customer_credit_limit'] !== null ? number_format($issueData[0]['customer_credit_limit'], 2) . ' บาท' : '-')), 0, 1);
    
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'เงื่อนไขการชำระเงิน:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, iconv('UTF-8', 'cp874', ($issueData[0]['customer_credit_terms'] !== null && $issueData[0]['customer_credit_terms'] !== '' ? $issueData[0]['customer_credit_terms'] . ' วัน' : '-')), 0, 1);
} else {
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่เริ่มโครงการ:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $issueData[0]['project_start_date'] ? date('d/m/Y', strtotime($issueData[0]['project_start_date'])) : '-'), 0);
    $pdf->SetFont('THSarabunNew', 'B', 11);
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่สิ้นสุดโครงการ:'), 0);
    $pdf->SetFont('THSarabunNew', '', 11);
    $pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $issueData[0]['project_end_date'] ? date('d/m/Y', strtotime($issueData[0]['project_end_date'])) : '-'), 0, 1);
}

$pdf->Ln(5);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายการสินค้าที่เบิก'), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
$pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
$pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'คลัง'), 1, 0, 'C', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', 'คงเหลือ'), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetFont('THSarabunNew', '', 11);
$total = 0;
$i = 1;
foreach ($issueData as $item) {
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    
    $pdf->Cell(15, 5, $i, 1, 0, 'C');
    $pdf->Cell(25, 5, $item['product_id'], 1, 0, 'C');
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $item['product_name']), 1);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', $item['location_name']), 1, 0, 'C');
    $pdf->Cell(20, 5, number_format($item['quantity'], 2), 1, 0, 'R');
    $pdf->Cell(20, 5, iconv('UTF-8', 'cp874', $item['unit']), 1, 0, 'C');
    $pdf->Cell(20, 5, number_format($item['current_quantity'], 2), 1, 0, 'R');
    $pdf->Ln();
    $total += $item['quantity'];
    $i++;
}

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(130, 5, iconv('UTF-8', 'cp874', 'รวมทั้งสิ้น'), 1, 0, 'R', true);
$pdf->Cell(20, 5, number_format($total, 2), 1, 0, 'R', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', '-'), 1, 0, 'C', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', '-'), 1, 0, 'C', true);
$pdf->Ln(20);

// ตรวจสอบว่ามีพื้นที่เพียงพอสำหรับลายเซ็นหรือไม่ ถ้าไม่พอให้ขึ้นหน้าใหม่
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}

// ส่วนลายเซ็น
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้เบิกสินค้า'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้จ่ายสินค้า'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้อนุมัติ'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 1, 'C');
$pdf->Ln(3);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 1, 'C');


$pdf->Output('I', 'issue_report.pdf');
?>