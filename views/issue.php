<?php
require_once '../config/permission.php';
requirePermission(['manage_issue']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>เบิกสินค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
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
                <div class="row">
                    <div class="col">
                        <h3 class="page-title"><i class="fas fa-cart-arrow-down" ></i> เบิกสินค้า</h3>
                        <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url();?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">เบิกสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="issueForm">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>วันที่เบิกสินค้า</label>
                                            <input type="date" class="form-control" id="issueDate" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>ประเภทการเบิก</label>
                                            <select class="form-control" id="issueType">
                                                <option value="">กรุณาเลือกประเภทการเบิก</option>
                                                <option value="sale">เบิกเพื่อขาย</option>
                                                <option value="project">เบิกเพื่อโครงการ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="saleSection" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ลูกค้า</label>
                                            <select class="form-control select2" id="customer">
                                                <!-- เพิ่มตัวเลือกลูกค้าจากฐานข้อมูล -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="projectSection" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>โครงการ</label>
                                            <select class="form-control select2" id="project">
                                                <!-- เพิ่มตัวเลือกโครงการจากฐานข้อมูล -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h4>รายการสินค้าที่จะเบิก</h4>
                                        <table class="table table-bordered" id="issueTable">
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
                                                <!-- รายการสินค้าที่เบิกจะถูกเพิ่มที่นี่ -->
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
                                                        <th>จำนวนคงเหลือ</th>
                                                        <th>หน่วย</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึกการเบิกสินค้า</button>
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

    <style>
        .error-highlight {
            border: 2px solid red !important;
        }
    </style>

    <script>
$(document).ready(function () {
    $('.select2').select2();

    function setMaxDate() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const maxDate = tomorrow.toISOString().split('T')[0];
        $('#issueDate').attr('max', maxDate);
    }

    setMaxDate();

    $('#issueDate').on('change', function() {
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

    loadData('../api/get_customers.php', '#customer', 'customer_id', 'name', 'phone_number');
    loadData('../api/get_projects.php', '#project', 'project_id', 'project_name', 'start_date', 'end_date');

    updateSections($('#issueType').val());

    function updateSections(issueType) {
        if (issueType === 'sale') {
            $('#saleSection').show();
            $('#projectSection').hide();
        } else if (issueType === 'project') {
            $('#saleSection').hide();
            $('#projectSection').show();
        } else {
            $('#saleSection').hide();
            $('#projectSection').hide();
        }
    }

            function loadData(url, selectId, valueKey, textKey, additionalKey = null) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        var options = '<option value="">เลือก</option>';
                        if (response.status === 'success') {
                            $.each(response.data, function (index, item) {
                                var text = item[textKey];
                                if (selectId === '#customer') {
                                    // สำหรับลูกค้า ใช้ชื่อที่รวม prefix แล้ว
                                    text = item.full_name;
                                }
                                if (additionalKey && item[additionalKey]) {
                                    text += ' - ' + item[additionalKey];
                                }
                                if (selectId === '#project' && item.start_date) {
                                    text += ` (เริ่ม: ${item.start_date}${item.end_date ? ', สิ้นสุด: ' + item.end_date : ''})`;
                                }
                                options += `<option value="${item[valueKey]}">${text}</option>`;
                            });
                            $(selectId).html(options);
                        }
                    },
                    error: function () {
                        // Error handling
                    }
                });
            }

            var productSelectionCount = {};
            var productLocationSelections = {}; // New object to track selected locations for each product
            var productTable;

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
            url: '../api/get_products_for_issue.php',
            type: 'POST',
            dataSrc: function(json) {
                // กรองสินค้าที่มีจำนวนรวมมากกว่า 0
                return json.data.filter(function(product) {
                    var totalQuantity = product.locations.reduce(function(sum, location) {
                        return sum + parseInt(location.quantity);
                    }, 0);
                    return totalQuantity > 0;
                });
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
    var availableLocations = row.locations.filter(location => parseInt(location.quantity) > 0).length;
    var disabled = (productSelectionCount[row.product_id] || 0) >= availableLocations ? 'disabled' : '';
    return '<input type="checkbox" class="product-select" value="' + row.product_id + '" ' + disabled + '>';
}
            },
            {
                data: 'image',
                render: function (data, type, row) {
                    return '<img src="' + data + '" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">';
                }
            },
            { data: 'product_id' },
            { data: 'name_th' },
            { data: 'name_en' },
            {
                data: 'locations',
                render: function (data, type, row) {
                    return data.reduce((sum, location) => sum + parseInt(location.quantity), 0);
                }
            },
            { data: 'unit' }
        ],
        order: [[2, 'asc']]
        ,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "emptyTable": "ไม่พบข้อมูลสินค้า",
                        "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                        "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                        "search": "ค้นหา:",
                        "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
    });
}

            initializeProductTable();

            $('#issueType').change(function () {
                updateSections($(this).val());
                $('#issueTable tbody').empty();
                initializeProductTable();
            });

            $('#productTable').on('change', '.product-select', function () {
                var row = $(this).closest('tr');
                var data = productTable.row(row).data();
                if (this.checked) {
                    if (!productSelectionCount[data.product_id]) {
                        productSelectionCount[data.product_id] = 0;
                    }
                    productSelectionCount[data.product_id]++;
                    addProductToIssueTable(data);
                } else {
                    productSelectionCount[data.product_id]--;
                    removeProductFromIssueTable(data.product_id);
                }
                updateProductSelectionStatus(data.product_id);
            });

            function updateProductSelectionStatus(productId) {
    var row = productTable.row(function (idx, data, node) {
        return data.product_id === productId;
    });
    if (row.length) {
        var data = row.data();
        var checkbox = $(row.node()).find('.product-select');
        var availableLocations = data.locations.filter(location => parseInt(location.quantity) > 0).length;
        if (productSelectionCount[productId] >= availableLocations) {
            checkbox.prop('disabled', true);
        } else {
            checkbox.prop('disabled', false);
        }
        productTable.cell(row, 0).invalidate().draw(false);
    }
}

            function addProductToIssueTable(product) {
    if (!productLocationSelections[product.product_id]) {
        productLocationSelections[product.product_id] = [];
    }

    var locationSelect = $('<select class="form-control location-select">').append(
        $('<option>').val('').text('เลือกคลังสินค้า')
    );

    product.locations.forEach(function (location) {
    if (parseInt(location.quantity) > 0) {  // แสดงเฉพาะคลังที่มีสินค้า
        locationSelect.append($('<option>')
            .val(location.id)
            .text(location.name + ' (คงเหลือ: ' + location.quantity + ')')
            .data('quantity', location.quantity)
        );
    }
});

    var newRow = $('<tr>').attr('data-product-id', product.product_id).append(
        $('<td>').text(product.product_id),
        $('<td>').text(product.name_th),
        $('<td>').append(locationSelect),
        $('<td>').append($('<input>').attr({
            type: 'number',
            class: 'form-control quantity',
            value: "",
            max: locationSelect.find('option:first').data('quantity'),
            required: true
        })),
        $('<td>').text(product.unit),
        $('<td>').append($('<button>').attr({
            type: 'button',
            class: 'btn btn-danger btn-sm remove-row'
        }).text('ลบ'))
    );

    $('#issueTable tbody').append(newRow);
}

            $('#issueTable').on('change', '.location-select', function () {
                var row = $(this).closest('tr');
                var productId = row.data('product-id');
                var selectedLocationId = $(this).val();

                if (selectedLocationId) {
                    if (!productLocationSelections[productId]) {
                        productLocationSelections[productId] = [];
                    }
                    productLocationSelections[productId].push(parseInt(selectedLocationId));
                }

                var maxQuantity = $(this).find(':selected').data('quantity');
                var quantityInput = row.find('.quantity');
                quantityInput.attr('max', maxQuantity);
                if (parseInt(quantityInput.val()) > maxQuantity) {
                    quantityInput.val(maxQuantity);
                }

                updateAvailableLocations(productId);
            });

            function updateAvailableLocations(productId) {
                $('#issueTable tbody tr[data-product-id="' + productId + '"]').each(function () {
                    var locationSelect = $(this).find('.location-select');
                    var currentLocationId = locationSelect.val();

                    locationSelect.find('option').each(function () {
                        var optionLocationId = $(this).val();
                        if (optionLocationId && optionLocationId !== currentLocationId) {
                            $(this).prop('disabled', productLocationSelections[productId].includes(parseInt(optionLocationId)));
                        }
                    });
                });
            }

            function removeProductFromIssueTable(productId) {
                var removedRow = $('#issueTable tbody').find(`tr[data-product-id="${productId}"]:last`);
                var removedLocationId = removedRow.find('.location-select').val();

                if (removedLocationId) {
                    productLocationSelections[productId] = productLocationSelections[productId].filter(id => id !== parseInt(removedLocationId));
                }

                removedRow.remove();
                updateProductSelectionStatus(productId);
                updateAvailableLocations(productId);
            }

            $('#issueTable').on('click', '.remove-row', function () {
                var row = $(this).closest('tr');
                var productId = row.data('product-id');
                var removedLocationId = row.find('.location-select').val();

                if (removedLocationId) {
                    productLocationSelections[productId] = productLocationSelections[productId].filter(id => id !== parseInt(removedLocationId));
                }

                row.remove();
                productSelectionCount[productId]--;
                $('#productTable').find(`input[value="${productId}"]`).prop('checked', false);
                updateProductSelectionStatus(productId);
                updateAvailableLocations(productId);
            });

            $('#issueForm').submit(function (e) {
                e.preventDefault();

                $('.form-control').removeClass('error-highlight');

                var isValid = true;
                var errorMessage = '';

                if (!$('#issueDate').val()) {
                    $('#issueDate').addClass('error-highlight');
                    errorMessage += 'กรุณาเลือกวันที่เบิกสินค้า<br>';
                    isValid = false;
                }

                var issueType = $('#issueType').val();
                if (!issueType) {
                    $('#issueType').addClass('error-highlight');
                    errorMessage += 'กรุณาเลือกประเภทการเบิก<br>';
                    isValid = false;
                } else {
                    if (issueType === 'sale' && !$('#customer').val()) {
                        $('#customer').addClass('error-highlight');
                        errorMessage += 'กรุณาเลือกลูกค้า<br>';
                        isValid = false;
                    } else if (issueType === 'project' && !$('#project').val()) {
                        $('#project').addClass('error-highlight');
                        errorMessage += 'กรุณาเลือกโครงการ<br>';
                        isValid = false;
                    }
                }

                if ($('#issueTable tbody tr').length === 0) {
                    errorMessage += 'กรุณาเลือกสินค้าที่ต้องการเบิก<br>';
                    isValid = false;
                } else {
                    $('#issueTable tbody tr').each(function () {
        var locationSelect = $(this).find('.location-select');
        var quantityInput = $(this).find('.quantity');

        if (!locationSelect.val()) {
            locationSelect.addClass('error-highlight');
            errorMessage += 'กรุณาเลือกคลังสินค้าสำหรับทุกรายการ<br>';
            isValid = false;
        }

        if (quantityInput.val() === '' || isNaN(parseInt(quantityInput.val())) || parseInt(quantityInput.val()) <= 0) {
            quantityInput.addClass('error-highlight');
            errorMessage += 'กรุณาระบุจำนวนที่ถูกต้อง (มากกว่า 0) สำหรับทุกรายการ<br>';
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
                    issueDate: $('#issueDate').val(),
                    issueType: issueType,
                    customer: issueType === 'sale' ? $('#customer').val() : null,
                    project: issueType === 'project' ? $('#project').val() : null,
                    products: []
                };

                $('#issueTable tbody tr').each(function () {
                    var locationSelect = $(this).find('.location-select');
                    var product = {
                        productId: $(this).find('td:first').text(),
                        locationId: locationSelect.val(),
                        locationName: locationSelect.find('option:selected').text(),
                        quantity: $(this).find('.quantity').val(),
                        unit: $(this).find('td:eq(4)').text()
                    };
                    formData.products.push(product);
                });

                $.ajax({
                    url: '../system/save_issue.php',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function (response) {
                        console.log('Server response:', response);
                        if (typeof response === 'string') {
                            try {
                                response = JSON.parse(response);
                            } catch (e) {
                                console.error('Error parsing JSON:', e);
                            }
                        }
                        if (response && response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกสำเร็จ',
                                text: 'บันทึกการเบิกสินค้าเรียบร้อยแล้ว (เลขที่บิล: ' + response.billNumber + ')',
                                confirmButtonText: 'ตกลง'
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: (response && response.message) || 'ไม่สามารถบันทึกการเบิกสินค้าได้',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.log('XHR Response:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                });
            });

            // ป้องกันการใส่จำนวนที่ไม่ถูกต้องในช่องจำนวน
            $(document).on('input', '.quantity', function () {
    var max = parseInt($(this).attr('max'));
    var value = $(this).val();

    if (value === '') {
        // ให้ค่าว่างได้
        return;
    }

    value = parseInt(value);

    if (isNaN(value) || value < 0) {
        $(this).val('');
    } else if (value > max) {
        $(this).val(max);
        Swal.fire({
            icon: 'warning',
            title: 'เกินจำนวนคงเหลือ',
            text: 'จำนวนที่เบิกไม่สามารถเกินจำนวนคงเหลือได้',
            confirmButtonText: 'ตกลง'
        });
    }
});

            // รีเซ็ตไฮไลท์เมื่อผู้ใช้แก้ไขข้อมูล
            $(document).on('change', '.form-control', function () {
                $(this).removeClass('error-highlight');
            });
        });
    </script>
</body>

