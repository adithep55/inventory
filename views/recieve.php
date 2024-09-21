<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>บันทึกการรับสินค้า</title>
    
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
                        <h3 class="page-title">บันทึกการรับสินค้า</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="receiveForm">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>วันที่รับสินค้า</label>
                                            <input type="date" class="form-control" id="receiveDate" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>ประเภทการรับ</label>
                                            <select class="form-control" id="receiveType">
                                                <option value="normal">รับสินค้าปกติ</option>
                                                <option value="opening">ยอดยกมา (เริ่มต้นระบบ)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h4>รายการสินค้าที่จะรับ</h4>
                                        <table class="table table-bordered" id="receiveTable">
                                            <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ชื่อสินค้า</th>
                                                    <th>คลังสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>หน่วย</th>
                                                    <th>การดำเนินการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- รายการสินค้าที่รับจะถูกเพิ่มที่นี่ -->
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
                                                        <th>รหัสสินค้า</th>
                                                        <th>ชื่อสินค้า (ไทย)</th>
                                                        <th>ชื่อสินค้า (อังกฤษ)</th>
                                                        <th>หน่วย</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึกการรับสินค้า</button>
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
    <script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
    <script>
$(document).ready(function() {
        $('.select2').select2();
        
        function setMaxDate() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const maxDate = tomorrow.toISOString().split('T')[0];
            $('#receiveDate').attr('max', maxDate);
        }

        setMaxDate();

        $('#receiveDate').on('change', function() {
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
        var productTable;
        var productSelectionCount = {};
        var productLocationSelections = {};
        var availableLocations = [];

        function loadLocations() {
            return $.ajax({
                url: '../api/get_locations.php',
                type: 'GET',
                dataType: 'json'
            });
        }

        loadLocations().done(function(response) {
            if (response.status === 'success') {
                availableLocations = response.data;
            }
        });

    function initializeProductTable() {
        if (productTable) {
            productTable.destroy();
        }
        productSelectionCount = {};
        productLocationSelections = {};
        productTable = $('#productTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../api/get_products.php',
                type: 'POST'
            },
            columns: [
                {
                    data: null,
                    render: function(data, type, row) {
                        var disabled = (productSelectionCount[row.product_id] || 0) >= availableLocations.length ? 'disabled' : '';
                        return '<input type="checkbox" class="product-select" value="' + row.product_id + '" ' + disabled + '>';
                    }
                },
                {
                    data: 'image',
                    render: function(data, type, row) {
                        return '<img src="' + data + '" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">';
                    }
                },
                { data: 'product_id' },
                { data: 'name_th' },
                { data: 'name_en' },
                { data: 'unit' }
            ],
            order: [[2, 'asc']]
        });
    }

    initializeProductTable();

    $('#productTable').on('change', '.product-select', function() {
        var row = $(this).closest('tr');
        var data = productTable.row(row).data();
        if (this.checked) {
            if (!productSelectionCount[data.product_id]) {
                productSelectionCount[data.product_id] = 0;
            }
            if (productSelectionCount[data.product_id] < availableLocations.length) {
                productSelectionCount[data.product_id]++;
                addProductToReceiveTable(data);
            } else {
                $(this).prop('checked', false);
                Swal.fire({
                    icon: 'warning',
                    title: 'ไม่สามารถเพิ่มสินค้าได้',
                    text: 'คุณได้เลือกสินค้านี้ครบตามจำนวนคลังที่มีแล้ว',
                    confirmButtonText: 'ตกลง'
                });
            }
        } else {
            removeAllProductInstances(data.product_id);
        }
        updateProductSelectionStatus(data.product_id);
    });

    function addProductToReceiveTable(product) {
            if (!productLocationSelections[product.product_id]) {
                productLocationSelections[product.product_id] = [];
            }

            var locationSelect = $('<select class="form-control location-select">').append(
                $('<option>').val('').text('เลือกคลังสินค้า')
            );
            
            availableLocations.forEach(function(location) {
                locationSelect.append($('<option>')
                    .val(location.location_id)
                    .text(location.location)
                );
            });
            
            var newRow = $('<tr>').attr('data-product-id', product.product_id).append(
                $('<td>').text(product.product_id),
                $('<td>').text(product.name_th),
                $('<td>').append(locationSelect),
                $('<td>').append($('<input>').attr({
                    type: 'number',
                    class: 'form-control quantity',
                    value: 1,
                    min: 1,
                    required: true
                })),
                $('<td>').text(product.unit),
                $('<td>').append($('<button>').attr({
                    type: 'button',
                    class: 'btn btn-danger btn-sm remove-row'
                }).text('ลบ'))
            );
            
            $('#receiveTable tbody').append(newRow);
            updateAvailableLocations(product.product_id);
        }


        $('#receiveTable').on('change', '.location-select', function() {
            var row = $(this).closest('tr');
            var productId = row.data('product-id');
            var selectedLocationId = $(this).val();

            if (selectedLocationId) {
                if (productLocationSelections[productId].includes(selectedLocationId)) {
                    $(this).val('');
                    Swal.fire({
                        icon: 'warning',
                        title: 'คลังสินค้าซ้ำ',
                        text: 'คุณได้เลือกคลังสินค้านี้สำหรับสินค้านี้แล้ว',
                        confirmButtonText: 'ตกลง'
                    });
                } else {
                    productLocationSelections[productId].push(selectedLocationId);
                    updateAvailableLocations(productId);
                }
            }
        });

        function updateAvailableLocations(productId) {
            $('#receiveTable tbody tr[data-product-id="' + productId + '"]').each(function() {
                var locationSelect = $(this).find('.location-select');
                var currentLocationId = locationSelect.val();
                
                locationSelect.find('option').prop('disabled', false);
                
                productLocationSelections[productId].forEach(function(locId) {
                    if (locId !== currentLocationId) {
                        locationSelect.find('option[value="' + locId + '"]').prop('disabled', true);
                    }
                });
            });
        }

        function removeAllProductInstances(productId) {
            $('#receiveTable tbody tr[data-product-id="' + productId + '"]').remove();
            productLocationSelections[productId] = [];
            productSelectionCount[productId] = 0;
            updateProductSelectionStatus(productId);
        }

        function updateProductSelectionStatus(productId) {
            var checkbox = $('#productTable').find('input[value="' + productId + '"]');
            if (productSelectionCount[productId] >= availableLocations.length) {
                checkbox.prop('disabled', true);
            } else {
                checkbox.prop('disabled', false);
            }
            checkbox.prop('checked', productSelectionCount[productId] > 0);
            productTable.draw(false);
        }

        $('#receiveTable').on('click', '.remove-row', function() {
            var row = $(this).closest('tr');
            var productId = row.data('product-id');
            var locationId = row.find('.location-select').val();
            
            if (locationId) {
                productLocationSelections[productId] = productLocationSelections[productId].filter(id => id !== locationId);
            }
            
            row.remove();
            productSelectionCount[productId]--;
            updateProductSelectionStatus(productId);
            updateAvailableLocations(productId);
        });

        $('#receiveForm').submit(function(e) {
            e.preventDefault();
            
            $('.form-control').removeClass('error-highlight');
            
            var isValid = true;
            var errorMessage = '';

            // Date validation
            let receiveDate = new Date($('#receiveDate').val());
            let today = new Date();
            today.setHours(0, 0, 0, 0);
            let maxDate = new Date(today);
            maxDate.setDate(maxDate.getDate() + 1);
            if (receiveDate > maxDate) {
                $('#receiveDate').addClass('error-highlight');
                errorMessage += 'วันที่รับสินค้าต้องไม่เกินวันปัจจุบัน<br>';
                isValid = false;
            }

            if (!$('#receiveDate').val()) {
                $('#receiveDate').addClass('error-highlight');
                errorMessage += 'กรุณาเลือกวันที่รับสินค้า<br>';
                isValid = false;
            }

        if ($('#receiveTable tbody tr').length === 0) {
            errorMessage += 'กรุณาเลือกสินค้าที่ต้องการรับ<br>';
            isValid = false;
        } else {
            $('#receiveTable tbody tr').each(function() {
                var locationSelect = $(this).find('.location-select');
                var quantityInput = $(this).find('.quantity');
                
                if (!locationSelect.val()) {
                    locationSelect.addClass('error-highlight');
                    errorMessage += 'กรุณาเลือกคลังสินค้าสำหรับทุกรายการ<br>';
                    isValid = false;
                }
                
                if (!quantityInput.val() || parseInt(quantityInput.val()) < 1) {
                    quantityInput.addClass('error-highlight');
                    errorMessage += 'กรุณาระบุจำนวนที่ถูกต้องสำหรับทุกรายการ<br>';
                    isValid = false;
                }
            });
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

        var formData = {
            receiveDate: $('#receiveDate').val(),
            receiveType: $('#receiveType').val(),
            products: []
        };

        $('#receiveTable tbody tr').each(function() {
            var product = {
                productId: $(this).data('product-id'),
                locationId: $(this).find('.location-select').val(),
                quantity: $(this).find('.quantity').val(),
                unit: $(this).find('td:eq(4)').text()
            };
            formData.products.push(product);
        });

        $.ajax({
            url: '../system/save_receive.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ',
                        text: 'บันทึกการรับสินค้าเรียบร้อยแล้ว (เลขที่บิล: ' + response.message.split(':')[1].trim() + ')',
                        confirmButtonText: 'ตกลง'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message || 'ไม่สามารถบันทึกการรับสินค้าได้',
                        confirmButtonText: 'ตกลง'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    });

    $(document).on('input', '.quantity', function() {
        var value = parseInt($(this).val());
        if (isNaN(value) || value < 1) {
            $(this).val(1);
        }
    });

    $(document).on('change', '.form-control', function() {
        $(this).removeClass('error-highlight');
    });
});
</script>
<style>
        .error-highlight {
            border: 2px solid red !important;
        }
    </style>
</body>
</html>