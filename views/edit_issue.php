<?php
require_once '../config/permission.php';
requirePermission(['manage_issue']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขรายการเบิก</title>
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
                        <h3 class="page-title">แก้ไขรายการเบิก</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="editIssueForm">
                                <input type="hidden" id="issueId" name="issueId">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>เลขที่เบิก</label>
                                            <input type="text" class="form-control" id="billNumber" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>วันที่เบิก</label>
                                            <input type="date" class="form-control" id="issueDate" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>ประเภทการเบิก</label>
                                            <select class="form-control" id="issueType">
                                                <option value="sale">ขาย</option>
                                                <option value="project">โครงการ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group" id="customerSection">
                                            <label for="customer">ลูกค้า</label>
                                            <select class="form-control" id="customer" name="customer">
                                                <option value="">เลือกลูกค้า</option>
                                      
                                            </select>
                                        </div>
                                        <div class="form-group" id="projectSection" style="display: none;">
                                            <label for="project">โครงการ</label>
                                            <select class="form-control" id="project" name="project">
                                                <option value="">เลือกโครงการ</option>
                                               
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h4>รายการสินค้าที่เบิก</h4>
                                        <table class="table table-bordered" id="issueTable">
                                            <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ชื่อสินค้า</th>
                                                    <th>คลังสินค้า</th>
                                                    <th>จำนวนที่เบิก</th>
                                                    <th>จำนวนก่อนเบิก</th>
                                                    <th>หน่วย</th>
                                                    <th>การดำเนินการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- รายการสินค้าที่เบิกจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h4>เลือกสินค้า</h4>
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

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="history.back()">ยกเลิก</button>
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
    <script src="../assets/js/script.js"></script>

    <script>
 $(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const issueId = urlParams.get('id');

    if (!issueId) {
        Swal.fire('Error', 'ไม่พบรหัสรายการเบิก', 'error');
        return;
    }

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
        updateFormStatus();  // เพิ่มฟังก์ชันนี้หากคุณต้องการอัปเดตสถานะฟอร์มเมื่อมีการเปลี่ยนแปลงวันที่
    });

    let availableLocations = [];
    let productSelectionCount = {};
    let productLocationSelections = {};
    let productTable;
    let originalIssueData = {};

    function loadAvailableLocations() {
        return $.ajax({
            url: '../api/get_locations.php',
            type: 'GET',
            dataType: 'json'
        }).then(function (response) {
            if (response.status === 'success') {
                availableLocations = response.data;
            } else {
                console.error('Failed to load locations:', response.message);
            }
        });
    }

            function loadIssueData(id) {
                return fetch(`../api/get_issue_details.php?id=${id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('API Response:', data); // เพิ่ม log เพื่อตรวจสอบข้อมูลที่ได้รับ
                        if (data.status === 'error') {
                            throw new Error(data.message || 'Unknown error occurred');
                        }
                        if (!data.data) {
                            throw new Error('No data returned from API');
                        }
                        originalIssueData = JSON.parse(JSON.stringify(data.data)); // กำหนดค่า originalIssueData
                        return data.data;
                    })
                    .catch(error => {
                        console.error('Error in loadIssueData:', error);
                        throw error;
                    });
            }

            // เรียกใช้ฟังก์ชัน
            loadIssueData(issueId)
                .then(data => {
                    populateForm(data);
                    initializeProductTable();
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถโหลดข้อมูลได้',
                        text: 'กรุณาลองโหลดหน้านี้ใหม่อีกครั้ง หากยังไม่สำเร็จ โปรดติดต่อผู้ดูแลระบบ',
                        footer: 'รายละเอียดข้อผิดพลาด: ' + error.message
                    });
                });

            Promise.all([loadAvailableLocations(), loadIssueData(issueId)])
                .then(function ([locationsResponse, issueData]) {
                    if (issueData) {
                        originalIssueData = JSON.parse(JSON.stringify(issueData));
                        populateForm(issueData);
                        initializeProductTable();
                    } else {
                        throw new Error('Issue data is undefined');
                    }
                })
                .catch(function (error) {
                    console.error('Error details:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถโหลดข้อมูลได้',
                        text: 'กรุณาลองโหลดหน้านี้ใหม่อีกครั้ง หากยังไม่สำเร็จ โปรดติดต่อผู้ดูแลระบบ',
                        footer: 'รายละเอียดข้อผิดพลาด: ' + error.message
                    });
                });

                function populateForm(data) {
    if (!data) {
        console.error('No data to populate form');
        Swal.fire('Error', 'ไม่พบข้อมูลรายการเบิก', 'error');
        return;
    }

    originalIssueData = JSON.parse(JSON.stringify(data));

    $('#issueId').val(data.bill_number || '');
    $('#billNumber').val(data.bill_number || '');
    $('#issueDate').val(data.issue_date || '');
    $('#issueType').val(data.issue_type || '');

    updateSections(data.issue_type);

    if (data.issue_type === 'sale') {
        loadCustomerData().then(() => {
            setTimeout(() => {
                const customerSelect = $('#customer');
                customerSelect.val(customerSelect.find('option:contains("' + data.customer_name + '")').val());
                if (!customerSelect.val()) {
                    console.warn('Customer not found in list, adding it manually');
                    customerSelect.append($('<option>').val(data.customer_name).text(data.customer_name));
                    customerSelect.val(data.customer_name);
                }
                customerSelect.trigger('change');
            }, 100);
        });
    } else if (data.issue_type === 'project') {
        loadProjectData().then(() => {
            $('#project').val(data.project_id || '').trigger('change');
        });
    }

    const tbody = $('#issueTable tbody');
    tbody.empty();

    productSelectionCount = {};
    productLocationSelections = {};

    if (Array.isArray(data.items) && data.items.length > 0) {
        data.items.forEach(function (item) {
            addProductToIssueTable(item, true);
            if (!productSelectionCount[item.product_id]) {
                productSelectionCount[item.product_id] = 0;
            }
            productSelectionCount[item.product_id]++;
        });
    }
    updateAllProductSelectionStatus();
    updateFormStatus();
}


            function updateAllProductSelectionStatus() {
                for (var productId in productSelectionCount) {
                    updateProductSelectionStatus(productId);
                }
            }
            function updateProductTableQuantity(productId, quantity) {
    productTable.rows().every(function(rowIdx, tableLoop, rowLoop) {
        var data = this.data();
        if (data.product_id === productId) {
            data.locations.forEach(function(location) {
                if (location.id === locationId) {
                    location.quantity = quantity;
                }
            });
            this.data(data).draw();
            return false; // ออกจากลูปเมื่อพบสินค้าที่ต้องการ
        }
    });
}
            function updateSections(issueType) {
                if (issueType === 'sale') {
                    $('#customerSection').show();
                    $('#projectSection').hide();
                    $('#customer').prop('required', true);
                    $('#project').prop('required', false);
                } else if (issueType === 'project') {
                    $('#customerSection').hide();
                    $('#projectSection').show();
                    $('#customer').prop('required', false);
                    $('#project').prop('required', true);
                } else {
                    $('#customerSection').hide();
                    $('#projectSection').hide();
                    $('#customer').prop('required', false);
                    $('#project').prop('required', false);
                }
            }

            // เรียกใช้ฟังก์ชันนี้เมื่อโหลดหน้าและเมื่อมีการเปลี่ยนแปลงประเภทการเบิก
            $(document).ready(function () {
                updateSections($('#issueType').val());
                $('#issueType').change(function () {
                    updateSections($(this).val());
                });
            });
            function loadCustomerData() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../api/get_customers.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const select = $('#customer');
                    select.empty().append($('<option>').val('').text('เลือกลูกค้า'));
                    response.data.forEach(function(customer) {
                        let optionValue = customer.customer_id || customer.full_name;
                        let optionText = customer.full_name;
                        select.append($('<option>').val(optionValue).text(optionText));
                    });
                    resolve();
                } else {
                    console.error('Failed to load customers:', response.message);
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                reject(error);
            }
        });
    });
}

// เพิ่ม event listener สำหรับการเปลี่ยนแปลงลูกค้า
$('#customer').on('change', function() {
    var selectedOption = $(this).find('option:selected');
    $('#customerName').text(selectedOption.data('name') || '');
});

function loadProjectData() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../api/get_projects.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const select = $('#project');
                    select.empty().append($('<option>').val('').text('เลือกโครงการ'));
                    response.data.forEach(function(project) {
                        select.append($('<option>').val(project.project_id).text(project.project_name));
                    });
                    
                    // เลือกโครงการที่มีอยู่เดิม (ถ้ามี)
                    if (originalIssueData && originalIssueData.project_id) {
                        select.val(originalIssueData.project_id).trigger('change');
                    }
                    
                    resolve();
                } else {
                    console.error('Failed to load projects:', response.message);
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                reject(error);
            }
        });
    });
}
$(document).ready(function() {
    loadCustomerData();
    loadProjectData();
    updateSections($('#issueType').val());

    $('#issueType').change(function() {
        updateSections($(this).val());
        if ($(this).val() === 'sale') {
            loadCustomerData();
        } else if ($(this).val() === 'project') {
            loadProjectData();
        }
    });
});

            function initializeProductTable() {
                if ($.fn.DataTable.isDataTable('#productTable')) {
                    $('#productTable').DataTable().destroy();
                }

                productTable = $('#productTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '../api/get_products_for_issue.php',
                        type: 'POST',
                        data: function (d) {
                            d.issueId = issueId;
                        }
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row) {
                                var checked = (productSelectionCount[row.product_id] || 0) > 0 ? 'checked' : '';
                                var disabled = (productSelectionCount[row.product_id] || 0) >= row.locations.length ? 'disabled' : '';
                                return '<input type="checkbox" class="product-select" value="' + row.product_id + '" ' + checked + ' ' + disabled + '>';
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
                    order: [[2, 'asc']],
                    drawCallback: function (settings) {
                        updateAllProductSelectionStatus();
                    }
                });
            }

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
        // ไม่อนุญาตให้ลบผ่าน checkbox
        $(this).prop('checked', true);
        return false;
    }
    updateAvailableLocations(data.product_id);
    updateProductSelectionStatus(data.product_id);
});

function addProductToIssueTable(product, isExisting = false) {
    if (!productLocationSelections[product.product_id]) {
        productLocationSelections[product.product_id] = {};
    }

    var locationSelect = $('<select class="form-control location-select">').append(
        $('<option>').val('').text('เลือกคลังสินค้า')
    );

    var selectedLocationId = '';
    var originalQuantity = isExisting ? parseInt(product.quantity) : 0;
    var maxQuantity = product.locations.reduce((sum, location) => sum + parseInt(location.quantity), 0) + originalQuantity;

    product.locations.forEach(function (location) {
        var originalQuantity = parseInt(location.quantity);
        var currentQuantity = originalQuantity;
        if (isExisting && location.id == product.location_id) {
            currentQuantity += parseInt(product.quantity);
            selectedLocationId = location.id;
        }
        locationSelect.append($('<option>')
            .val(location.id)
            .text(location.name + ' (คงเหลือ: ' + currentQuantity + ')')
            .data('originalQuantity', originalQuantity)
            .data('currentQuantity', currentQuantity)
        );
    });

    var newRow = $('<tr>')
        .attr('data-product-id', product.product_id)
        .attr('data-location-id', selectedLocationId)
        .append(
            $('<td>').text(product.product_id),
            $('<td>').text(product.name_th || product.product_name),
            $('<td>').append(locationSelect),
            $('<td>').append($('<input>').attr({
                type: 'number',
                class: 'form-control quantity',
                value: originalQuantity,
                min: 0,
                max: maxQuantity,
                required: true
            })),
            $('<td>').addClass('original-quantity').text(originalQuantity),
            $('<td>').text(product.unit),
            $('<td>').append($('<button>').attr({
                type: 'button',
                class: 'btn btn-danger btn-sm remove-row'
            }).text('ลบ'))
        );

    $('#issueTable tbody').append(newRow);

    if (selectedLocationId) {
        locationSelect.val(selectedLocationId).trigger('change');
        productLocationSelections[product.product_id][selectedLocationId] = true;
    }

    if (isExisting) {
        productSelectionCount[product.product_id] = (productSelectionCount[product.product_id] || 0) + 1;
    }

    updateAvailableLocations(product.product_id);
    updateProductSelectionStatus(product.product_id);
    updateFormStatus();
}



$('#issueTable').on('change', '.location-select', function () {
    var row = $(this).closest('tr');
    var productId = row.data('product-id');
    var quantityInput = row.find('.quantity');
    var originalQuantityCell = row.find('.original-quantity');
    var selectedOption = $(this).find(':selected');
    var maxQuantity = 0;
    $(this).find('option').each(function() {
        maxQuantity += parseInt($(this).data('originalQuantity')) || 0;
    });
    var originalQuantity = parseInt(originalQuantityCell.text());

    quantityInput.attr('max', maxQuantity);
    if (parseInt(quantityInput.val()) > maxQuantity) {
        quantityInput.val(maxQuantity);
    }

    updateAvailableLocations(productId);
});

$('#issueTable').on('input', '.quantity', function () {
    var row = $(this).closest('tr');
    var productId = row.data('product-id');
    var max = parseInt($(this).attr('max'));
    var value = parseInt($(this).val());

    if (isNaN(value) || value < 0) {
        $(this).val(0);
    } else if (value > max) {
        $(this).val(max);
        Swal.fire({
            icon: 'warning',
            title: 'เกินจำนวนคงเหลือ',
            text: 'จำนวนที่เบิกไม่สามารถเกินจำนวนคงเหลือรวมในทุกคลังได้',
            confirmButtonText: 'ตกลง'
        });
    }

    updateAvailableLocations(productId);
});

function updateAvailableLocations(productId) {
    console.log('Updating available locations for product:', productId);

    if (!productTable || !productTable.rows || typeof productTable.rows !== 'function') {
        console.warn('Product table is not initialized or invalid');
        return;
    }

    var productData = productTable.rows().data();
    var product = productData.filter(function(d) {
        return d.product_id === productId;
    })[0];

    if (!product) {
        console.warn('Product not found in table:', productId);
        return;
    }

    var totalQuantity = product.locations.reduce((sum, location) => sum + parseInt(location.quantity), 0);

    $('#issueTable tbody tr[data-product-id="' + productId + '"]').each(function () {
        var locationSelect = $(this).find('.location-select');
        var quantityInput = $(this).find('.quantity');
        var currentLocationId = locationSelect.val();
        var isExisting = true;
        var originalQuantity = parseInt($(this).find('.original-quantity').text()) || 0;

        var maxQuantity = totalQuantity + originalQuantity;

        locationSelect.find('option').each(function () {
            var optionLocationId = $(this).val();
            if (optionLocationId) {
                var locationData = product.locations.find(loc => loc.id == optionLocationId);
                if (locationData) {
                    var locationOriginalQuantity = parseInt(locationData.quantity);
                    var currentQuantity = locationOriginalQuantity;
                    if (isExisting && optionLocationId == currentLocationId) {
                        currentQuantity += originalQuantity;
                    }
                    $(this).text(locationData.name + ' (คงเหลือ: ' + currentQuantity + ')');
                    $(this).data('originalQuantity', locationOriginalQuantity);
                    $(this).data('currentQuantity', currentQuantity);
                }
            }
        });

        quantityInput.attr('max', maxQuantity);
        var currentValue = parseInt(quantityInput.val()) || 0;
        if (currentValue > maxQuantity) {
            quantityInput.val(maxQuantity);
            Swal.fire({
                icon: 'warning',
                title: 'เกินจำนวนคงเหลือ',
                text: 'จำนวนที่เบิกไม่สามารถเกินจำนวนคงเหลือรวมในทุกคลังได้',
                confirmButtonText: 'ตกลง'
            });
        }

        console.log('Updated row:', {
            productId: productId,
            maxQuantity: maxQuantity,
            currentLocationId: currentLocationId
        });
    });

    // ย้าย updateProductSelectionStatus ไปที่นี่
    updateProductSelectionStatus(productId);
}
function removeProductFromIssueTable(productId) {
    var removedRow = $('#issueTable tbody').find(`tr[data-product-id="${productId}"]:last`);
    var removedLocationId = removedRow.find('.location-select').val();

    if (removedLocationId) {
        if (productLocationSelections[productId]) {
            delete productLocationSelections[productId][removedLocationId];
        }
    }

    removedRow.remove();

    // ลดจำนวนการเลือกสินค้า
    if (productSelectionCount[productId]) {
        productSelectionCount[productId]--;
    }

    updateProductSelectionStatus(productId);
    updateAvailableLocations(productId);

    $('#issueTable tbody tr').each(function () {
        updateAvailableLocations($(this).data('product-id'));
    });

    // อัพเดทสถานะฟอร์ม
    updateFormStatus();
}

$('#issueTable').on('click', '.remove-row', function () {
    var row = $(this).closest('tr');
    var productId = row.data('product-id');
    var quantity = parseInt(row.find('.quantity').val());

    if (quantity > 0) {
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: "คุณแน่ใจหรือไม่ที่จะลบรายการนี้?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบรายการ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                removeProductFromIssueTable(productId);
            }
        });
    } else {
        removeProductFromIssueTable(productId);
    }
});

function updateProductSelectionStatus(productId) {
    if (!productTable || !productTable.data || !productTable.data().length) {
        console.warn('Product table is not initialized or empty');
        return;
    }

    var rowData = productTable.rows().data().filter(function (d) {
        return d.product_id === productId;
    });

    if (rowData.length > 0) {
        var data = rowData[0];
        var checkbox = $('#productTable').find('input[value="' + productId + '"]');

        if (data && data.locations) {
            var availableLocations = data.locations.filter(loc => parseInt(loc.quantity) > 0).length;
            var selectedLocations = productSelectionCount[productId] || 0;

            if (selectedLocations >= availableLocations) {
                checkbox.prop('disabled', true);
            } else {
                checkbox.prop('disabled', false);
            }

            // ไม่ติ๊ก checkbox โดยอัตโนมัติ แต่ยังคงการ disable ไว้
            checkbox.prop('checked', false);
        } else {
            console.warn('Product data or locations not available for product ID:', productId);
        }
    } else {
        console.warn('Product not found in table:', productId);
    }
}

            $('#editIssueForm').on('submit', function (e) {
                e.preventDefault();  // ป้องกันการ submit form แบบปกติ
                submitForm();  // เรียกใช้ฟังก์ชัน submitForm ที่เราสร้างไว้
            });
            function submitForm() {
    const issueType = $('#issueType').val();
    const formData = {
        issueId: $('#issueId').val(),
        issueDate: $('#issueDate').val(),
        issueType: issueType,
        customerId: issueType === 'sale' ? $('#customer').val() : null,
        projectId: issueType === 'project' ? $('#project').val() : null,
        items: []
    };

    // ตรวจสอบข้อมูลก่อนส่ง
    if (!formData.issueId || !formData.issueDate || !formData.issueType) {
        Swal.fire('ข้อผิดพลาด', 'กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
        return;
    }

    if (issueType === 'sale' && !formData.customerId) {
        Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกลูกค้า', 'error');
        return;
    }

    if (issueType === 'project' && !formData.projectId) {
        Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกโครงการ', 'error');
        return;
    }

    var isValid = true;
    $('#issueTable tbody tr').each(function () {
        const item = {
            productId: $(this).data('product-id'),
            locationId: $(this).find('.location-select').val(),
            quantity: parseInt($(this).find('.quantity').val()),
            originalQuantity: parseInt($(this).find('.original-quantity').text())
        };

        if (!item.productId || !item.locationId || isNaN(item.quantity)) {
            isValid = false;
            Swal.fire('ข้อผิดพลาด', 'กรุณากรอกข้อมูลสินค้าให้ครบถ้วน', 'error');
            return false;
        }

        var maxQuantity = parseInt($(this).find('.quantity').attr('max'));
        if (item.quantity > maxQuantity) {
            isValid = false;
            Swal.fire('ข้อผิดพลาด', `สินค้า ${item.productId} ในคลัง ${item.locationId} มีจำนวนเกินกว่าที่มีในคลัง`, 'error');
            return false;
        }

        formData.items.push(item);
    });

    if (!isValid) return;

    if (formData.items.length === 0) {
        Swal.fire('ข้อผิดพลาด', 'กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ', 'error');
        return;
    }

    console.log('Sending data:', formData);

    // แสดง loading
    Swal.fire({
        title: 'กำลังบันทึกข้อมูล',
        text: 'กรุณารอสักครู่...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '../system/update_issue.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function (response) {
            console.log('Received response:', response);
            Swal.close();
            if (response.status === 'success') {
                Swal.fire({
                    title: 'สำเร็จ',
                    text: 'บันทึกการแก้ไขเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.href = 'issue_history.php';
                });
            } else {
                Swal.fire('ข้อผิดพลาด', response.message || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ', 'error');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            Swal.close();
            try {
                var response = JSON.parse(jqXHR.responseText);
                Swal.fire('ข้อผิดพลาด', response.message || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ', 'error');
            } catch (e) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถบันทึกการแก้ไขได้: ' + textStatus, 'error');
            }
        }
    });
}
            function updateFormStatus() {
                $('button[type="submit"]').prop('disabled', false);
            }

            function initializeForm() {
                $('#issueDate').data('original', $('#issueDate').val());
                $('#issueType').data('original', $('#issueType').val());
                $('#customer, #project').data('original', function () { return $(this).val(); });
            }

            $('#issueType').change(function () {
                updateSections($(this).val());
            });

            $('button.btn-secondary').on('click', function () {
                if ($('button[type="submit"]').prop('disabled')) {
                    history.back();
                } else {
                    Swal.fire({
                        title: 'ยืนยันการยกเลิก',
                        text: "คุณมีการเปลี่ยนแปลงที่ยังไม่ได้บันทึก ต้องการยกเลิกหรือไม่?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ใช่, ยกเลิกการแก้ไข',
                        cancelButtonText: 'ไม่, ทำต่อ'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            history.back();
                        }
                    });
                }
            });




            function checkFormChanges() {
                var isChanged = false;

                if ($('#issueDate').val() !== $('#issueDate').data('original')) isChanged = true;
                if ($('#issueType').val() !== $('#issueType').data('original')) isChanged = true;
                if ($('#customer').val() !== $('#customer').data('original')) isChanged = true;
                if ($('#project').val() !== $('#project').data('original')) isChanged = true;

                // ตรวจสอบการเปลี่ยนแปลงในรายการสินค้า
                if (originalIssueData && originalIssueData.items && Array.isArray(originalIssueData.items)) {
                    $('#issueTable tbody tr').each(function () {
                        var productId = $(this).data('product-id');
                        var locationId = $(this).find('.location-select').val();
                        var quantity = parseInt($(this).find('.quantity').val()) || 0;
                        var originalItem = originalIssueData.items.find(item =>
                            item.product_id === productId && item.location_id === locationId
                        );

                        if (!originalItem || originalItem.quantity !== quantity) {
                            isChanged = true;
                            return false; // ออกจาก .each() loop
                        }
                    });
                } else {
                    console.warn('Original issue data is not properly initialized');
                    isChanged = true; // เพื่อความปลอดภัย, ถือว่ามีการเปลี่ยนแปลงถ้าไม่มีข้อมูลเดิม
                }

                $('button[type="submit"]').prop('disabled', !isChanged);
            }

        });
    </script>
</body>

</html>