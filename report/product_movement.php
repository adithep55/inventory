<?php
require_once '../config/permission.php';
requirePermission(['manage_reports']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายงานการเคลื่อนไหวสินค้า</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    <style>
        .opening-balance {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .product-header {
            background-color: #e9ecef;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        /* เพิ่ม CSS สำหรับปรับแต่งตารางให้ responsive */
        @media screen and (max-width: 767px) {

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none;
                text-align: center;
            }

            .dataTables_wrapper .dataTables_paginate {
                margin-top: 0.5em;
            }

            /* ปรับขนาดฟอนต์สำหรับ modal */
            div.dtr-modal {
                font-size: 14px;
            }

            /* ปรับความกว้างของ modal */
            div.dtr-modal-content {
                width: 90%;
            }
        }

        .error-highlight,
        .error-highlight+.select2-container .select2-selection {
            border: 2px solid red !important;
        }
    </style>
</head>

<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">รายงานการเคลื่อนไหวสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">รายงานการเคลื่อนไหวสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 m-1">
                                    <select id="startProductId" class="form-control select2">
                                        <option value="">เลือกรหัสสินค้าเริ่มต้น</option>
                                    </select>
                                </div>
                                <div class="col-md-3 m-1">
                                    <select id="endProductId" class="form-control select2">
                                        <option value="">เลือกรหัสสินค้าสิ้นสุด</option>
                                    </select>
                                </div>
                                <div class="col-md-3 m-1">
                                    <input type="date" id="endDate" class="form-control"
                                        placeholder="วันที่สิ้นสุดรายงาน">
                                </div>
                                <div class="col-md-3 m-1">
    <button id="generateReport" class="btn btn-primary">สร้างรายงาน</button>
</div>
<div class="col-md-3 m-1">
    <button id="generatePdfReport" class="btn btn-secondary">สร้าง PDF Report</button>
</div>
                            </div>
                            <div id="productReports"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            function createProductTable(productId, productName) {
                var $productSection = $('<div>').addClass('mb-4');
                $productSection.append($('<div>').addClass('product-header').append($('<h4>').text('รายงานสินค้า: ' + productId + ' - ' + productName)));
                $productSection.append($('<table>').addClass('table table-striped table-bordered responsive nowrap') // เพิ่มคลาส 'responsive' และ 'nowrap'
                    .attr('id', 'movementTable_' + productId)
                    .attr('width', '100%') // เพิ่มความกว้าง 100%
                    .append($('<thead>').append($('<tr>')
                        .append($('<th>').text('วันที่'))
                        .append($('<th>').text('รายการ'))
                        .append($('<th>').text('รับ'))
                        .append($('<th>').text('เบิก'))
                        .append($('<th>').text('โอนย้าย'))
                        .append($('<th>').text('คงเหลือ'))
                    )));
                return $productSection;
            }

            function formatNumber(number) {
                if (number === null || number === undefined || isNaN(number)) {
                    return '';
                }
                return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            function initializeDataTable(productId) {
                $('#movementTable_' + productId).DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '../api/report/get_product_movement.php',
                        type: 'POST',
                        data: function (d) {
                            return {
                                productId: productId,
                                endDate: $('#endDate').val(),
                                draw: d.draw,
                                start: d.start,
                                length: d.length,
                                search: d.search ? d.search.value : null,
                                order: d.order ? d.order[0] : null
                            };
                        },
                        dataSrc: function (json) {
                            console.log("Raw API response:", json);
                            if (json.error) {
                                console.error("API Error:", json.error);
                                return [];
                            }
                            if (!json.data) {
                                console.error("Invalid data structure received from API");
                                return [];
                            }
                            return json.data;
                        }
                    },
                    columns: [
                        { data: 'date', responsivePriority: 1 },
                        {
                            data: 'entry_type',
                            render: function (data, type, row) {
                                return data === 'opening_balance' ? 'ยอดยกมา' : 'รายการปกติ';
                            },
                            responsivePriority: 2
                        },
                        {
                            data: 'receive',
                            render: function (data, type, row) {
                                return row.entry_type === 'opening_balance' ? '' : formatNumber(data);
                            },
                            responsivePriority: 3
                        },
                        {
                            data: 'issue',
                            render: function (data, type, row) {
                                return row.entry_type === 'opening_balance' ? '' : formatNumber(data);
                            },
                            responsivePriority: 4
                        },
                        {
                            data: 'transfer',
                            render: function (data, type, row) {
                                return row.entry_type === 'opening_balance' ? '' : data;
                            },
                            responsivePriority: 5
                        },
                        {
                            data: 'balance',
                            render: function (data, type, row) {
                                return formatNumber(data) + ' ' + row.unit;
                            },
                            responsivePriority: 2
                        }
                    ],
                    order: [[0, 'asc']],
                    createdRow: function (row, data, dataIndex) {
                        if (data.entry_type === 'opening_balance') {
                            $(row).addClass('opening-balance');
                        }
                    },
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json'
                    },
                    error: function (xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                    }
                });
            }
            $('#generatePdfReport').click(function() {
    var startId = $('#startProductId').val();
    var endId = $('#endProductId').val();
    var endDate = $('#endDate').val();

    if (startId && endId && endDate) {
        window.open('generate_movement_report.php?startProductId=' + startId + '&endProductId=' + endId + '&endDate=' + endDate, '_blank');
    } else {
        Swal.fire({
            icon: 'error',
            title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
            text: 'กรุณาเลือกรหัสสินค้าเริ่มต้น, รหัสสินค้าสิ้นสุด และวันที่สิ้นสุดรายงาน',
            confirmButtonText: 'ตกลง'
        });
    }
});

            $('#generateReport').click(function () {
                var startId = $('#startProductId').val();
                var endId = $('#endProductId').val();
                var endDate = $('#endDate').val();

                // Reset error highlighting
                $('.form-control, .select2-container').removeClass('error-highlight');

                var isValid = true;
                var errorMessage = '';

                if (!startId) {
                    $('#startProductId').next('.select2-container').addClass('error-highlight');
                    errorMessage += 'กรุณาเลือกรหัสสินค้าเริ่มต้น<br>';
                    isValid = false;
                }

                if (!endId) {
                    $('#endProductId').next('.select2-container').addClass('error-highlight');
                    errorMessage += 'กรุณาเลือกรหัสสินค้าสิ้นสุด<br>';
                    isValid = false;
                }

                if (!endDate) {
                    $('#endDate').addClass('error-highlight');
                    errorMessage += 'กรุณาเลือกวันที่สิ้นสุดรายงาน<br>';
                    isValid = false;
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                        html: errorMessage,
                        confirmButtonText: 'ตกลง'
                    });
                    return;
                }
                if (startId && endId && endDate) {
                    $.ajax({
                        url: '../api/get_products.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            console.log("Generate report response:", response);
                            if (response.data && Array.isArray(response.data)) {
                                $('#productReports').empty();
                                var filteredProducts = response.data.filter(function (product) {
                                    return product.product_id >= startId && product.product_id <= endId;
                                });
                                filteredProducts.forEach(function (product) {
                                    var $productSection = createProductTable(product.product_id, product.name_th || product.name_en);
                                    $('#productReports').append($productSection);
                                    initializeDataTable(product.product_id);
                                });
                            } else {
                                console.error("Invalid product data structure:", response);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Ajax error loading products:", error);
                        }
                    });
                }
            });

            // โหลดตัวเลือกสินค้า
            $.ajax({
                url: '../api/get_products.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log("Product API response:", response);
                    if (response.data && Array.isArray(response.data)) {
                        var options = '<option value="">เลือกรหัสสินค้า</option>';
                        response.data.forEach(function (product) {
                            var productId = product.product_id || '';
                            var productName = product.name_th || product.name_en || '';
                            options += '<option value="' + productId + '">' + productId + ' - ' + productName + '</option>';
                        });
                        $('#startProductId, #endProductId').html(options);
                    } else {
                        console.error("Invalid product data structure:", response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Ajax error loading products:", error);
                }
            });

            // ตั้งค่าวันที่เริ่มต้นเป็นวันสุดท้ายของเดือนปัจจุบัน
            var today = new Date();
            var lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            $('#endDate').val(lastDayOfMonth.toISOString().split('T')[0]);
            // รีเซ็ต highlight สำหรับ input ปกติและ Select2
            $(document).on('change', '.form-control', function () {
                $(this).removeClass('error-highlight');
            });

            $(document).on('select2:select', '.select2', function (e) {
                $(this).next('.select2-container').removeClass('error-highlight');
            });
        });
    </script>
</body>
</html>