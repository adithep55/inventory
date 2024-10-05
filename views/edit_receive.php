<?php
require_once '../config/permission.php';
requirePermission(['manage_receiving']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขการรับสินค้า</title>
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
                        <h3 class="page-title">แก้ไขการรับสินค้า</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="editReceiveForm">
                                <input type="hidden" id="receiveId" name="receiveId">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>เลขที่เอกสารรับสินค้า</label>
                                            <input type="text" class="form-control" id="billNumber" readonly>
                                        </div>
                                    </div>
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
                                        <h4>รายการสินค้าที่รับ</h4>
                                        <table class="table table-bordered" id="receiveItemsTable">
                                            <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ชื่อสินค้า</th>
                                                    <th>คลังสินค้า</th>
                                                    <th>จำนวนเดิม</th>
                                                    <th>จำนวนใหม่</th>
                                                    <th>หน่วย</th>
                                                    <th>การดำเนินการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- รายการสินค้าที่รับจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary" id="addNewItem">เพิ่มสินค้าใหม่</button>
                                        <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                                        <button type="button" class="btn btn-secondary" onclick="history.back()">ยกเลิก</button>
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
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const receiveId = urlParams.get('id');
    const today = new Date().toISOString().split('T')[0];
    $('#receiveDate').attr('max', today);

    let availableLocations = [];
    let productSelectionCount = {};
    let productLocationSelections = {};

    function loadAvailableLocations() {
        return $.ajax({
            url: '../api/get_locations.php',
            type: 'GET',
            dataType: 'json'
        }).then(function(response) {
            if (response.status === 'success') {
                availableLocations = response.data;
            } else {
                console.error('Failed to load locations:', response.message);
            }
        });
    }

    function loadReceiveData(id) {
        return $.ajax({
            url: '../api/get_receive_details.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json'
        });
    }
    loadReceiveData(receiveId).then(function(receiveResponse) {
        if (receiveResponse.status === 'success') {
            populateForm(receiveResponse.data);
            initializeForm();
        } else {
            Swal.fire('Error', receiveResponse.message, 'error');
        }
    });

    Promise.all([loadAvailableLocations(), loadReceiveData(receiveId)])
        .then(function([_, receiveResponse]) {
            if (receiveResponse.status === 'success') {
                populateForm(receiveResponse.data);
            } else {
                Swal.fire('Error', receiveResponse.message, 'error');
            }
        })
        .catch(function(error) {
            console.error('Error loading data:', error);
            Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลได้', 'error');
        });

        function populateForm(data) {
    $('#receiveId').val(data.receive_header_id);
    $('#billNumber').val(data.bill_number);
    $('#receiveDate').val(data.received_date);
    $('#receiveType').val(data.is_opening_balance == 1 ? 'opening' : 'normal');

    const tbody = $('#receiveItemsTable tbody');
    tbody.empty();
    data.items.forEach(function(item) {
        addItemRow(item);
    });
    $('#receiveItemsTable').data('original-row-count', data.items.length);
    updateFormStatus();
}

function addItemRow(item) {
        if (!productLocationSelections[item.product_id]) {
            productLocationSelections[item.product_id] = [];
        }
        if (!productLocationSelections[item.product_id].includes(item.location_id)) {
            productLocationSelections[item.product_id].push(item.location_id);
        }

        const existingRow = $(`#receiveItemsTable tbody tr[data-product-id="${item.product_id}"][data-location-id="${item.location_id}"]`);
        if (existingRow.length > 0) {
            existingRow.find('.location-select').val(item.location_id);
            existingRow.find('.new-quantity').val(item.quantity);
            existingRow.attr('data-location-id', item.location_id);
            existingRow.find('td:eq(3)').text(item.quantity);
        } else {
            const newRow = $('<tr>').attr({
                'data-product-id': item.product_id,
                'data-location-id': item.location_id,
                'data-original-location-id': item.location_id
            });
            newRow.html(`
                <td>${item.product_id}</td>
                <td>${item.product_name}</td>
                <td>
                    <select class="form-control location-select" data-original="${item.location_id}">
                        ${generateLocationOptions(item.product_id, item.location_id)}
                    </select>
                </td>
                <td>${item.quantity}</td>
                <td><input type="number" class="form-control new-quantity" value="${item.quantity}" min="0" step="1"></td>
                <td>${item.unit}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-item">ลบ</button></td>
            `);
            $('#receiveItemsTable tbody').append(newRow);
        }
        updateProductSelectionStatus(item.product_id);
        updateFormStatus();
    }
    function generateLocationOptions(productId, selectedLocationId) {
        return availableLocations.map(location => {
            const isDisabled = productLocationSelections[productId].includes(location.location_id) && location.location_id != selectedLocationId;
            return `<option value="${location.location_id}" ${location.location_id == selectedLocationId ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${location.location}</option>`;
        }).join('');
    }

    $('#receiveItemsTable').on('click', '.remove-item', function() {
        const row = $(this).closest('tr');
        const productId = row.attr('data-product-id');
        const locationId = parseInt(row.attr('data-location-id'));
        
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: "การลบรายการนี้อาจส่งผลต่อสต็อกสินค้า คุณแน่ใจหรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบรายการ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
        if (result.isConfirmed) {
            if (productLocationSelections[productId]) {
                productLocationSelections[productId] = productLocationSelections[productId].filter(id => id !== locationId);
            }
            if (productSelectionCount[productId]) {
                productSelectionCount[productId]--;
            }
            row.remove();
            updateProductSelectionStatus(productId);
            updateAvailableLocations(productId);
            updateFormStatus(); 
        }
    });
    });
    function updateAvailableLocations(productId) {
        const rows = $('#receiveItemsTable tbody tr[data-product-id="' + productId + '"]');
        const selectedLocations = new Set();

        // รวบรวมคลังสินค้าที่ถูกเลือกแล้วสำหรับสินค้านี้
        rows.each(function() {
            const locationId = $(this).find('.location-select').val();
            if (locationId) selectedLocations.add(parseInt(locationId));
        });

        // อัปเดตตัวเลือกคลังสินค้าสำหรับทุกแถวของสินค้านี้
        rows.each(function() {
            const locationSelect = $(this).find('.location-select');
            const currentLocationId = parseInt(locationSelect.val());
            
            locationSelect.find('option').each(function() {
                const optionLocationId = parseInt($(this).val());
                if (optionLocationId) {
                    $(this).prop('disabled', selectedLocations.has(optionLocationId) && optionLocationId !== currentLocationId);
                }
            });
        });
    }
    $('#addNewItem').on('click', function() {
    Swal.fire({
        title: 'เพิ่มสินค้าใหม่',
        html:
            '<select id="newProductId" class="swal2-input">' +
            '<option value="">กำลังโหลดข้อมูล...</option>' +
            '</select>' +
            '<select id="newLocationId" class="swal2-input">' +
            '<option value="">กำลังโหลดข้อมูล...</option>' +
            '</select>',
        focusConfirm: false,
        didOpen: () => {
            populateProductDropdown();
            populateLocationDropdown();
        },
        preConfirm: () => {
            return {
                productId: document.getElementById('newProductId').value,
                locationId: document.getElementById('newLocationId').value,
                quantity: 0  // กำหนดค่า default เป็น 0
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            addNewItemToTable(result.value);
        }
    });
});

    function populateProductDropdown() {
        $.ajax({
            url: '../api/get_products.php',
            type: 'POST',
            dataType: 'json',
            data: {
                draw: 1,
                start: 0,
                length: 1000,
                search: { value: '' }
            },
            success: function(response) {
                if (response && Array.isArray(response.data)) {
                    const select = $('#newProductId');
                    select.empty();
                    select.append($('<option>').val('').text('เลือกสินค้า'));
                    response.data.forEach(function(product) {
                        const option = $('<option>')
                            .val(product.product_id)
                            .text(product.name_th + ' (' + product.product_id + ')');
                        
                        if (productSelectionCount[product.product_id] >= availableLocations.length) {
                            option.prop('disabled', true);
                        }
                        
                        select.append(option);
                    });
                } else {
                    console.error('Invalid response from get_products.php:', response);
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลสินค้าได้', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
            }
        });
    }

    function populateLocationDropdown() {
        const select = $('#newLocationId');
        select.empty();
        select.append($('<option>').val('').text('เลือกคลังสินค้า'));
        availableLocations.forEach(function(location) {
            select.append($('<option>').val(location.location_id).text(location.location));
        });
    }

    $('#receiveItemsTable').on('change', '.location-select', function() {
        const row = $(this).closest('tr');
        const productId = row.attr('data-product-id');
        const newLocationId = parseInt($(this).val());
        const originalLocationId = parseInt(row.attr('data-original-location-id'));

        if (newLocationId !== originalLocationId) {
            const otherRows = $(`#receiveItemsTable tbody tr[data-product-id="${productId}"]`).not(row);
            const isLocationAlreadySelected = otherRows.find('.location-select').filter(function() {
                return parseInt($(this).val()) === newLocationId;
            }).length > 0;

            if (isLocationAlreadySelected) {
                Swal.fire('ข้อผิดพลาด', 'สินค้านี้มีอยู่ในคลังนี้แล้ว', 'error');
                $(this).val(originalLocationId);
                return;
            }

            row.attr('data-original-location-id', newLocationId);
        }

        updateAvailableLocations(productId);
        updateFormStatus();
    });


    function addNewItemToTable(item) {
        if (!productLocationSelections[item.productId]) {
            productLocationSelections[item.productId] = [];
        }

        if (productLocationSelections[item.productId].includes(parseInt(item.locationId))) {
            Swal.fire('ข้อผิดพลาด', 'สินค้านี้มีอยู่ในคลังนี้แล้ว', 'error');
            return;
        }

        if (productLocationSelections[item.productId].length >= availableLocations.length) {
            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเพิ่มสินค้านี้ได้อีก เนื่องจากได้เลือกครบทุกคลังแล้ว', 'error');
            return;
        }

        $.ajax({
            url: '../api/get_products.php',
            type: 'POST',
            dataType: 'json',
            data: {
                draw: 1,
                start: 0,
                length: 1,
                search: { value: item.productId }
            },
            success: function(response) {
                if (response && Array.isArray(response.data) && response.data.length > 0) {
                    const product = response.data[0];
                    addItemRow({
                        product_id: product.product_id,
                        product_name: product.name_th,
                        location_id: item.locationId,
                        quantity: item.quantity,
                        unit: product.unit
                    });
                } else {
                    console.error('Product not found:', item.productId);
                    Swal.fire('ข้อผิดพลาด', 'ไม่พบข้อมูลสินค้า', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
            }
        });
    }

   
 $('#receiveItemsTable').on('change', '.location-select', function() {
        const row = $(this).closest('tr');
        const productId = row.attr('data-product-id');
        const newLocationId = parseInt($(this).val());
        const originalLocationId = parseInt(row.attr('data-original-location-id'));

        if (newLocationId !== originalLocationId) {
            if (productLocationSelections[productId].includes(newLocationId)) {
                Swal.fire('ข้อผิดพลาด', 'สินค้านี้มีอยู่ในคลังนี้แล้ว', 'error');
                $(this).val(originalLocationId);
                return;
            }

            productLocationSelections[productId] = productLocationSelections[productId].filter(id => id !== originalLocationId);
            productLocationSelections[productId].push(newLocationId);
            row.attr('data-location-id', newLocationId);
            row.attr('data-original-location-id', newLocationId);
        }

        updateAvailableLocations(productId);
        updateFormStatus();
    });

    $('#receiveDate').on('change', function() {
        updateFormStatus();
    });
    function initializeForm() {
        const initialDate = $('#receiveDate').val();
        $('#receiveDate').data('original', initialDate);
        
        $('#receiveItemsTable tbody tr').each(function() {
            const row = $(this);
            const locationSelect = row.find('.location-select');
            locationSelect.data('original', locationSelect.val());
        });

        updateFormStatus();
    }

    $('#receiveItemsTable').on('change', '.new-quantity, .location-select', updateFormStatus);

    function updateFormStatus() {
        let hasChanges = false;
        
        // ตรวจสอบการเปลี่ยนแปลงวันที่
        const originalDate = $('#receiveDate').data('original');
        const currentDate = $('#receiveDate').val();
        if (originalDate !== currentDate) {
            hasChanges = true;
        }

       // ตรวจสอบการเปลี่ยนแปลงในรายการสินค้า
    const originalRowCount = $('#receiveItemsTable').data('original-row-count') || 0;
    const currentRowCount = $('#receiveItemsTable tbody tr').length;
    
    if (originalRowCount !== currentRowCount) {
        hasChanges = true;
    } else {
        $('#receiveItemsTable tbody tr').each(function() {
            const row = $(this);
            const originalQuantity = parseFloat(row.find('td:eq(3)').text());
            const newQuantity = parseFloat(row.find('.new-quantity').val());
            const originalLocation = row.find('.location-select').data('original');
            const newLocation = row.find('.location-select').val();

            if (originalQuantity !== newQuantity || originalLocation != newLocation) {
                hasChanges = true;
                return false; 
            }
        });
    }

    $('button[type="submit"]').prop('disabled', !hasChanges);
}
    function updateProductSelectionStatus(productId) {
        const option = $(`#newProductId option[value="${productId}"]`);
        if (productSelectionCount[productId] >= availableLocations.length) {
            option.prop('disabled', true);
        } else {
            option.prop('disabled', false);
        }
    }

    function validateBeforeSubmit() {
        let isValid = true;
        const errorMessages = [];

        const receiveDate = $('#receiveDate').val();
        if (!receiveDate) {
            errorMessages.push('กรุณาระบุวันที่รับสินค้า');
            isValid = false;
        }

        if ($('#receiveItemsTable tbody tr').length === 0) {
            errorMessages.push('กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ');
            isValid = false;
        }

        $('#receiveItemsTable tbody tr').each(function() {
            const quantity = parseFloat($(this).find('.new-quantity').val());
            if (isNaN(quantity) || quantity <= 0) {
                errorMessages.push('จำนวนสินค้าต้องมากกว่า 0');
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            Swal.fire({
                title: 'ข้อมูลไม่ถูกต้อง',
                html: errorMessages.join('<br>'),
                icon: 'error'
            });
        }

        return isValid;
    }

    $('#editReceiveForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        if (validateBeforeSubmit()) {
            submitForm();
        }
    });

    function submitForm() {
        const formData = {
            receiveId: $('#receiveId').val(),
            receiveDate: $('#receiveDate').val(),
            receiveType: $('#receiveType').val(),
            items: []
        };

        $('#receiveItemsTable tbody tr').each(function() {
            const row = $(this);
            formData.items.push({
                productId: row.attr('data-product-id'),
                locationId: row.find('.location-select').val(),
                originalLocationId: row.attr('data-original-location-id'),
                originalQuantity: parseFloat(row.find('td:eq(3)').text()),
                newQuantity: parseFloat(row.find('.new-quantity').val()),
                unit: row.find('td:eq(5)').text()
            });
        });

        $.ajax({
            url: '../system/update_receive.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'สำเร็จ',
                        text: 'บันทึกการแก้ไขเรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        window.location.href = 'receive_history.php';
                    });
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถบันทึกการแก้ไขได้', 'error');
            }
        });
    }
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
            Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกวันที่ไม่เกินวันปัจจุบัน', 'error');
        }
        updateFormStatus();
    });

    $('button.btn-secondary').on('click', function() {
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

    function updateTotalQuantity() {
        let totalQuantity = 0;
        $('#receiveItemsTable tbody tr').each(function() {
            const quantity = parseFloat($(this).find('.new-quantity').val()) || 0;
            totalQuantity += quantity;
        });
        $('#totalQuantity').text(totalQuantity.toFixed(2));
    }

    $('#receiveItemsTable').on('input', '.new-quantity', function() {
        updateTotalQuantity();
        updateFormStatus();
    });

    updateTotalQuantity();
});
</script>
</body>
</html>