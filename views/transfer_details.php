<?php
require_once '../config/permission.php';
requirePermission(['manage_transfers']);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายละเอียดการโอนย้าย</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">การโอนย้ายสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="transfer_history.php">รายการโอนย้ายสินค้า</a></li>
                            <li class="breadcrumb-item active">รายละเอียดการโอนย้ายสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">
                                    <i class="fas fa-info-circle mr-2"></i> ข้อมูลการโอนย้าย
                                </h4>
                                <div class="wordset">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item">
                                            <a id="pdfButton" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
                                                <img src="../assets/img/icons/pdf.svg" alt="PDF">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-receipt mr-2"></i> เลขบิล</label>
                                        <input type="text" class="form-control" id="billNumber" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt mr-2"></i> วันที่โอนย้าย</label>
                                        <input type="text" class="form-control" id="transferDate" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-user mr-2"></i> ผู้ดำเนินการ</label>
                                        <input type="text" class="form-control" id="username" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-hashtag mr-2"></i> จำนวนรายการ</label>
                                        <input type="text" class="form-control" id="itemCount" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-cubes mr-2"></i> ปริมาณรวม</label>
                                        <input type="text" class="form-control" id="totalQuantity" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-clock mr-2"></i> เวลาอัพเดตล่าสุด</label>
                                        <input type="text" class="form-control" id="updatedAt" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">
                                <i class="fas fa-list mr-2"></i> รายการสินค้าที่โอนย้าย
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0" id="transferItemsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-barcode mr-2"></i> รหัสสินค้า</th>
                                            <th><i class="fas fa-box mr-2"></i> ชื่อสินค้า (ไทย)</th>
                                            <th><i class="fas fa-box mr-2"></i> ชื่อสินค้า (อังกฤษ)</th>
                                            <th><i class="fas fa-hashtag mr-2"></i> จำนวน</th>
                                            <th><i class="fas fa-balance-scale mr-2"></i> หน่วย</th>
                                            <th><i class="fas fa-warehouse mr-2"></i> จากคลัง</th>
                                            <th><i class="fas fa-warehouse mr-2"></i> ไปคลัง</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const transferId = urlParams.get('id');

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // PDF button click event
            $('#pdfButton').on('click', function(e) {
                e.preventDefault();
                if (transferId) {
                    window.open(`../report/generate_transfer_report.php?id=${transferId}`, '_blank');
                } else {
                    alert('ไม่พบรหัสการโอนย้ายสินค้า');
                }
            });

            // Fetch transfer details
            $.ajax({
                url: '../api/get_transfer_details.php',
                type: 'GET',
                data: { id: transferId },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        alert('Error: ' + response.error);
                        return;
                    }

                    // Populate transfer details
                    $('#billNumber').val(response.bill_number);
                    $('#transferDate').val(response.transfer_date);
                    $('#username').val(response.username);
                    $('#itemCount').val(response.item_count);
                    $('#totalQuantity').val(response.total_quantity);
                    $('#updatedAt').val(response.updated_at);

                    // Populate items table
                    let tableBody = $('#transferItemsTable tbody');
                    tableBody.empty();
                    response.items.forEach(function (item) {
                        tableBody.append(`
                            <tr>
                                <td>${item.product_id}</td>
                                <td>${item.product_name_th}</td>
                                <td>${item.product_name_en}</td>
                                <td>${item.quantity}</td>
                                <td>${item.unit}</td>
                                <td>${item.from_location}</td>
                                <td>${item.to_location}</td>
                            </tr>
                        `);
                    });
                },
                error: function (xhr, status, error) {
                    alert('An error occurred while fetching transfer details.');
                    console.error(error);
                }
            });
        });
    </script>
</body>

</html>