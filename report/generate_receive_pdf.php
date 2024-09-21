<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');

// กำหนดเส้นทางของฟอนต์
define('FPDF_FONTPATH', 'C:/xampp/htdocs/assets/fpdf186/font/');

if (!isset($_GET['receive_id'])) {
    die('Receive ID is required');
}

$receiveId = $_GET['receive_id'];

// Query to fetch receive details
$query = "
SELECT 
    hr.bill_number,
    hr.received_date,
    u.fname AS receiver_fname,
    u.lname AS receiver_lname,
    d.product_id,
    p.name_th AS product_name,
    d.quantity,
    p.unit,
    l.location AS location_name
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
    function Header()
    {
        $this->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $this->SetFont('THSarabunNew', '', 15);
        $this->Image('../assets/img/logo.png', 10, 6, 30); // Adjust path to your logo
        $this->Cell(0, 10, iconv('UTF-8', 'cp874', 'บริษัท ตัวอย่าง จำกัด'), 0, 1, 'C');
        $this->SetFont('THSarabunNew', '', 12);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', '257/1 ถ.รามคำแหง แขวงรามคำแหง เขตบางกะปิ กรุงเทพฯ 10240'), 0, 1, 'C');
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'โทร. 0-2739-5900 โทรสาร 0-2739-5910 เลขประจำตัวผู้เสียภาษี 3125523223'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 10, iconv('UTF-8', 'cp874', 'หน้า ').$this->PageNo().'/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'รายการรับสินค้า'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'เลขที่เอกสาร:'), 0);
$pdf->Cell(0, 10, $receiveData[0]['bill_number'], 0, 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'วันที่:'), 0);
$pdf->Cell(0, 10, $receiveData[0]['received_date'], 0, 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'ผู้รับสินค้า:'), 0);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', $receiveData[0]['receiver_fname'] . ' ' . $receiveData[0]['receiver_lname']), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1);
$pdf->Cell(60, 10, iconv('UTF-8', 'cp874', 'รายการสินค้า'), 1);
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'จำนวน'), 1);
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'หน่วยนับ'), 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'สถานที่จัดเก็บ'), 1);
$pdf->Ln();

$total = 0;
foreach ($receiveData as $item) {
    $pdf->Cell(30, 10, $item['product_id'], 1);
    $pdf->Cell(60, 10, iconv('UTF-8', 'cp874', $item['product_name']), 1);
    $pdf->Cell(30, 10, $item['quantity'], 1);
    $pdf->Cell(30, 10, iconv('UTF-8', 'cp874', $item['unit']), 1);
    $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', $item['location_name']), 1);
    $pdf->Ln();
    $total += $item['quantity'];
}

$pdf->Cell(90, 10, iconv('UTF-8', 'cp874', 'รวม'), 1, 0, 'R');
$pdf->Cell(30, 10, $total, 1);
$pdf->Cell(70, 10, '', 1);
$pdf->Ln(20);

$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้จัดทำเอกสาร'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้รับของ'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้ตรวจสอบ'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้อนุมัติ'), 0, 1, 'C');
$pdf->Ln(15);
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 1, 'C');

$pdf->Output('I', 'internal_memo.pdf');
?>