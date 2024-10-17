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
    try {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM website_settings");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        return [];
    }
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
        
        $this->SetFont('THSarabunNew', 'B', 18);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_name'] ?? ''), 0, 1, 'C');
        
        // Company Address and Contact
        $this->SetFont('THSarabunNew', '', 12);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_address'] ?? ''), 0, 1, 'C');
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $this->settings['company_contact'] ?? ''), 0, 1, 'C');
        
        // Report Title
        $this->Ln(5);
        $this->SetFont('THSarabunNew', 'B', 16);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'รายงานรายการสินค้า'), 0, 1, 'C');
        $this->Line(5, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        // Report generation info
        $this->SetFont('THSarabunNew', '', 12);
        $this->Cell(40, 5, iconv('UTF-8', 'cp874', 'วันที่พิมพ์รายงาน:'), 0);
        $this->Cell(0, 5, date('d/m/Y H:i:s'), 0, 1);
        $this->Cell(40, 5, iconv('UTF-8', 'cp874', 'ผู้พิมพ์รายงาน:'), 0);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', $_SESSION['fname'] . ' ' . $_SESSION['lname'] ?? 'N/A'), 0, 1);
        $this->Ln(5);

        // Table Header
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(10, 5, iconv('UTF-8', 'cp874', 'ลำดับ'), 1, 0, 'C', true);
        $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'รหัสสินค้า'), 1, 0, 'C', true);
        $this->Cell(60, 5, iconv('UTF-8', 'cp874', 'ชื่อสินค้า'), 1, 0, 'C', true);
        $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'หมวดหมู่'), 1, 0, 'C', true);
        $this->Cell(30, 5, iconv('UTF-8', 'cp874', 'ประเภทย่อย'), 1, 0, 'C', true);
        // $this->Cell(20, 5, iconv('UTF-8', 'cp874', 'จำนวน'), 1, 0, 'C', true);
        $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'หน่วย'), 1, 0, 'C', true);
        $this->Cell(15, 5, iconv('UTF-8', 'cp874', 'จุดสั่งซื้อ'), 1, 0, 'C', true);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 5, iconv('UTF-8', 'cp874', 'หน้า ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF($settings);
$pdf->AliasNbPages();
$pdf->AddPage();

// Query to get all products
$query = "SELECT p.*, 
       pt.name AS product_type_name, 
       pc.name AS category_name,
       (SELECT SUM(quantity) FROM inventory WHERE product_id = p.product_id) AS total_quantity 
FROM products p 
LEFT JOIN product_types pt ON pt.type_id = (
    SELECT product_category_id 
    FROM product_cate 
    WHERE category_id = p.product_type_id
)
LEFT JOIN product_cate pc ON pc.category_id = p.product_type_id
ORDER BY p.product_id;";

$result = $conn->query($query);
$products = $result->fetchAll(PDO::FETCH_ASSOC);

$pdf->SetFont('THSarabunNew', '', 12);
$total = 0;
$i = 1;
foreach ($products as $product) {
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    
    $pdf->Cell(10, 5, $i, 1, 0, 'C');
    $pdf->Cell(20, 5, $product['product_id'], 1, 0, 'C');
    $pdf->Cell(60, 5, iconv('UTF-8', 'cp874', $product['name_th']), 1);
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', $product['product_type_name']), 1, 0, 'C'); 
    $pdf->Cell(30, 5, iconv('UTF-8', 'cp874', $product['category_name']), 1, 0, 'C');
    // $pdf->Cell(20, 5, number_format($product['total_quantity'] ?? 0, 2), 1, 0, 'R');
    $pdf->Cell(15, 5, iconv('UTF-8', 'cp874', $product['unit']), 1, 0, 'C');
    $pdf->Cell(15, 5, number_format($product['low_level'], 2), 1, 0, 'C');
    $pdf->Ln();
    $i++;
}

// Signature section
$pdf->Ln(20);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้จัดทำรายงาน'), 0, 0, 'C');
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'ลงชื่อ..................................ผู้ตรวจสอบ'), 0, 1, 'C');
$pdf->Ln(10);
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 0, 'C');
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', '(................................................)'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 0, 'C');
$pdf->Cell(95, 5, iconv('UTF-8', 'cp874', 'วันที่ ........../........../..........'), 0, 1, 'C');

$pdf->Output('I', 'product_inventory_report.pdf');
?>