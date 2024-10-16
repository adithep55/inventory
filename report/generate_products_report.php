<?php
require_once '../config/connect.php';
require_once('../assets/fpdf186/fpdf.php');
require_once '../config/permission.php';
requirePermission(['manage_products', 'manage_reports']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', '../assets/fpdf186/font/');

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
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายงานสินค้าคงคลัง'), 0, 1, 'C');
        $this->SetLineWidth(0.1);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        $this->SetFont('THSarabunNew', 'B', 11);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
        $this->Cell(25, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
        $this->Cell(60, 5, iconv('UTF-8', 'cp874', 'ชื่อสินค้า'), 1, 0, 'C', true);
        $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'หมวดหมู่'), 1, 0, 'C', true);
        $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
        $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
        $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'จุดสั่งซื้อ'), 1, 0, 'C', true);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 10, iconv('UTF-8', 'cp874', 'หน้า ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF($settings);
$pdf->AliasNbPages();
$pdf->AddPage();

// ส่วนหัวของเอกสาร
$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์รายงาน:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(40, 5, iconv('UTF-8', 'cp874', 'ผู้พิมพ์รายงาน:'), 0);
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(0, 5, iconv('UTF-8', 'cp874', $_SESSION['fname'] . ' ' . $_SESSION['lname'] ?? 'N/A'), 0, 1);

$pdf->Ln(5);

// Query to get all products
$query = "SELECT p.*, pc.name AS category_name, 
          (SELECT SUM(quantity) FROM inventory WHERE product_id = p.product_id) AS total_quantity
          FROM products p
          LEFT JOIN product_cate pc ON p.product_type_id = pc.category_id
          ORDER BY p.product_id";
$result = $conn->query($query);
$products = $result->fetchAll(PDO::FETCH_ASSOC);

$pdf->SetFont('THSarabunNew', '', 11);
$total = 0;
$i = 1;
foreach ($products as $product) {
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    
    $pdf->Cell(15, 5, $i, 1, 0, 'C');
    $pdf->Cell(25, 5, $product['product_id'], 1, 0, 'C');
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $product['name_th']), 1);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', $product['category_name']), 1, 0, 'C');
    $pdf->Cell(20, 5, number_format($product['total_quantity'] ?? 0, 2), 1, 0, 'R');
    $pdf->Cell(20, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 0, 'C');
    $pdf->Cell(20, 5, number_format($product['low_level'], 2), 1, 0, 'R');
    $pdf->Ln();
    $total += $product['total_quantity'] ?? 0;
    $i++;
}

$pdf->SetFont('THSarabunNew', 'B', 11);
$pdf->Cell(130, 5, iconv('UTF-8', 'cp874', 'รวมทั้งสิ้น'), 1, 0, 'R', true);
$pdf->Cell(20, 5, number_format($total, 2), 1, 0, 'R', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', '-'), 1, 0, 'C', true);
$pdf->Cell(20, 5, iconv('UTF-8', 'cp874', '-'), 1, 0, 'C', true);
$pdf->Ln(20);

// ส่วนลายเซ็น
$pdf->SetFont('THSarabunNew', '', 11);
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้จัดทำรายงาน'), 0, 0, 'C');
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้ตรวจสอบ'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 1, 'C');
$pdf->Ln(3);
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 1, 'C');

$pdf->Output('I', 'product_inventory_report.pdf');
?>