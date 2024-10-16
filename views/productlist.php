<?php
require_once '../config/permission.php';
requirePermission(['manage_products']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายการสินค้า</title>
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
                <div class="page-title">
                    <h4><i class="fas fa-barcode"></i> รายการสินค้า</h4>
                    <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url();?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">จัดการสินค้าของคุณ</li>
               </ul>
                </div>
                <div class="page-btn">
                    <a href="addproduct" class="btn btn-added"><img src="../assets/img/icons/plus.svg" alt="img" class="me-1">เพิ่มสินค้าใหม่</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                            <ul>
                                <li>
                                    <button id="deleteSelected" class="btn btn-danger"
                                        style="display: flex; align-items: center; padding: 6px 12px;">
                                        <img src="../assets/img/icons/delete.svg" alt="img"
                                            style="width: 20px; height: 20px; margin-right: 10px; filter: brightness(0) invert(1);">
                                        <span>ลบรายการที่เลือก</span>
                                    </button>
                                </li>
                            </ul>
                            <div class="wordset">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item">
                                            <a href="<?php echo base_url()?>/report/products_PDF.php" id="pdfButton" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
                                                <img src="../assets/img/icons/pdf.svg" alt="PDF">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table datanew" id="productTable">
                            <thead>
                                <tr>
                                    <th>
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>รูป</th>
                                    <th>รหัสสินค้า</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>หมวดหมู่</th>
                                    <th>ประเภท</th>
                                    <th>ขนาด</th>
                                    <th>หน่วย</th>
                                    <th>สร้างโดย</th>
                                    <th>การกระทำ</th>
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
    <script>
        $(document).ready(function () {
            // ตรวจสอบว่าตารางมี DataTable อยู่แล้วหรือไม่
            if ($.fn.DataTable.isDataTable('#productTable')) {
                // หากมี DataTable อยู่แล้ว ให้ทำลายมันก่อน
                $('#productTable').DataTable().destroy();
            }

            // สร้าง DataTable ใหม่
            var productTable = $('#productTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../api/get_products.php",
                    "type": "POST"
                },
                "columns": [
                    {
                        "data": "checkbox",
                        "render": function (data, type, row) {
                            return '<label class="checkboxs"><input type="checkbox" class="product-checkbox" value="' + row.product_id + '"><span class="checkmarks"></span></label>';
                        },
                        "orderable": false
                    },
                    {
                        "data": "image",
                        "render": function (data, type, row) {
                            return '<img src="' + data + '" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">';
                        }
                    },
                    { "data": "product_id" },
                    {
                        "data": null,
                        "render": function (data, type, row) {
                            return row.name_th + ' (' + row.name_en + ')';
                        }
                    },
                    { "data": "product_type_name" },
                    { "data": "product_category_name" },
                    { "data": "size" },
                    { "data": "unit" },
                    { "data": "created_by" },
                    { "data": "actions" }
                    
                ],
                "drawCallback": function (settings) {
                    updateSelectAllCheckbox();
                    updateDeleteButtonState();
                }
                ,
            "language": {
                "emptyTable": "ไม่พบข้อมูลสินค้า",
                "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "lengthMenu": "แสดง _MENU_ รายการ",
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

            // จัดการกับการคลิกที่ checkbox ทั้งหมด
            $('#select-all').on('click', function () {
                $('.product-checkbox').prop('checked', this.checked);
                updateDeleteButtonState();
            });

            // จัดการกับการคลิกที่ checkbox แต่ละรายการ
            $('#productTable').on('change', '.product-checkbox', function () {
                updateSelectAllCheckbox();
                updateDeleteButtonState();
            });

            // อัพเดทสถานะของ checkbox ทั้งหมด
            function updateSelectAllCheckbox() {
                var allChecked = $('.product-checkbox:checked').length === $('.product-checkbox').length && $('.product-checkbox').length > 0;
                $('#select-all').prop('checked', allChecked);
            }

            // อัพเดทสถานะของปุ่มลบ
            function updateDeleteButtonState() {
                var anyChecked = $('.product-checkbox:checked').length > 0;
                $('#deleteSelected').prop('disabled', !anyChecked);
            }

            // สำหรับการลบหลายรายการ
            $('#deleteSelected').on('click', function () {
                var selectedIds = $('.product-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกรายการที่ต้องการลบ', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการลบ',
                    text: "คุณแน่ใจหรือไม่ที่จะลบสินค้าที่เลือก?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDelete(selectedIds);
                    }
                });
            });

            function performDelete(ids) {
                $.ajax({
                    url: '../system/delete_product.php',
                    type: 'POST',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function (response) {
                        handleDeleteResponse(response);
                    },
                    error: function (xhr, status, error) {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            'เกิดข้อผิดพลาดในการลบสินค้า: ' + error,
                            'error'
                        );
                    }
                });
            }

            function handleDeleteResponse(response) {
                if (response.status === 'error' && response.relatedRecords) {
                    let message = 'ไม่สามารถลบสินค้าบางรายการได้เนื่องจากมีข้อมูลที่เกี่ยวข้อง:\n\n';
                    for (let table in response.relatedRecords) {
                        message += `${table}: ${response.relatedRecords[table]} รายการ\n`;
                    }
                    Swal.fire({
                        title: 'ไม่สามารถลบสินค้าบางรายการ',
                        text: message,
                        icon: 'warning',
                        confirmButtonText: 'เข้าใจแล้ว'
                    });
                } else if (response.status === 'success') {
                    Swal.fire(
                        'ลบสำเร็จ!',
                        response.message,
                        'success'
                    ).then(() => {
                        productTable.ajax.reload(null, false);
                    });
                } else {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        response.message,
                        'error'
                    );
                }
            }
        });

        // สำหรับการลบสินค้าเดียว
        function deleteProduct(productId) {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: "คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../system/delete_product.php',
                        type: 'POST',
                        data: { id: productId },
                        dataType: 'json',
                        success: function (response) {
                            handleDeleteResponse(response);
                        },
                        error: function (xhr, status, error) {
                            Swal.fire(
                                'เกิดข้อผิดพลาด!',
                                'เกิดข้อผิดพลาดในการลบสินค้า: ' + error,
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function handleDeleteResponse(response) {
            if (response.status === 'error' && response.relatedRecords) {
                let message = 'ไม่สามารถลบสินค้าได้เนื่องจากมีข้อมูลที่เกี่ยวข้อง:\n\n';
                for (let table in response.relatedRecords) {
                    message += `${table}: ${response.relatedRecords[table]} รายการ\n`;
                }
                Swal.fire({
                    title: 'ไม่สามารถลบสินค้า',
                    text: message,
                    icon: 'warning',
                    confirmButtonText: 'เข้าใจแล้ว'
                });
            } else if (response.status === 'success') {
                Swal.fire(
                    'ลบสำเร็จ!',
                    response.message,
                    'success'
                ).then(() => {
                    $('#productTable').DataTable().ajax.reload(null, false);
                });
            } else {
                Swal.fire(
                    'เกิดข้อผิดพลาด!',
                    response.message,
                    'error'
                );
            }
        }
    </script>
</body>

</html>