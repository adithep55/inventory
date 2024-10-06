<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_receiving']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

if (!isset($_GET['id'])) {
    die('Receive ID is required');
}

$receiveId = $_GET['id'];

// Fetch website settings
function getWebsiteSettings($conn) {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM website_settings");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$settings = getWebsiteSettings($conn);

// Query to fetch receive details
$query = "
SELECT 
    hr.receive_header_id,
    hr.bill_number,
    hr.received_date,
    u.fname AS receiver_fname,
    u.lname AS receiver_lname,
    d.product_id,
    p.name_th AS product_name,
    d.quantity,
    p.unit,
    l.location AS location_name,
    hr.updated_at
FROM 
    h_receive hr
JOIN d_receive d ON hr.receive_header_id = d.receive_header_id
JOIN products p ON d.product_id = p.product_id
JOIN users u ON hr.user_id = u.UserID
JOIN locations l ON d.location_id = l.location_id
WHERE hr.receive_header_id = :receive_id
";

$result = dd_q($query, [':receive_id' => $receiveId]);
$receiveData = $result->fetchAll(PDO::FETCH_ASSOC);

if (empty($receiveData)) {
    die('Receive data not found');
}

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
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'เอกสารการรับสินค้า'), 0, 1, 'C');
        $this->SetLineWidth(0.1);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        if ($this->PageNo() > 1) {
            $this->SetFont('THSarabunNew', 'B', 11);
            $this->SetFillColor(240, 240, 240);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
            $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
            $this->Cell(60, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
            $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
            $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
            $this->Cell(40, 5, iconv('UTF-8', 'cp874', 'สถานที่จัดเก็บ'), 1, 0, 'C', true);
            $this->Ln();
        }
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
$pdf->AddPage();

// ส่วนหัวของเอกสาร
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'เลขที่เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, $receiveData[0]['bill_number'], 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่รับสินค้า:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y', strtotime($receiveData[0]['received_date'])), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ผู้รับสินค้า:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $receiveData[0]['receiver_fname'] . ' ' . $receiveData[0]['receiver_lname']), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);

$pdf->Ln(5);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายการสินค้าที่รับ'), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
$pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
$pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
$pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'สถานที่จัดเก็บ'), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetFont('THSarabunNew', '', 11);
$total = 0;
$i = 1;
foreach ($receiveData as $item) {
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    $pdf->Cell(15, 5, $i, 1, 0, 'C');
    $pdf->Cell(25, 5, $item['product_id'], 1, 0, 'C');
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $item['product_name']), 1);
    $pdf->Cell(25, 5, number_format($item['quantity'], 2), 1, 0, 'R');
    $pdf->Cell(25, 5, iconv('UTF-8', 'cp874', $item['unit']), 1, 0, 'C');
    $pdf->Cell(40, 5, iconv('UTF-8', 'cp874', $item['location_name']), 1, 0, 'C');
    $pdf->Ln();
    $total += $item['quantity'];
    $i++;
}

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(100, 5, iconv('UTF-8', 'cp874', 'รวมทั้งสิ้น'), 1, 0, 'R', true);
$pdf->Cell(25, 5, number_format($total, 2), 1, 0, 'R', true);
$pdf->Cell(65, 5, '', 1, 0, 'C', true);
$pdf->Ln(20);

// ตรวจสอบว่ามีพื้นที่เพียงพอสำหรับลายเซ็นหรือไม่ ถ้าไม่พอให้ขึ้นหน้าใหม่
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}

// ส่วนลายเซ็น
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้จัดทำเอกสาร'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้รับสินค้า'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้ตรวจสอบ'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 1, 'C');
$pdf->Ln(3);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 1, 'C');

$pdf->Output('I', 'receive_report.pdf');
?>