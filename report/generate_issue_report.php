<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');

define('FPDF_FONTPATH', 'C:/xampp/htdocs/assets/fpdf186/font/');

class PDF extends FPDF
{
    function Header()
    {
        $this->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $this->SetFont('THSarabunNew', '', 15);
        $this->Image('../assets/img/logo.png', 10, 6, 30);
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

$query = "
SELECT DISTINCT p.product_id, p.name_th AS product_name, p.unit
FROM products p
JOIN d_receive dr ON p.product_id = dr.product_id
ORDER BY p.product_id
";

$result = dd_q($query);
$products = $result->fetchAll(PDO::FETCH_ASSOC);

$pdf = new PDF();
$pdf->AliasNbPages();

foreach ($products as $product) {
    $pdf->AddPage();
    
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->SetFont('THSarabunNew', '', 18);
    $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'รายงานการเบิกสินค้าตามการรับ'), 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('THSarabunNew', '', 14);
    $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'รหัสสินค้า:'), 0);
    $pdf->Cell(0, 10, $product['product_id'], 0, 1);
    $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'ชื่อสินค้า:'), 0);
    $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', $product['product_name']), 0, 1);
    $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'หน่วยนับ:'), 0);
    $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', $product['unit']), 0, 1);

    $pdf->Ln(10);
    $pdf->SetFont('THSarabunNew', '', 12);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'เลขที่เอกสารรับ'), 1, 0, 'C', true);
    $pdf->Cell(25, 10, iconv('UTF-8', 'cp874', 'วันที่รับ'), 1, 0, 'C', true);
    $pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'คลังรับ'), 1, 0, 'C', true);
    $pdf->Cell(20, 10, iconv('UTF-8', 'cp874', 'จำนวนรับ'), 1, 0, 'C', true);
    $pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'เลขที่เอกสารเบิก'), 1, 0, 'C', true);
    $pdf->Cell(25, 10, iconv('UTF-8', 'cp874', 'วันที่เบิก'), 1, 0, 'C', true);
    $pdf->Cell(20, 10, iconv('UTF-8', 'cp874', 'จำนวนเบิก'), 1, 1, 'C', true);

    $receiveQuery = "
    SELECT 
        hr.bill_number AS receive_bill,
        hr.received_date,
        l.location AS receive_location,
        dr.quantity AS receive_quantity,
        hi.bill_number AS issue_bill,
        hi.issue_date,
        di.quantity AS issue_quantity
    FROM 
        h_receive hr
    JOIN d_receive dr ON hr.receive_header_id = dr.receive_header_id
    JOIN locations l ON dr.location_id = l.location_id
    LEFT JOIN d_issue di ON dr.product_id = di.product_id AND dr.receive_header_id = (
        SELECT receive_header_id 
        FROM d_receive 
        WHERE product_id = di.product_id AND quantity >= di.quantity
        ORDER BY received_date DESC 
        LIMIT 1
    )
    LEFT JOIN h_issue hi ON di.issue_header_id = hi.issue_header_id
    WHERE dr.product_id = :product_id
    ORDER BY hr.received_date DESC, hi.issue_date DESC
    ";

    $receiveResult = dd_q($receiveQuery, [':product_id' => $product['product_id']]);
    $receiveData = $receiveResult->fetchAll(PDO::FETCH_ASSOC);

    foreach ($receiveData as $row) {
        $pdf->SetFont('THSarabunNew', '', 10);
        $pdf->Cell(30, 10, $row['receive_bill'], 1);
        $pdf->Cell(25, 10, date('d/m/Y', strtotime($row['received_date'])), 1);
        $pdf->Cell(30, 10, iconv('UTF-8', 'cp874', $row['receive_location']), 1);
        $pdf->Cell(20, 10, $row['receive_quantity'], 1, 0, 'R');
        $pdf->Cell(30, 10, $row['issue_bill'] ?? '-', 1);
        $pdf->Cell(25, 10, $row['issue_date'] ? date('d/m/Y', strtotime($row['issue_date'])) : '-', 1);
        $pdf->Cell(20, 10, $row['issue_quantity'] ?? '-', 1, 1, 'R');
    }

    // Calculate and display total
    $totalReceived = array_sum(array_column($receiveData, 'receive_quantity'));
    $totalIssued = array_sum(array_column($receiveData, 'issue_quantity'));
    $currentStock = $totalReceived - $totalIssued;

    $pdf->Ln(10);
    $pdf->SetFont('THSarabunNew', '', 12);
    $pdf->Cell(90, 10, iconv('UTF-8', 'cp874', 'รวมรับทั้งหมด: ' . $totalReceived . ' ' . $product['unit']), 0, 1);
    $pdf->Cell(90, 10, iconv('UTF-8', 'cp874', 'รวมเบิกทั้งหมด: ' . $totalIssued . ' ' . $product['unit']), 0, 1);
    $pdf->Cell(90, 10, iconv('UTF-8', 'cp874', 'คงเหลือ: ' . $currentStock . ' ' . $product['unit']), 0, 1);
}

$pdf->Output('I', 'product_issue_report.pdf');
?>