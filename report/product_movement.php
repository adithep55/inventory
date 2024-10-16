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
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    <style>
        .card {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-generate {
            min-width: 150px;
        }

        @media screen and (max-width: 767px) {

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none;
                text-align: center;
            }

            .dataTables_wrapper .dataTables_paginate {
                margin-top: 0.5em;
            }

            div.dtr-modal {
                font-size: 14px;
            }

            div.dtr-modal-content {
                width: 90%;
            }
        }

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
                        <h3 class="page-title"><i class="fas fa-chart-bar"></i> รายงานการเคลื่อนไหวสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url() ?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">รายงานการเคลื่อนไหวสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">กำหนดเงื่อนไขรายงาน</h4>
                        </div>
                        <div class="card-body">
                            <form id="reportForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="reportType"><i class="fas fa-file-alt"></i>
                                                วิธีการรายงาน</label>
                                            <select id="reportType" class="form-control select2">
                                                <option value="">เลือกวิธีการรายงาน</option>
                                                <option value="product">ตามรหัสสินค้า</option>
                                                <option value="category">ตามหมวดหมู่</option>
                                                <option value="warehouse">ตามคลังสินค้า</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" id="productDiv" style="display:none;">
                                            <label for="productIds"><i class="fas fa-barcode"></i> เลือกสินค้า</label>
                                            <select id="productIds" class="form-control select2" multiple>
                                                <option value="all">เลือกทุกสินค้า</option>
                                            </select>
                                        </div>
                                        <div class="form-group" id="categoryDiv" style="display:none;">
                                            <label for="category_id"><i class="fas fa-tags"></i> หมวดหมู่</label>
                                            <select id="category_id" class="form-control select2">
                                                <option value="">เลือกหมวดหมู่</option>
                                            </select>
                                        </div>
                                        <div class="form-group" id="warehouseDiv" style="display:none;">
                                            <label for="warehouseId"><i class="fas fa-warehouse"></i> คลังสินค้า</label>
                                            <select id="warehouseId" class="form-control select2" multiple>
                                                <option value="all">เลือกทุกคลัง</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" id="typeDiv" style="display:none;">
                                            <label for="type_id"><i class="fas fa-sitemap"></i> ประเภทย่อย</label>
                                            <select id="type_id" class="form-control select2">
                                                <option value="">เลือกประเภทย่อย (ไม่บังคับ)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="endDate"><i class="fas fa-calendar-alt"></i>
                                                วันที่สิ้นสุดรายงาน</label>
                                            <input type="date" id="endDate" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group text-right" style="margin-top: 32px;">
                                            <button type="button" id="generateReport"
                                                class="btn btn-primary btn-generate mr-2">
                                                <i class="fas fa-chart-bar"></i> สร้างรายงาน
                                            </button>
                                            <button type="button" id="generatePdfReport"
                                                class="btn btn-secondary btn-generate">
                                                <i class="fas fa-file-pdf"></i> สร้าง PDF Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">ผลลัพธ์รายงาน</h4>
                        </div>
                        <div class="card-body">
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
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            function formatNumber(number) {
                if (number === null || number === undefined || isNaN(number)) {
                    return '';
                }
                return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            $('#reportType').change(function () {
                var reportType = $(this).val();
                $('#productDiv, #categoryDiv, #typeDiv, #warehouseDiv').hide();
                $('#productIds, #category_id, #type_id, #warehouseId').val('').trigger('change');

                if (reportType === 'product') {
                    $('#productDiv').show();
                    loadProducts();
                } else if (reportType === 'category') {
                    $('#categoryDiv').show();
                    loadTypes();
                } else if (reportType === 'warehouse') {
                    $('#warehouseDiv').show();
                    loadWarehouses();
                }
            });


            function loadWarehouses() {
                $.ajax({
                    url: '../api/get_locations.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var options = '<option value="all">เลือกทุกคลัง</option>';
                        $.each(data.data, function (index, location) {
                            options += '<option value="' + location.location_id + '">' + location.location + '</option>';
                        });
                        $('#warehouseId').html(options).select2({
                            placeholder: "เลือกคลังสินค้า",
                            allowClear: true,
                            multiple: true
                        });

                        $('#warehouseId').on('change', function () {
                            var selectedValues = $(this).val();
                            if (selectedValues && selectedValues.includes('all')) {
                                $(this).val('all').trigger('change.select2');
                            }
                        });
                    }
                });
            }

            function loadTypes() {
                $.ajax({
                    url: '../api/get_types.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var options = '<option value="">เลือกหมวดหมู่หลัก</option>';
                        $.each(data, function (index, type) {
                            options += '<option value="' + type.type_id + '">' + type.name + '</option>';
                        });
                        $('#category_id').html(options);
                    }
                });
            }

            $('#category_id').change(function () {
                var typeId = $(this).val();
                if (typeId) {
                    $('#typeDiv').show();
                    loadCategories(typeId);
                } else {
                    $('#typeDiv').hide();
                    $('#type_id').val('').trigger('change');
                }
            });

            function loadCategories(typeId) {
                $.ajax({
                    url: '../api/get_categories.php',
                    type: 'GET',
                    data: { type_id: typeId },
                    dataType: 'json',
                    success: function (data) {
                        var options = '<option value="">เลือกประเภทย่อย (ไม่บังคับ)</option>';
                        $.each(data, function (index, category) {
                            options += '<option value="' + category.category_id + '">' + category.name + '</option>';
                        });
                        $('#type_id').html(options);
                    }
                });
            }

            function loadProducts() {
                $.ajax({
                    url: '../api/get_products.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.data && Array.isArray(response.data)) {
                            var options = '<option value="all">เลือกทุกสินค้า</option>';
                            response.data.forEach(function (product) {
                                var productId = product.product_id || '';
                                var productName = product.name_th || product.name_en || '';
                                options += '<option value="' + productId + '">' + productId + ' - ' + productName + '</option>';
                            });
                            $('#productIds').html(options).select2({
                                placeholder: "เลือกสินค้า",
                                allowClear: true,
                                multiple: true
                            });

                            $('#productIds').on('change', function () {
                                var selectedValues = $(this).val();
                                if (selectedValues && selectedValues.includes('all')) {
                                    $(this).val('all').trigger('change.select2');
                                }
                            });
                        }
                    }
                });
            }

            $('#generateReport, #generatePdfReport').click(function () {
                var reportType = $('#reportType').val();
                var productIds = $('#productIds').val();
                var categoryId = $('#category_id').val();
                var typeId = $('#type_id').val();
                var warehouseIds = $('#warehouseId').val();
                var endDate = $('#endDate').val();

                if (productIds && productIds.includes('all')) {
                    productIds = 'all';
                } else if (Array.isArray(productIds)) {
                    productIds = productIds.join(',');
                }

                if (warehouseIds && warehouseIds.includes('all')) {
                    warehouseIds = 'all';
                } else if (Array.isArray(warehouseIds)) {
                    warehouseIds = warehouseIds.join(',');
                }

                if (!reportType || !endDate ||
                    (reportType === 'product' && !productIds) ||
                    (reportType === 'category' && !categoryId) ||
                    (reportType === 'warehouse' && !warehouseIds)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                        text: 'กรุณาเลือกวิธีการรายงานและกรอกข้อมูลที่จำเป็น',
                        confirmButtonText: 'ตกลง'
                    });
                    return;
                }

                var requestData = {
                    reportType: reportType,
                    productIds: productIds,
                    categoryId: categoryId,
                    typeId: typeId,
                    warehouseId: warehouseIds,
                    endDate: endDate
                };

                if ($(this).attr('id') === 'generatePdfReport') {
                    var url;
                    if (reportType === 'warehouse') {
                        url = 'generate_warehouse_report.php?' + $.param(requestData);
                    } else {
                        url = 'generate_movement_report.php?' + $.param(requestData);
                    }
                    window.open(url, '_blank');
                } else {
                    var apiUrl;
                    if (reportType === 'warehouse') {
                        apiUrl = '../api/report/get_warehouse_movement.php';
                    } else {
                        apiUrl = '../api/report/get_report_data.php';
                    }

                    $.ajax({
                        url: apiUrl,
                        type: 'GET',
                        data: requestData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.error) {
                                console.error("Error:", response.error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: response.error,
                                    confirmButtonText: 'ตกลง'
                                });
                            } else if (response.data && response.data.length > 0) {
                                if (reportType === 'warehouse') {
                                    displayWarehouseReport(response.data);
                                } else {
                                    var products = response.data;
                                    var promises = [];

                                    products.forEach(function (product) {
                                        promises.push(
                                            $.ajax({
                                                url: '../api/report/get_product_movement.php',
                                                type: 'POST',
                                                data: {
                                                    productId: product.product_id,
                                                    endDate: endDate,
                                                    warehouseId: warehouseIds
                                                },
                                                dataType: 'json'
                                            })
                                        );
                                    });

                                    Promise.all(promises).then(function (results) {
                                        displayReport(results, products);
                                    }).catch(function (error) {
                                        console.error("Error in promises:", error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'เกิดข้อผิดพลาดในการดึงข้อมูล',
                                            text: 'กรุณาลองใหม่อีกครั้ง',
                                            confirmButtonText: 'ตกลง'
                                        });
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'ไม่พบข้อมูล',
                                    text: 'ไม่พบข้อมูลสินค้าตามเงื่อนไขที่ระบุ',
                                    confirmButtonText: 'ตกลง'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Ajax error:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                                text: 'กรุณาลองใหม่อีกครั้ง',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    });
                }
            });

            function displayWarehouseReport(data) {
    $('#productReports').empty();
    data.forEach(function (warehouse) {
        var $warehouseSection = $('<div>').addClass('warehouse-section mb-5');
        $warehouseSection.append($('<h3>').text('คลังสินค้า: ' + warehouse.location));

        warehouse.products.forEach(function (product) {
            var $productSection = $('<div>').addClass('product-section mb-4 text-center');
            $productSection.append($('<h4>').addClass('product-header').text('สินค้า: ' + product.name));

            var $table = $('<table>').addClass('table table-striped table-bordered responsive nowrap').attr('width', '100%');
            var $thead = $('<thead>').appendTo($table);
            var $headerRow = $('<tr>').appendTo($thead);

            $('<th>').text('วันที่').appendTo($headerRow);
            $('<th>').text('รับ').appendTo($headerRow);
            $('<th>').text('เบิก').appendTo($headerRow);
            $('<th>').text('โอนย้าย').appendTo($headerRow);
            $('<th>').text('รายละเอียด').appendTo($headerRow);
            $('<th>').text('คงเหลือ').appendTo($headerRow);

            var $tbody = $('<tbody>').appendTo($table);

            // Add movement rows
            product.movements.forEach(function (movement) {
                // Skip the opening balance row if it's 0.00
                if (movement.details !== 'ยอดยกมา' || parseFloat(product.opening_balance) !== 0) {
                    var $row = $('<tr>');
                    $('<td>').text(movement.date).appendTo($row);
                    $('<td>').text(movement.receive ? formatNumber(movement.receive) : '-').appendTo($row);
                    $('<td>').text(movement.issue ? formatNumber(movement.issue) : '-').appendTo($row);
                    $('<td>').text(movement.transfer ? formatNumber(movement.transfer) : '-').appendTo($row);
                    $('<td>').text(movement.details).appendTo($row);
                    $('<td>').text(formatNumber(movement.balance) + ' ' + product.unit).appendTo($row);
                    $row.appendTo($tbody);
                }
            });

            // Add total row
            var $totalRow = $('<tr>').addClass('total-row');
            $('<td>').text('รวม').appendTo($totalRow);
            $('<td>').text(formatNumber(product.total_receive)).appendTo($totalRow);
            $('<td>').text(formatNumber(product.total_issue)).appendTo($totalRow);
            $('<td>').text(formatNumber(product.total_transfer)).appendTo($totalRow);
            $('<td>').text('-').appendTo($totalRow);
            $('<td>').text(formatNumber(product.closing_balance) + ' ' + product.unit).appendTo($totalRow);
            $totalRow.appendTo($tbody);

            $productSection.append($table);
            $warehouseSection.append($productSection);
        });

        $('#productReports').append($warehouseSection);
    });

    // Initialize DataTables for each table
    $('.warehouse-section table').each(function () {
        $(this).DataTable({
            responsive: true,
            ordering: false,
            language: {
                lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
                zeroRecords: "ไม่พบข้อมูล",
                info: "แสดงหน้าที่ _PAGE_ จาก _PAGES_",
                infoEmpty: "ไม่มีข้อมูล",
                infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
                search: "ค้นหา:",
                paginate: {
                    first: "หน้าแรก",
                    last: "หน้าสุดท้าย",
                    next: "ถัดไป",
                    previous: "ก่อนหน้า"
                }
            }
        });
    });
}
            function displayReport(results, products) {
                $('#productReports').empty();
                results.forEach(function (result, index) {
                    if (result && result.data) {
                        var product = products[index];
                        var $productSection = createProductTable(product.product_id, product.name_th || product.name_en);
                        $('#productReports').append($productSection);
                        initializeDataTable(product.product_id, result.data);
                    } else {
                        console.error("Invalid result data for product at index", index);
                    }
                });
            }

            function createProductTable(productId, productName) {
                var $productSection = $('<div>').addClass('mb-4');
                $productSection.append($('<div>').addClass('product-header').append($('<h4>').text('รายงานสินค้า: ' + productId + ' - ' + productName)));
                $productSection.append($('<table>').addClass('table table-striped table-bordered responsive nowrap')
                    .attr('id', 'movementTable_' + productId)
                    .attr('width', '100%')
                    .append($('<thead>').append($('<tr>')
                        .append($('<th>').text('วันที่'))
                        .append($('<th>').text('ตำแหน่ง'))
                        .append($('<th>').text('รายการ'))
                        .append($('<th>').text('รับ'))
                        .append($('<th>').text('เบิก'))
                        .append($('<th>').text('โอนย้าย'))
                        .append($('<th>').text('คงเหลือ'))
                    )));
                return $productSection;
            }

            function initializeDataTable(productId, data) {
                var openingBalances = data.filter(function (row) {
                    return row.entry_type === 'opening_balance';
                });
                var mainData = data.filter(function (row) {
                    return row.entry_type !== 'opening_balance' && row.entry_type !== 'total';
                });
                var totalRow = data.find(function (row) {
                    return row.entry_type === 'total';
                });

                var tableData = openingBalances.concat(mainData);

                $('#movementTable_' + productId).DataTable({
                    data: tableData,
                    responsive: true,
                    columns: [
                        {
                            data: 'date',
                            responsivePriority: 1,
                            type: 'date'
                        },
                        { data: 'location', responsivePriority: 2 },
                        {
                            data: 'entry_type',
                            render: function (data, type, row) {
                                if (data === 'opening_balance') return 'ยอดยกมา';
                                return 'รายการปกติ';
                            },
                            responsivePriority: 3
                        },
                        {
                            data: 'receive',
                            render: function (data, type, row) {
                                return formatNumber(data);
                            },
                            responsivePriority: 4
                        },
                        {
                            data: 'issue',
                            render: function (data, type, row) {
                                return formatNumber(data);
                            },
                            responsivePriority: 5
                        },
                        { data: 'transfer', responsivePriority: 6 },
                        {
                            data: 'balance',
                            render: function (data, type, row) {
                                return formatNumber(data) + ' ' + row.unit;
                            },
                            responsivePriority: 2
                        }
                    ],
                    order: [[0, 'asc'], [0, 'asc']],
                    language: {
                        lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
                        emptyTable: "ไม่พบข้อมูลสินค้า",
                        info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
                        infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
                        search: "ค้นหา:",
                        zeroRecords: "ไม่พบข้อมูลที่ตรงกัน",
                        paginate: {
                            first: "หน้าแรก",
                            last: "หน้าสุดท้าย",
                            next: "ถัดไป",
                            previous: "ก่อนหน้า"
                        }
                    },
                    drawCallback: function (settings) {
                        var api = this.api();
                        var footer = $(api.table().footer());
                        if (footer.length === 0) {
                            footer = $('<tfoot>').appendTo(api.table().node());
                        }
                        if (totalRow) {
                            footer.html('<tr><td colspan="6" style="text-align: right;">รวมทั้งสิ้น</td><td style="text-align: right;">' + formatNumber(totalRow.balance) + ' ' + totalRow.unit + '</td></tr>');
                        }
                    },
                    createdRow: function (row, data, dataIndex) {
                        if (data.entry_type === 'opening_balance') {
                            $(row).addClass('opening-balance');
                        }
                    }
                });
            }

// ตั้งค่าวันที่เริ่มต้นเป็นวันปัจจุบัน
var today = new Date();
var dd = String(today.getDate()).padStart(2, '0');
var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
var yyyy = today.getFullYear();

today = yyyy + '-' + mm + '-' + dd;
$('#endDate').val(today);
});
    </script>
</body>

</html>