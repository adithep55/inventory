<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขการโอนย้ายสินค้า</title>
    
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
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">แก้ไขการโอนย้ายสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="transfer_history.php">ประวัติการโอนย้าย</a></li>
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
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const transferId = urlParams.get('id');

    // แก้ไขการตั้งค่า max ของ input วันที่
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
                success: function(response) {
                    if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: response.error,
                            confirmButtonText: 'ตกลง'
                        });
                        return;
                    }
                    displayTransferDetails(response);
                },
                error: function() {
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
            $('#transferDate').val(data.transfer_date);

            const itemsTable = $('#transferItemsTable tbody');
            itemsTable.empty();

            data.items.forEach(function(item) {
                addProductToTransfer(item.product_id, item.product_name, item.from_location, item.to_location, item.quantity, item.unit);
            });
        }

        function addProductToTransfer(productId, productName, fromLocation, toLocation, quantity, unit) {
    $.ajax({
        url: '../api/get_product_locations.php',
        type: 'POST',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            let fromLocationOptions = response.all_locations.map(loc => 
                `<option value="${loc.location_id}" ${loc.location === fromLocation ? 'selected' : ''}>${loc.location} ${loc.location === fromLocation ? `(${quantity} ${unit})` : ''}</option>`
            ).join('');

            let toLocationOptions = response.all_locations.map(loc => 
                `<option value="${loc.location_id}" ${loc.location === toLocation ? 'selected' : ''}>${loc.location}</option>`
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
                <td><input type="number" class="form-control quantity" min="1" step="1" value="${quantity}" required></td>
                <td>${unit}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                <input type="hidden" name="product_ids[]" value="${productId}">
            </tr>
            `;
            $('#transferItemsTable tbody').append(newRow);
        },
        error: function() {
            alert('Error fetching product locations');
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

        $('#productTable').on('change', '.product-select', function() {
            var row = $(this).closest('tr');
            var data = productTable.row(row).data();
            if (this.checked) {
                addProductToTransfer(data.product_id, data.name_th, '', '', 1, data.unit);
            } else {
                $('#transferItemsTable tbody').find(`input[value="${data.product_id}"]`).closest('tr').remove();
            }
        });

        $('#transferItemsTable').on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#transferItemsTable').on('change', '.from-location', function() {
            let row = $(this).closest('tr');
            let quantityInput = row.find('.quantity');
            let maxQuantity = $(this).find('option:selected').data('quantity');
            quantityInput.attr('max', maxQuantity);
        });

        $('#transferItemsTable').on('change', '.from-location, .to-location, .quantity', function() {
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

            if (quantity < 1) {
                quantityInput.val(1);
            }

            if (quantity > maxQuantity) {
                Swal.fire({
                    icon: 'error',
                    title: 'จำนวนไม่ถูกต้อง',
                    text: `จำนวนที่โอนย้ายต้องไม่เกิน ${maxQuantity}`,
                    confirmButtonText: 'ตกลง'
                });
                quantityInput.val(maxQuantity);
            }
        });

        $('#editTransferForm').submit(function(e) {
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

        let formData = {
            transfer_id: transferId,
            transfer_date: $('#transferDate').val(),
            products: []
        };

$('#transferItemsTable tbody tr').each(function() {
    let productId = $(this).find('input[name="product_ids[]"]').val();
    let fromLocation = $(this).find('.from-location').val();
    let toLocation = $(this).find('.to-location').val();
    let quantity = $(this).find('.quantity').val();
    let unit = $(this).find('td:eq(4)').text();
    
    formData.products.push({
        product_id: productId,
        from_location_id: fromLocation,
        to_location_id: toLocation,
        quantity: quantity,
        unit: unit
    });
    $('#transferDate').on('change', function() {
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

            $.ajax({
                url: '../system/update_transfer.php',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
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
                error: function(xhr, status, error) {
                    console.error("XHR Status:", status);
                    console.error("Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถบันทึกการแก้ไขการโอนย้ายสินค้าได้ โปรดลองอีกครั้งหรือติดต่อผู้ดูแลระบบ',
                        confirmButtonText: 'ตกลง'
                    });
                }
            });
        });
        $('#transferDate').on('change', function() {
        let selectedDate = new Date($(this).val());
        let maxDate = new Date();
        maxDate.setHours(0, 0, 0, 0);
        maxDate.setDate(maxDate.getDate() + 1);

        if (selectedDate > maxDate) {
            $(this).val(today);
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