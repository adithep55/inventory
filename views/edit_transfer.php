<?php
require_once '../config/permission.php';
requirePermission(['manage_transfers']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขการโอนย้ายสินค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
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
                        <h3 class="page-title"><i class="fas fa-edit"></i> แก้ไขการโอนย้ายสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php base_url();?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="<?php base_url();?>/views/transfer_history">ประวัติการโอนย้าย</a></li>
                            <li class="breadcrumb-item active">แก้ไขการโอนย้ายสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="editTransferForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>เลขที่เอกสารโอนย้าย</label>
                                            <input type="text" class="form-control" id="billNumber" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>วันที่โอนย้าย</label>
                                            <input type="date" class="form-control" id="transferDate" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h4>รายการสินค้าที่โอนย้าย</h4>
                                        <table class="table table-bordered" id="transferItemsTable">
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
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h4>เพิ่มสินค้า</h4>
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

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                                        <a href="transfer_history.php" class="btn btn-secondary">กลับ</a>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const transferId = urlParams.get('id');
    let formData;

    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const maxDate = tomorrow.toISOString().split('T')[0];
    $('#transferDate').attr('max', maxDate);

    if (transferId) {
        loadTransferDetails(transferId);
    } else {
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: 'ไม่พบรหัสการโอนย้าย',
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'transfer_history.php';
        });
    }

    function loadTransferDetails(transferId) {
        $.ajax({
            url: '../api/get_transfer_details.php',
            type: 'GET',
            data: { id: transferId },
            dataType: 'json',
            success: function (response) {
                if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อผิดพลาด',
                        text: response.error,
                        confirmButtonText: 'ตกลง'
                    });
                    return;
                }
                console.log('Transfer details:', response);
                displayTransferDetails(response);
            },
            error: function (xhr, status, error) {
                console.error('Error loading transfer details:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'ไม่สามารถโหลดข้อมูลการโอนย้ายได้',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    }

    function displayTransferDetails(data) {
        $('#billNumber').val(data.bill_number);
        
        let dateParts = data.transfer_date.split("-");
        let year = parseInt(dateParts[2]) - 543;
        let formattedDate = `${year}-${dateParts[1].padStart(2, '0')}-${dateParts[0].padStart(2, '0')}`;
        
        $('#transferDate').val(formattedDate);
        
        console.log('Displaying transfer details:', data);
        const itemsTable = $('#transferItemsTable tbody');
        itemsTable.empty();
        
        data.items.forEach(function (item) {
            addProductToTransfer(
                item.product_id,
                item.product_name_th,
                item.product_name_en,
                item.from_location,
                item.to_location,
                item.quantity,
                item.unit
            );
        });
        
        console.log('Added all product items');
    }

    function addProductToTransfer(productId, productNameTh, productNameEn, fromLocation, toLocation, quantity, unit) {
        $.ajax({
            url: '../api/get_product_locations.php',
            type: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    console.log('Product locations:', response);
                    let totalQuantity = response.locations.reduce((sum, loc) => sum + parseFloat(loc.quantity), 0);
                    let fromLocationOptions = response.locations.map(loc => {
                        let isSelected = loc.location === fromLocation;
                        return `<option value="${loc.location_id}" 
                            ${isSelected ? 'selected' : ''} 
                            data-quantity="${loc.quantity}">
                            ${loc.location || 'ไม่ระบุ'} (${loc.quantity} ${unit})
                        </option>`;
                    }).join('');

                    let toLocationOptions = response.all_locations.map(loc =>
                        `<option value="${loc.location_id}" 
                            ${loc.location === toLocation ? 'selected' : ''}>
                            ${loc.location || 'ไม่ระบุ'}
                        </option>`
                    ).join('');

                    let newRow = `
                        <tr data-product-id="${productId}">
                            <td>${productNameTh || productNameEn || 'ไม่ระบุ'}</td>
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
                            <td><input type="number" class="form-control quantity" min="1" step="1" value="${quantity}" required></td>
                            <td>${unit || 'ไม่ระบุ'}</td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                            <input type="hidden" name="product_ids[]" value="${productId}">
                            <input type="hidden" class="original-quantity" value="${quantity}">
                            <input type="hidden" class="total-available-quantity" value="${totalQuantity}">
                        </tr>
                    `;
                    $('#transferItemsTable tbody').append(newRow);
                    
                    updateQuantityLimit(productId);
                    
                    console.log('Added product row:', { productId, productNameTh, productNameEn, fromLocation, toLocation, quantity, unit, totalQuantity });
                } else {
                    console.error('Error fetching product locations:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching product locations:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    function updateQuantityLimit(productId) {
        let row = $(`#transferItemsTable tbody tr[data-product-id="${productId}"]`);
        let quantityInput = row.find('.quantity');
        let fromLocationSelect = row.find('.from-location');
        let totalAvailableQuantity = parseFloat(row.find('.total-available-quantity').val());
        let originalQuantity = parseFloat(row.find('.original-quantity').val());
        
        let currentFromLocationQuantity = parseFloat(fromLocationSelect.find('option:selected').data('quantity')) || 0;
        
        console.log('Updated quantity info:', { 
            productId, 
            currentFromLocationQuantity, 
            originalQuantity, 
            totalAvailableQuantity 
        });

        let maxTransferQuantity = Math.max(originalQuantity, totalAvailableQuantity);
        quantityInput.attr('max', maxTransferQuantity);

        quantityInput.off('input').on('input', function() {
            let enteredQuantity = parseFloat($(this).val());
            if (enteredQuantity > maxTransferQuantity) {
                Swal.fire({
                    icon: 'warning',
                    title: 'คำเตือน',
                    text: `จำนวนที่โอนย้าย (${enteredQuantity}) มากกว่าจำนวนสูงสุดที่สามารถโอนได้ (${maxTransferQuantity})`,
                    confirmButtonText: 'ตกลง'
                });
                $(this).val(maxTransferQuantity);
            } else if (enteredQuantity > currentFromLocationQuantity) {
                console.log('จำนวนที่โอนย้ายมากกว่าจำนวนในคลังปัจจุบัน แต่ไม่เกินจำนวนเดิม');
            }
        });
    }

    $('#editTransferForm').submit(function (e) {
        e.preventDefault();
        let transferDate = new Date($('#transferDate').val());
        let maxAllowedDate = new Date(maxDate);

        if (transferDate > maxAllowedDate) {
            Swal.fire({
                icon: 'error',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'วันที่โอนย้ายต้องไม่เป็นวันในอนาคต',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        formData = {
            transfer_id: transferId,
            transfer_date: $('#transferDate').val(),
            products: []
        };

        let isValid = true;

        $('#transferItemsTable tbody tr').each(function () {
            let row = $(this);
            let productId = row.find('input[name="product_ids[]"]').val();
            let fromLocation = row.find('.from-location').val();
            let toLocation = row.find('.to-location').val();
            let quantity = parseFloat(row.find('.quantity').val());
            let unit = row.find('td:eq(4)').text();
            let originalQuantity = parseFloat(row.find('.original-quantity').val());
            let totalAvailableQuantity = parseFloat(row.find('.total-available-quantity').val());
            let maxTransferQuantity = Math.max(originalQuantity, totalAvailableQuantity);

            if (fromLocation === toLocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'คลังสินค้าไม่ถูกต้อง',
                    text: 'คลังสินค้าต้นทางและปลายทางต้องไม่เป็นคลังเดียวกัน',
                    confirmButtonText: 'ตกลง'
                });
                isValid = false;
                return false;
            }

            if (quantity > maxTransferQuantity) {
                Swal.fire({
                    icon: 'error',
                    title: 'จำนวนไม่ถูกต้อง',
                    text: `จำนวนที่โอนย้ายของสินค้า ${productId} (${quantity}) มากกว่าจำนวนสูงสุดที่สามารถโอนได้ (${maxTransferQuantity})`,
                    confirmButtonText: 'ตกลง'
                });
                isValid = false;
                return false;
            }

            addProductToFormData(productId, fromLocation, toLocation, quantity, unit, originalQuantity);
        });

        if (!isValid) {
            return;
        }

        if (formData.products.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'ข้อมูลไม่ครบถ้วน',
                text: 'กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการ',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        submitTransferData();
    });

 function addProductToFormData(productId, fromLocation, toLocation, quantity, unit, originalQuantity) {
        console.log('Adding product to formData:', { productId, fromLocation, toLocation, quantity, unit, originalQuantity });
        formData.products.push({
            product_id: productId,
            from_location_id: fromLocation,
            to_location_id: toLocation,
            quantity: quantity,
            unit: unit,
            original_quantity: originalQuantity
        });
    }

    function submitTransferData() {
        console.log('Submitting transfer data:', formData);

        $.ajax({
            url: '../system/update_transfer.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                console.log('Server response:', response);
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'บันทึกการแก้ไขการโอนย้ายสินค้าเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'transfer_history.php';
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
            error: function (xhr, status, error) {
                console.error("XHR Status:", status);
                console.error("Error:", error);
                console.error("Response Text:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถบันทึกการแก้ไขการโอนย้ายสินค้าได้ โปรดลองอีกครั้งหรือติดต่อผู้ดูแลระบบ',
                    confirmButtonText: 'ตกลง'});
            }
        });
    }

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
                "render": function (data, type, row) {
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

    $('#productTable').on('change', '.product-select', function () {
        var row = $(this).closest('tr');
        var data = productTable.row(row).data();
        if (this.checked) {
            addProductToTransfer(data.product_id, data.name_th, data.name_en, '', '', 1, data.unit);
        } else {
            $('#transferItemsTable tbody').find(`input[value="${data.product_id}"]`).closest('tr').remove();
        }
    });

    $('#transferItemsTable').on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });

    $('#transferItemsTable').on('change', '.from-location', function () {
        let row = $(this).closest('tr');
        let productId = row.data('product-id');
        updateQuantityLimit(productId);
    });

    $('#transferItemsTable').on('change', '.to-location', function () {
        let row = $(this).closest('tr');
        let fromLocation = row.find('.from-location');
        let toLocation = $(this);

        if (fromLocation.val() === toLocation.val()) {
            Swal.fire({
                icon: 'error',
                title: 'คลังสินค้าไม่ถูกต้อง',
                text: 'คลังสินค้าต้นทางและปลายทางต้องไม่เป็นคลังเดียวกัน',
                confirmButtonText: 'ตกลง'
            });
            toLocation.val('');
        }
    });

    $('#transferDate').on('change', function () {
        let selectedDate = new Date($(this).val());
        let maxAllowedDate = new Date(maxDate);

        if (selectedDate > maxAllowedDate) {
            $(this).val(maxDate);
            Swal.fire({
                icon: 'error',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'ไม่สามารถเลือกวันที่ในอนาคตได้',
                confirmButtonText: 'ตกลง'
            });
        }
    });
});
    </script>
</body>

</html>