<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>โอนย้ายสินค้า</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
</head>

<body>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>
    
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">โอนย้ายสินค้า</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">โอนย้ายสินค้า</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form id="transferForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>วันที่โอนย้าย</label>
                                        <input type="date" class="form-control" id="transferDate" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4>รายการสินค้าที่จะโอนย้าย</h4>
                                    <table class="table table-bordered" id="transferTable">
                                        <thead>
                                            <tr>
                                                <th>สินค้า</th>
                                                <th>จากคลังสินค้า</th>
                                                <th>ไปยังคลังสินค้า</th>
                                                <th>จำนวน</th>
                                                <th>หน่วย</th>
                                                <th>การดำเนินการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Transfer items will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="productTable">
                                            <thead>
                                                <tr>
                                                    <th>เลือก</th>
                                                    <th>รูปภาพ</th>
                                                    <th>ชื่อสินค้า (ไทย)</th>
                                                    <th>ชื่อสินค้า (อังกฤษ)</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ประเภทสินค้า</th>
                                                    <th>หมวดหมู่สินค้า</th>
                                                    <th>หน่วย</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">บันทึกการโอนย้าย</button>
                                </div>
                            </div>
                        </form>
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
<script src="../assets/js/script.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    function setMaxDate() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const maxDate = tomorrow.toISOString().split('T')[0];
        $('#transferDate').attr('max', maxDate);
    }

    setMaxDate();

    $('#transferDate').on('change', function() {
        const selectedDate = new Date($(this).val());
        const currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0);
        const maxDate = new Date(currentDate);
        maxDate.setDate(maxDate.getDate() + 1);

        if (selectedDate > maxDate) {
            $(this).val(maxDate.toISOString().split('T')[0]);
            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเลือกวันที่ในอนาคตได้', 'error');
        }
    });
    
    function preventNegativeInput(input) {
        let value = parseInt(input.val());
        if (isNaN(value) || value < 1) {
            input.val(1);
        } else {
            input.val(Math.floor(value));
        }
    }
    $(document).on('input', '.quantity', function() {
        preventNegativeInput($(this));
    });
    var productTable = $('#productTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "../api/get_inventory_TF.php",
            "type": "POST"
        },
        "columns": [
            { 
                "data": null,
                "render": function (data, type, row) {
                    return '<input type="checkbox" class="product-select" value="' + row.product_id + '">';
                }
            },
            { 
                "data": "image_url",
                "render": function(data, type, row) {
                    return '<img src="' + data + '" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">';
                }
            },
            { "data": "name_th" },
            { "data": "name_en" },
            { "data": "product_id" },
            { "data": "product_type_name" },
            { "data": "product_category_name" },
            { "data": "unit" }
        ]
    });

    function addProductToTransfer(productId, productName, unit) {
        $.ajax({
            url: '../api/get_product_locations.php',
            type: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function(response) {
                let fromLocationOptions = response.locations.map(loc => 
                    `<option value="${loc.location_id}" data-quantity="${loc.quantity}">${loc.location} (${loc.quantity} ${unit})</option>`
                ).join('');

                let toLocationOptions = response.all_locations.map(loc => 
                    `<option value="${loc.location_id}">${loc.location}</option>`
                ).join('');

                let newRow = `
            <tr>
                <td>${productName}</td>
                <td>
                    <select class="form-control from-location" required>
                        <option value="">เลือกคลังสินค้าต้นทาง</option>
                        ${fromLocationOptions}
                    </select>
                </td>
                <td>
                    <select class="form-control to-location" required>
                        <option value="">เลือกคลังสินค้าปลายทาง</option>
                        ${toLocationOptions}
                    </select>
                </td>
                <td><input type="number" class="form-control quantity" min="1" step="1" required></td>
                <td>${unit}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                <input type="hidden" name="product_ids[]" value="${productId}">
            </tr>
        `;
        $('#transferTable tbody').append(newRow);
    },
            error: function() {
                alert('Error fetching product locations');
            }
        });
    }

    $('#productTable').on('change', '.product-select', function() {
        var row = $(this).closest('tr');
        var data = productTable.row(row).data();
        if (this.checked) {
            addProductToTransfer(data.product_id, data.name_th, data.unit);
        } else {
            $('#transferTable tbody').find(`input[value="${data.product_id}"]`).closest('tr').remove();
        }
    });

    $('#transferTable').on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    $('#transferTable').on('change', '.from-location', function() {
        let row = $(this).closest('tr');
        let quantityInput = row.find('.quantity');
        let maxQuantity = $(this).find('option:selected').data('quantity');
        quantityInput.attr('max', maxQuantity);
    });

    $('#transferTable').on('change', '.from-location, .to-location, .quantity', function() {
        let row = $(this).closest('tr');
        let fromLocation = row.find('.from-location');
        let toLocation = row.find('.to-location');
        let quantityInput = row.find('.quantity');
        let quantity = parseInt(quantityInput.val());
        let maxQuantity = parseInt(fromLocation.find('option:selected').data('quantity'));

        if (fromLocation.val() === toLocation.val()) {
            Swal.fire({
                icon: 'error',
                title: 'คลังสินค้าไม่ถูกต้อง',
                text: 'คลังสินค้าต้นทางและปลายทางต้องไม่เป็นคลังเดียวกัน',
                confirmButtonText: 'ตกลง'
            });
            toLocation.val('');
        }
        if ($('#transferTable tbody tr').length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'ไม่มีรายการสินค้า',
            text: 'กรุณาเลือกสินค้าอย่างน้อย 1 รายการก่อนบันทึกการโอนย้าย',
            confirmButtonText: 'ตกลง'
        });
        return;
    }
        preventNegativeInput(quantityInput);

        if (quantity > maxQuantity) {
            Swal.fire({
                icon: 'error',
                title: 'จำนวนไม่ถูกต้อง',
                text: `จำนวนที่โอนย้ายต้องไม่เกิน ${maxQuantity}`,
                confirmButtonText: 'ตกลง'
            });
            quantityInput.val('1');
        }
    });
    function loadServerDate() {
        $.ajax({
            url: '../api/get_server_date.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var serverDate = new Date(response.date);
                    serverDate.setDate(serverDate.getDate() + 1);
                    var formattedDate = serverDate.toISOString().split('T')[0];
                    $('#transferDate').attr('max', formattedDate);
                    
                    $('#transferDate').on('change', function() {
                        validateTransferDate(serverDate);
                    });
                }
            },
            error: function() {
                console.error('Failed to get server date');
            }
        });
    }

    function validateTransferDate(serverDate) {
        var selectedDate = new Date($('#transferDate').val());
        if (selectedDate > serverDate) {
            Swal.fire({
                icon: 'error',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'กรุณาเลือกวันที่ไม่เกินวันปัจจุบัน',
                confirmButtonText: 'ตกลง'
            });
            $('#transferDate').val(serverDate.toISOString().split('T')[0]);
        }
    }

    loadServerDate();
    $('#transferForm').submit(function(e) {
        e.preventDefault();
        let transferDate = new Date($('#transferDate').val());
        let today = new Date();
        today.setHours(0, 0, 0, 0);
        let maxDate = new Date(today);
        maxDate.setDate(maxDate.getDate() + 1);

        if (transferDate > maxDate) {
            Swal.fire({
                icon: 'error',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'วันที่โอนย้ายต้องไม่เกินวันปัจจุบัน',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        let formData = {
            transfer_date: $('#transferDate').val(),
            products: []
        };

        $('#transferTable tbody tr').each(function() {
        let productId = $(this).find('input[name="product_ids[]"]').val();
        let fromLocation = $(this).find('.from-location').val();
        let toLocation = $(this).find('.to-location').val();
        let quantity = $(this).find('.quantity').val();
        let unit = $(this).find('td:eq(4)').text();
        if (productId && fromLocation && toLocation && quantity) {
            formData.products.push({
                product_id: productId,
                from_location_id: fromLocation,
                to_location_id: toLocation,
                quantity: quantity,
                unit: unit
            });
        }
    });

        if (formData.products.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'ข้อมูลไม่ครบถ้วน',
            text: 'กรุณากรอกข้อมูลสินค้าให้ครบถ้วนอย่างน้อย 1 รายการ',
            confirmButtonText: 'ตกลง'
        });
        return;
    }
        $.ajax({
            url: '../system/save_transfer.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'บันทึกการโอนย้ายสินค้าเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("XHR Status:", status);
                console.error("Error:", error);
                console.error("Response Text:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถบันทึกการโอนย้ายสินค้าได้ โปรดลองอีกครั้งหรือติดต่อผู้ดูแลระบบ',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    });
});
</script>
</body>
</html>