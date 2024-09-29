
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายละเอียดสินค้า</title>

    <base href="/">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 767px) {
            .product-image {
                max-width: 100% !important;
                margin-bottom: 20px;
            }
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
                            <li class="breadcrumb-item"><a href="inventory">คลังสินค้า</a></li>
                            <li class="breadcrumb-item active">รายละเอียดสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body" id="productDetails">
                            <!-- รายละเอียดสินค้าจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/feather.min.js"></script>
    <script src="/assets/js/jquery.slimscroll.min.js"></script>
    <script src="/assets/js/jquery.dataTables.min.js"></script>
    <script src="/assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/plugins/select2/js/select2.min.js"></script>
    <script src="/assets/js/script.js"></script>

    <script>
    $(document).ready(function() {
        var pathArray = window.location.pathname.split('/');
        var productId = pathArray[pathArray.length - 1];

        $.ajax({
            url: '/api/get_info_inventory.php',
            type: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    displayProductDetails(response.data);
                } else {
                    $('#productDetails').html('<p class="text-danger">ไม่พบข้อมูลสินค้า: ' + (response.message || 'ไม่ทราบสาเหตุ') + '</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                $('#productDetails').html('<p class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + textStatus + '</p>');
            }
        });
    });

    function displayProductDetails(data) {
        let inventoryHtml = '';
        if (Array.isArray(data.inventory) && data.inventory.length > 0) {
            inventoryHtml = `
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">ข้อมูลคงเหลือในคลัง</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>สถานที่จัดเก็บ</th>
                                        <th>จำนวน</th>
                                         <th>วันที่เก็บ</th>
                                        <th>อัปเดตล่าสุด</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            data.inventory.forEach(function(item) {
                inventoryHtml += `
                    <tr>
                        <td>${item.location}</td>
                        <td>${item.quantity} ${data.unit}</td>
                        <td>${item.last_received_date}</td>
                        <td>${item.updated_at}</td>
                    </tr>
                `;
            });
            inventoryHtml += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } else {
            inventoryHtml = '<p>ไม่พบข้อมูลคงเหลือในคลัง</p>';
        }

        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h4>${data.name_th} <small>(${data.name_en})</small></h4>
                    <p><strong>รหัสสินค้า:</strong> ${data.product_id}</p>
                    <p><strong>ประเภทสินค้า:</strong> ${data.product_type_name}</p>
                    <p><strong>หมวดหมู่สินค้า:</strong> ${data.product_category_name}</p>
                    <p><strong>ขนาด:</strong> ${data.size}</p>
                    <p><strong>หน่วย:</strong> ${data.unit}</p>
                    <p><strong>ระดับต่ำสุด:</strong> ${data.low_level} ${data.unit}</p>
                </div>
                <div class="col-md-6 text-center">
                    <img src="${data.image_url}" alt="รูปสินค้า" class="product-image" style="max-width: 40%; height: auto;">
                </div>
            </div>
            ${inventoryHtml}
        `;

        $('#productDetails').html(detailsHtml);
    }
    </script>
</body>
</html>