<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');


define('FPDF_FONTPATH', '../assets/fpdf186/font/');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Transfer ID is required and must not be empty');
}

$transferId = intval($_GET['id']); 

if ($transferId <= 0) {
    die('Invalid Transfer ID');
}

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
    function Header()
    {
        $this->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $this->AddFont('THSarabunNew', 'B', 'THSarabunNew_b.php');
        $this->SetFont('THSarabunNew', 'B', 18);
        $this->Image('../assets/img/logo.png', 10, 6, 30);
        $this->Cell(0, 10, iconv('UTF-8', 'cp874', 'บริษัท ตัวอย่าง จำกัด'), 0, 1, 'C');
        $this->SetFont('THSarabunNew', '', 14);
        $this->Cell(0, 7, iconv('UTF-8', 'cp874', '257/1 ถ.รามคำแหง แขวงรามคำแหง เขตบางกะปิ กรุงเทพฯ 10240'), 0, 1, 'C');
        $this->Cell(0, 7, iconv('UTF-8', 'cp874', 'โทร. 0-2739-5900 โทรสาร 0-2739-5910 เลขประจำตัวผู้เสียภาษี 3125523223'), 0, 1, 'C');
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'cp874', 'หน้า ').$this->PageNo().'/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('THSarabunNew', 'B', 20);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'เอกสารการโอนย้ายสินค้า'), 0, 1, 'C');
$pdf->SetLineWidth(0.1);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'เลขที่เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(60, 10, $transferData[0]['bill_number'], 0);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'วันที่โอนย้าย:'), 0);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(0, 10, date('d/m/Y', strtotime($transferData[0]['transfer_date'])), 0, 1);
$currentDate = date('d/m/Y H:i:s');

$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'ผู้โอนย้าย:'), 0);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(60, 10, iconv('UTF-8', 'cp874', $transferData[0]['transferer_fname'] . ' ' . $transferData[0]['transferer_lname']), 0);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'วันที่พิมพ์เอกสาร:'), 0);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(0, 10, $currentDate, 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'จากคลัง:'), 0);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(60, 10, iconv('UTF-8', 'cp874', $transferData[0]['from_location']), 0);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'ไปยังคลัง:'), 0);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', $transferData[0]['to_location']), 0, 1);

$pdf->Ln(5);
$pdf->SetFont('THSarabunNew', 'B', 16);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'รายการสินค้าที่โอนย้าย'), 0, 1, 'C');
$pdf->SetLineWidth(0.1);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(2);

$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(15, 10, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
$pdf->Cell(75, 10, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetFont('THSarabunNew', '', 14);
$total = 0;
$i = 1;
foreach ($transferData as $item) {
    $pdf->Cell(15, 10, $i, 1, 0, 'C');
    $pdf->Cell(30, 10, $item['product_id'], 1, 0, 'C');
    $pdf->Cell(75, 10, iconv('UTF-8', 'cp874', $item['product_name']), 1);
    $pdf->Cell(30, 10, number_format($item['quantity'], 2), 1, 0, 'R');
    $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', $item['unit']), 1, 0, 'C');
    $pdf->Ln();
    $total += $item['quantity'];
    $i++;
}

$pdf->SetFont('THSarabunNew', 'B', 14);
$pdf->Cell(120, 10, iconv('UTF-8', 'cp874', 'รวมทั้งสิ้น'), 1, 0, 'R', true);
$pdf->Cell(30, 10, number_format($total, 2), 1, 0, 'R', true);
$pdf->Cell(40, 10, '', 1, 0, 'C', true);
$pdf->Ln(20);

$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้โอนย้ายสินค้า'), 0, 0, 'C');
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้รับสินค้า'), 0, 0, 'C');
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้ตรวจสอบ'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', '(................................................)'), 0, 1, 'C');
$pdf->Ln(3);
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(63, 10, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 1, 'C');

$pdf->Output('I', 'transfer_report.pdf');
?>