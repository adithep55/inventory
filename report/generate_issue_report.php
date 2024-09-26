<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');

define('FPDF_FONTPATH', 'C:/xampp/htdocs/assets/fpdf186/font/');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Issue ID is required and must not be empty');
}

$issueId = intval($_GET['id']); 

if ($issueId <= 0) {
    die('Invalid Issue ID');
}

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
    di.product_id,
    pr.name_th AS product_name,
    di.quantity,
    pr.unit,
    l.location AS location_name,
    i.quantity AS current_quantity,
    (
        SELECT hr.bill_number
        FROM h_receive hr
        JOIN d_receive dr ON hr.receive_header_id = dr.receive_header_id
        WHERE dr.product_id = di.product_id
        ORDER BY hr.received_date DESC
        LIMIT 1
    ) AS last_receive_number
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

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
$pdf->SetFont('THSarabunNew', '', 18);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'รายการเบิกสินค้า'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('THSarabunNew', '', 14);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'เลขที่เอกสาร:'), 0);
$pdf->Cell(0, 10, $issueData[0]['bill_number'], 0, 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'วันที่:'), 0);
$pdf->Cell(0, 10, date('d/m/Y', strtotime($issueData[0]['issue_date'])), 0, 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'ผู้เบิกสินค้า:'), 0);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', $issueData[0]['issuer_fname'] . ' ' . $issueData[0]['issuer_lname']), 0, 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'ประเภทการเบิก:'), 0);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', ($issueData[0]['issue_type'] == 'sale' ? 'เบิกเพื่อขาย' : 'เบิกเพื่อโครงการ')), 0, 1);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', ($issueData[0]['issue_type'] == 'sale' ? 'ลูกค้า:' : 'โครงการ:')), 0);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', $issueData[0]['customer_project_name']), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(15, 10, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
$pdf->Cell(25, 10, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
$pdf->Cell(70, 10, iconv('UTF-8', 'cp874', 'รายละเอียด'), 1, 0, 'C', true);
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'คลัง'), 1, 0, 'C', true);
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);

$pdf->Ln();

$total = 0;
$i = 1;
foreach ($issueData as $item) {
    $pdf->Cell(15, 10, $i, 1, 0, 'C');
    $pdf->Cell(25, 10, $item['product_id'], 1);
    $pdf->Cell(70, 10, iconv('UTF-8', 'cp874', $item['product_name']), 1);
    $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', $item['location_name']), 1);
    $pdf->Cell(30, 10, $item['quantity'] . ' ' . iconv('UTF-8', 'cp874', $item['unit']), 1, 0, 'R');
    $pdf->Ln();
    $total += $item['quantity'];
    $i++;
}

$pdf->SetFont('THSarabunNew', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(150, 10, iconv('UTF-8', 'cp874', 'รวม'), 1, 0, 'R');
$pdf->Cell(30, 10, $total, 1, 0, 'R');
$pdf->Ln(20);

$pdf->SetFont('THSarabunNew', '', 12);
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้เบิกสินค้า'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้จ่ายสินค้า'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้ตรวจสอบ'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'ผู้อนุมัติ'), 0, 1, 'C');
$pdf->Ln(15);
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 0, 'C');
$pdf->Cell(47, 10, iconv('UTF-8', 'cp874', 'วันที่ ___/___/___'), 0, 1, 'C');

$pdf->Output('I', 'issue_report.pdf');
?>