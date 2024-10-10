<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_transfers']);
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Transfer ID is required and must not be empty');
}

$transferId = intval($_GET['id']); 

if ($transferId <= 0) {
    die('Invalid Transfer ID');
}

// Fetch website settings
function getWebsiteSettings($conn) {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM website_settings");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$settings = getWebsiteSettings($conn);

// Fetch transfer data
$query = "
SELECT 
    ht.transfer_header_id,
    ht.bill_number,
    ht.transfer_date,
    u.fname AS transferer_fname,
    u.lname AS transferer_lname,
    l1.location AS from_location,
    l2.location AS to_location,
    dt.product_id,
    p.name_th AS product_name,
    dt.quantity,
    p.unit
FROM 
    h_transfer ht
JOIN d_transfer dt ON ht.transfer_header_id = dt.transfer_header_id
JOIN products p ON dt.product_id = p.product_id
JOIN users u ON ht.user_id = u.UserID
JOIN locations l1 ON ht.from_location_id = l1.location_id
JOIN locations l2 ON ht.to_location_id = l2.location_id
WHERE ht.transfer_header_id = :transfer_id
";

$result = dd_q($query, [':transfer_id' => $transferId]);
$transferData = $result->fetchAll(PDO::FETCH_ASSOC);

if (empty($transferData)) {
    die('Transfer data not found');
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
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'เอกสารการโอนย้ายสินค้า'), 0, 1, 'C');
        $this->SetLineWidth(0.1);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        if ($this->PageNo() > 1) {
            $this->SetFont('THSarabunNew', 'B', 11);
            $this->SetFillColor(240, 240, 240);
            $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
            $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
            $this->Cell(70, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
            $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
            $this->Cell(50, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
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
$pdf->Cell(60, 5, $transferData[0]['bill_number'], 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่โอนย้าย:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y', strtotime($transferData[0]['transfer_date'])), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ผู้โอนย้าย:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $transferData[0]['transferer_fname'] . ' ' . $transferData[0]['transferer_lname']), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'จากคลัง:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $transferData[0]['from_location']), 0);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ไปยังคลัง:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $transferData[0]['to_location']), 0, 1);

$pdf->Ln(5);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายการสินค้าที่โอนย้าย'), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
$pdf->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
$pdf->Cell(70, 5, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
$pdf->Cell(30, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
$pdf->Cell(50, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetFont('THSarabunNew', '', 11);
$total = 0;
$i = 1;
foreach ($transferData as $item) {
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    $pdf->Cell(15, 5, $i, 1, 0, 'C');
    $pdf->Cell(25, 5, $item['product_id'], 1, 0, 'C');
    $pdf->Cell(70, 5, iconv('UTF-8', 'cp874', $item['product_name']), 1);
    $pdf->Cell(30, 5, number_format($item['quantity'], 2), 1, 0, 'R');
    $pdf->Cell(50, 5, iconv('UTF-8', 'cp874', $item['unit']), 1, 0, 'C');
    $pdf->Ln();
    $total += $item['quantity'];
    $i++;
}

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(110, 5, iconv('UTF-8', 'cp874', 'รวมทั้งสิ้น'), 1, 0, 'R', true);
$pdf->Cell(30, 5, number_format($total, 2), 1, 0, 'R', true);
$pdf->Cell(50, 5, '', 1, 0, 'C', true);
$pdf->Ln(20);

// ตรวจสอบว่ามีพื้นที่เพียงพอสำหรับลายเซ็นหรือไม่ ถ้าไม่พอให้ขึ้นหน้าใหม่
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}

// ส่วนลายเซ็น
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(63, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้โอนย้ายสินค้า'), 0, 0, 'C');
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

$pdf->Output('I', 'transfer_report.pdf');
?>