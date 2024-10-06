<?php
require_once '../config/permission.php';
requirePermission(['manage_products']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายละเอียดสินค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/owlcarousel/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        .barcode-container {
            text-align: center;
            margin-bottom: 20px;
        }
        #barcode {
        width: 300px; 
        height: 100px; 
    }
        .product-image {
            max-width: 80%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        } 
    </style>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">รายละเอียดสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">แดชบอร์ด</a></li>
                            <li class="breadcrumb-item"><a href="productlist.html">รายการสินค้า</a></li>
                            <li class="breadcrumb-item active">รายละเอียดสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                        <div class="barcode-container">
    <svg id="barcode"></svg>
</div>
                            <div class="productdetails">
                                <ul class="product-bar">
                                    <!-- Product details will be populated here -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="slider-product-details">
                                <div class="owl-carousel owl-theme product-slide">
                                    <!-- Product image will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/plugins/owlcarousel/owl.carousel.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
     $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');

        if (productId) {
            $.ajax({
                url: '../api/get_product_details.php',
                type: 'GET',
                data: { id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const product = response.data;
                        updateProductDetails(product);
                        generateBarcode(product.product_id);
                    } else {
                        console.error('Error fetching product details:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        } else {
            console.error('Product ID not provided in URL');
        }
    });

    function updateProductDetails(product) {
        const detailsList = [
            { label: '  รหัสสินค้า', value: product.product_id, icon: 'fas fa-barcode' },
            { label: '  ชื่อสินค้า (ไทย)', value: product.name_th, icon: 'fas fa-box' },
            { label: '  ชื่อสินค้า (อังกฤษ)', value: product.name_en, icon: 'fas fa-language' },
            { label: '  ประเภทสินค้า', value: product.product_type_name, icon: 'fas fa-tags' },
            { label: '  หมวดหมู่', value: product.product_category_name, icon: 'fas fa-folder' },
            { label: '  ขนาด', value: product.size, icon: 'fas fa-ruler-combined' },
            { label: '  หน่วย', value: product.unit, icon: 'fas fa-balance-scale' },
            { label: '  ระดับการแจ้งเตือนสินค้าใกล้หมด', value: product.low_level, icon: 'fas fa-bell' },
            { label: '  สร้างโดย', value: product.created_by, icon: 'fas fa-user' },
            { label: '  วันที่อัปเดต', value: product.updated_at, icon: 'fas fa-calendar-alt' }
        ];

        let detailsHtml = '';
        detailsList.forEach(item => {
            detailsHtml += `
                <li>
                    <h4><i class="${item.icon}"></i>${item.label}</h4>
                    <h6>${item.value || 'N/A'}</h6>
                </li>
            `;
        });

        $('.productdetails ul').html(detailsHtml);

        // Update product image
        const imageUrl = product.img ? `../img/product/${product.img}` : '../img/product/default-image.jpg';
        $('.product-slide').html(`
            <div class="slider-product">
                <img src="${imageUrl}" alt="${product.name_th}" class="product-image">
                <h4>${product.name_th}</h4>
            </div>
        `);

        // Update barcode
        $('#barcodeNumber').text(product.product_id);

        // Initialize owl carousel
        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                }
            }
        });
    }
    function generateBarcode(productId) {
        JsBarcode("#barcode", productId, {
            format: "CODE128",
            width: 2,
            height: 100,
            displayValue: true
        });
    }
    </script>
</body>
</html>