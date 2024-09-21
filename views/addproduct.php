<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>เพิ่มสินค้าใหม่</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
</head>
<style>
    .form-group .btn-success.btn-sm {
        color: #28a745;
        background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-left: 5px;
            font-size: 1rem;

            align-items: center;
            justify-content: center;
    }
</style>

<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <form id="addProductForm" enctype="multipart/form-data">
        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Product Add</h4>
                        <h6>Create new product</h6>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-box-open"></i>
                                เพิ่มสินค้าใหม่
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_id"><i class="fas fa-barcode"></i> รหัสสินค้า</label>
                                        <input type="text" class="form-control" id="product_id" name="product_id"  required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name_th"><i class="fas fa-font"></i> ชื่อสินค้า (ภาษาไทย)</label>
                                        <input type="text" class="form-control" id="name_th" name="name_th" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name_en"><i class="fas fa-language"></i> ชื่อสินค้า
                                            (ภาษาอังกฤษ)</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="size"><i class="fas fa-ruler"></i> ขนาด</label>
                                        <input type="text" class="form-control" id="size" name="size">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="type_id" class="mb-0 mr-2"><i class="fas fa-folder"></i>
                                                หมวดหมู่หลัก</label>
                                            <button class="btn btn-success btn-sm rounded-circle" type="button"
                                                id="addMainCategory">
                                                <i class="fas fa-plus-circle "></i>
                                            </button>
                                        </div>
                                        <select class="form-control" id="type_id" name="type_id" required>
                                            <option value="">เลือกหมวดหมู่หลัก</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="category_id" class="mb-0 mr-2">  <i class="fas fa-folder-open"></i> ประเภทย่อย</label>
                                        <button class="btn btn-success btn-sm rounded-circle" type="button" id="addSubCategory"
                                                style="display: none;">
                                                <i class="fas fa-plus-circle "></i>
                                            </button>
                                    </div>
                                        <select class="form-control" id="category_id" name="category_id" required>
                                            <option value="">เลือกประเภทย่อย</option>
                                        </select>
                                        <div class="input-group-append">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit"><i class="fas fa-balance-scale"></i> หน่วย</label>
                                        <input type="text" class="form-control" id="unit" name="unit" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="low_level"><i class="fas fa-bell"></i>
                                            ระดับการแจ้งเตือน</label>
                                        <input type="number" class="form-control" id="low_level" name="low_level"
                                            step="1" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>รูปภาพสินค้า</label>
                                    <div class="image-upload">
                                        <input type="file" name="img" id="img" accept="image/*"
                                            class="custom-file-input">
                                        <div class="image-uploads" id="image-preview">
                                            <img src="<?php echo base_url() ?>/assets/img/icons/upload.svg" alt="img"
                                                id="default-img">
                                            <h4>คลิกหรือวางไฟล์เพื่ออัปโหลด</h4>
                                            <p id="file-name" style="text-align: center; margin-top: -5px;"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i>
                                    บันทึก</button>
                                <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="history.back()">
                                    <i class="fas fa-times"></i> ยกเลิก
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
    <div class="modal fade" id="addMainCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มหมวดหมู่หลัก</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addMainCategoryForm">
                        <div class="form-group">
                            <label for="mainCategoryName">ชื่อหมวดหมู่หลัก</label>
                            <input type="text" class="form-control" id="mainCategoryName" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="saveMainCategory">บันทึก</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal สำหรับเพิ่มประเภทย่อย -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มประเภทย่อย</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addSubCategoryForm">
                        <input type="hidden" id="subCategoryMainId" name="product_category_id">
                        <div class="form-group">
                            <label for="subCategoryName">ชื่อประเภทย่อย</label>
                            <input type="text" class="form-control" id="subCategoryName" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="saveSubCategory">บันทึก</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('img').addEventListener('change', function (e) {
            var fileName = e.target.files[0].name;
            document.getElementById('file-name').textContent = 'รูปที่อัปโหลด: ' + fileName;
        });

        $(document).ready(function () {
            // Load main categories
            $.ajax({
                url: '../api/get_types.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var options = '<option value="">เลือกหมวดหมู่หลัก</option>';
                    $.each(data, function (index, type) {
                        options += '<option value="' + type.type_id + '">' + type.name + '</option>';
                    });
                    $('#type_id').html(options);
                }
            });

            // Load subcategories when main category is selected
            $('#type_id').change(function () {
                var typeId = $(this).val();
                if ($(this).val()) {
                    $('#addSubCategory').show();
                } else {
                    $('#addSubCategory').hide();
                }
                if (typeId) {
                    $.ajax({
                        url: '../api/get_categories.php',
                        type: 'GET',
                        data: { type_id: typeId },
                        dataType: 'json',
                        success: function (data) {
                            var options = '<option value="">เลือกหมวดหมู่ย่อย</option>';
                            $.each(data, function (index, category) {
                                options += '<option value="' + category.category_id + '">' + category.name + '</option>';
                            });
                            $('#category_id').html(options);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    $('#category_id').html('<option value="">เลือกหมวดหมู่ย่อย</option>');
                }
            });

            // Display selected file name
            $('.custom-file-input').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Submit form
            $("#addProductForm").submit(function (e) {
                e.preventDefault();
                var productId = $("#product_id").val();

                if (!validateProductId(productId)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'รหัสสินค้าไม่ถูกต้อง',
                        text: 'รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น'
                    });
                    return;
                }

                checkProductIdExists(productId).then(function (response) {
                    if (response.exists) {
                        Swal.fire({
                            icon: 'error',
                            title: 'รหัสสินค้าซ้ำ',
                            text: 'รหัสสินค้านี้มีอยู่ในระบบแล้ว'
                        });
                    } else {
                        var formData = new FormData(e.target);

                        $.ajax({
                            type: 'POST',
                            url: '../system/add_product.php',
                            data: formData,
                            contentType: false,
                            processData: false,
                            beforeSend: function () {
                                Swal.fire({
                                    title: 'กำลังบันทึก...',
                                    text: 'กรุณารอสักครู่',
                                    allowOutsideClick: false,
                                    onBeforeOpen: () => {
                                        Swal.showLoading();
                                    },
                                });
                            },
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'สำเร็จ',
                                        text: response.message
                                    }).then(function () {
                                        window.location.href = 'productlist';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'เกิดข้อผิดพลาด',
                                        text: response.message
                                    });
                                }
                            },
                        });
                    }
                });
            });
            function validateProductId(input) {
                var regex = /^[a-zA-Z0-9]+$/;
                return regex.test(input);
            }
            function checkProductIdExists(productId) {
                return $.ajax({
                    url: '../system/add_product.php',
                    type: 'POST',
                    data: {
                        check_product_id: 1,
                        product_id: productId
                    },
                    dataType: 'json'
                });
            }
            $("#product_id").on('input', function () {
                var input = $(this);
                var productId = input.val();

                if (!validateProductId(productId)) {
                    input.addClass('is-invalid');
                    input.removeClass('is-valid');
                    if (!input.next('.invalid-feedback').length) {
                        input.after('<div class="invalid-feedback">รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น</div>');
                    }
                } else {
                    input.removeClass('is-invalid');
                    input.next('.invalid-feedback').remove();

                    if (productId !== '') {
                        checkProductIdExists(productId).then(function (response) {
                            if (response.exists) {
                                input.addClass('is-invalid');
                                input.removeClass('is-valid');
                                if (!input.next('.invalid-feedback').length) {
                                    input.after('<div class="invalid-feedback">รหัสสินค้านี้มีอยู่ในระบบแล้ว</div>');
                                }
                            } else {
                                input.removeClass('is-invalid');
                                // input.addClass('is-valid');
                                input.next('.invalid-feedback').remove();
                            }
                        });
                    } else {
                        input.removeClass('is-valid');
                    }
                }
            });

            $('#addMainCategory').click(function () {
                $('#addMainCategoryModal').modal('show');
            });

            // บันทึกหมวดหมู่หลักใหม่
            $('#saveMainCategory').click(function () {
                var mainCategoryName = $('#mainCategoryName').val();
                if (!mainCategoryName) {
                    Swal.fire('ข้อผิดพลาด', 'กรุณากรอกชื่อหมวดหมู่หลัก', 'error');
                    return;
                }

                $.ajax({
                    url: '../system/add_type.php',
                    type: 'POST',
                    data: { name: mainCategoryName },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire('สำเร็จ', 'เพิ่มหมวดหมู่หลักเรียบร้อยแล้ว', 'success');
                            $('#addMainCategoryModal').modal('hide');
                            // โหลดหมวดหมู่หลักใหม่
                            $.ajax({
                                url: '../api/get_types.php',
                                type: 'GET',
                                dataType: 'json',
                                success: function (data) {
                                    var options = '<option value="">เลือกหมวดหมู่หลัก</option>';
                                    $.each(data, function (index, type) {
                                        options += '<option value="' + type.type_id + '">' + type.name + '</option>';
                                    });
                                    $('#type_id').html(options);
                                }
                            });
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถเพิ่มหมวดหมู่หลักได้', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            // เปิด modal เพิ่มประเภทย่อย
            $('#addSubCategory').click(function () {
                var selectedMainCategory = $('#type_id').val();
                if (!selectedMainCategory) {
                    Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกหมวดหมู่หลักก่อน', 'error');
                    return;
                }
                $('#subCategoryMainId').val(selectedMainCategory);
                $('#addSubCategoryModal').modal('show');
            });

            // บันทึกประเภทย่อยใหม่
            $('#saveSubCategory').click(function () {
                var subCategoryName = $('#subCategoryName').val();
                var mainCategoryId = $('#subCategoryMainId').val();

                if (!subCategoryName) {
                    Swal.fire('ข้อผิดพลาด', 'กรุณากรอกชื่อประเภทย่อย', 'error');
                    return;
                }

                $.ajax({
                    url: '../system/add_category.php',
                    type: 'POST',
                    data: {
                        name: subCategoryName,
                        product_category_id: mainCategoryId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire('สำเร็จ', 'เพิ่มประเภทย่อยเรียบร้อยแล้ว', 'success');
                            $('#addSubCategoryModal').modal('hide');
                            // โหลดประเภทย่อยใหม่
                            $.ajax({
                                url: '../api/get_categories.php',
                                type: 'GET',
                                data: { type_id: mainCategoryId },
                                dataType: 'json',
                                success: function (data) {
                                    var options = '<option value="">เลือกประเภทย่อย</option>';
                                    $.each(data, function (index, category) {
                                        options += '<option value="' + category.category_id + '">' + category.name + '</option>';
                                    });
                                    $('#category_id').html(options);
                                }
                            });
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถเพิ่มประเภทย่อยได้', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            $('#addMainCategoryModal .btn-secondary').click(function () {
                $('#addMainCategoryModal').modal('hide');
            });
            $('#addSubCategoryModal .btn-secondary').click(function () {
                $('#addSubCategoryModal').modal('hide');
            });

            $('.modal .close').click(function() {
        $(this).closest('.modal').modal('hide');
    });
        });
    </script>

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
</body>

</html>