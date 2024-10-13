<?php
require_once '../config/permission.php';
requirePermission(['manage_products']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขข้อมูลสินค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
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
                        <h3 class="page-title"><i class="fas fa-edit"></i> แก้ไขข้อมูลสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url()?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url()?>/views/productlist">รายการสินค้า</a></li>
                            <li class="breadcrumb-item active">แก้ไขข้อมูลสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="editProductForm">
                                <input type="hidden" id="oldProductId" name="oldProductId">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>รหัสสินค้า</label>
                                            <input type="text" class="form-control" id="productCode" name="product_id" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ชื่อสินค้า (ไทย)</label>
                                            <input type="text" class="form-control" id="nameTh" name="nameTh" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ชื่อสินค้า (อังกฤษ)</label>
                                            <input type="text" class="form-control" id="nameEn" name="nameEn">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type_id">หมวดหมู่หลัก</label>
                                            <select class="form-control" id="type_id" name="type_id" required>
                                                <option value="">เลือกหมวดหมู่หลัก</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id">ประเภทย่อย</label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <option value="">เลือกประเภทย่อย</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ขนาด</label>
                                            <input type="text" class="form-control" id="size" name="size">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>หน่วย</label>
                                            <input type="text" class="form-control" id="unit" name="unit" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ระดับการแจ้งเตือน</label>
                                            <input type="number" class="form-control" id="lowLevel" name="lowLevel" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>รูปภาพสินค้า</label>
                                            <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*">
                                        </div>
                                    </div>
                                </div>
    
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                            <a href="javascript:history.back()" class="btn btn-secondary">กลับ</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('.select').select2();

        var productId = new URLSearchParams(window.location.search).get('id');
        if (productId) {
            loadProductTypes();
            loadProductData(productId);
        }

        function validateProductId(input) {
            var regex = /^[a-zA-Z0-9]+$/;
            return regex.test(input);
        }

        function checkProductIdExists(productId, oldProductId) {
            return $.ajax({
                url: '../system/update_product.php',
                type: 'POST',
                data: {
                    check_product_id: 1,
                    product_id: productId,
                    old_product_id: oldProductId
                },
                dataType: 'json'
            });
        }

        $("#productCode").on('input', function () {
            var input = $(this);
            var productId = input.val();
            var oldProductId = $('#oldProductId').val();

            if (!validateProductId(productId)) {
                input.addClass('is-invalid');
                input.removeClass('is-valid');
                if (!input.next('.invalid-feedback').length) {
                    input.after('<div class="invalid-feedback">รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น</div>');
                }
            } else {
                input.removeClass('is-invalid');
                input.next('.invalid-feedback').remove();

                if (productId !== '' && productId !== oldProductId) {
                    checkProductIdExists(productId, oldProductId).then(function (response) {
                        if (response.data.exists) {
                            input.addClass('is-invalid');
                            input.removeClass('is-valid');
                            if (!input.next('.invalid-feedback').length) {
                                input.after('<div class="invalid-feedback">รหัสสินค้านี้มีอยู่ในระบบแล้ว</div>');
                            }
                        } else {
                            input.removeClass('is-invalid');
                            input.addClass('is-valid');
                            input.next('.invalid-feedback').remove();
                        }
                    });
                } else {
                    input.removeClass('is-invalid');
                    input.removeClass('is-valid');
                }
            }
        });

        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            var productId = $("#productCode").val();
            var oldProductId = $('#oldProductId').val();

            if (!validateProductId(productId)) {
                Swal.fire({
                    icon: 'error',
                    title: 'รหัสสินค้าไม่ถูกต้อง',
                    text: 'รหัสสินค้าต้องประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น'
                });
                return;
            }

            if (productId !== oldProductId) {
                checkProductIdExists(productId, oldProductId).then(function (response) {
                    if (response.data.exists) {
                        Swal.fire({
                            icon: 'error',
                            title: 'รหัสสินค้าซ้ำ',
                            text: 'รหัสสินค้านี้มีอยู่ในระบบแล้ว'
                        });
                    } else {
                        updateProduct();
                    }
                });
            } else {
                updateProduct();
            }
        });

        function loadProductData(productId) {
    $.ajax({
        url: '../api/get_products.php',
        type: 'POST',
        data: { 
            draw: 1,
            start: 0,
            length: 1,
            search: { value: productId }
        },
        dataType: 'json',
        success: function(response) {
            if (response.data && response.data.length > 0) {
                var product = response.data[0];
                console.log("Product data:", product);
                $('#oldProductId').val(product.product_id);
                $('#productCode').val(product.product_id);
                $('#nameTh').val(product.name_th);
                $('#nameEn').val(product.name_en);
                $('#size').val(product.size);
                $('#unit').val(product.unit);
                $('#lowLevel').val(product.low_level);

                loadProductTypes(function() {
                    var $typeOption = $(`#type_id option:contains("${product.product_type_name}")`);
                    if ($typeOption.length) {
                        $('#type_id').val($typeOption.val()).trigger('change');
                        console.log("Setting type_id to:", $typeOption.val());
                        
                    }
                    
                    // Save product_category_name for later use
                    $('#category_id').data('saved-value', product.product_category_name);
                });

                if (product.image) {
                    $('#imagePreview').attr('src', product.image);
                }
            } else {
                Swal.fire('Error', 'Failed to load product data', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'An error occurred while loading product data', 'error');
        }
    });
}

function loadProductTypes(callback) {
    $.ajax({
        url: '../api/get_product_types.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && Array.isArray(response.data)) {
                var options = '<option value="">เลือกหมวดหมู่หลัก</option>';
                $.each(response.data, function(index, type) {
                    options += '<option value="' + type.type_id + '">' + type.name + '</option>';
                });
                $('#type_id').html(options);
                if (callback) callback();
            } else {
                console.error("Invalid product types data format");
                $('#type_id').html('<option value="">เลือกหมวดหมู่หลัก</option>');
                if (callback) callback();
            }
        },
        error: function() {
            console.error("Failed to load product types");
            $('#type_id').html('<option value="">เลือกหมวดหมู่หลัก</option>');
            if (callback) callback();
        }
    });
}

function loadProductCategories(typeId, callback) {
    $.ajax({
        url: '../api/get_categories.php',
        type: 'GET',
        data: { type_id: typeId },
        dataType: 'json',
        success: function(data) {
            console.log("Categories data:", data);
            var options = '<option value="">เลือกประเภทย่อย</option>';
            if (Array.isArray(data)) {
                $.each(data, function(index, category) {
                    options += '<option value="' + category.category_id + '">' + category.name + '</option>';
                });
            } else {
                console.error("Invalid categories data format");
            }
            $('#category_id').html(options);
            
            // Select the saved category
            var savedCategoryName = $('#category_id').data('saved-value');
            if (savedCategoryName) {
                var $option = $(`#category_id option:contains("${savedCategoryName}")`);
                if ($option.length) {
                    $('#category_id').val($option.val());
                    console.log("Setting category_id to:", $option.val());
                }
            }
            
            if (callback) callback();
        },
        error: function() {
            console.error("Failed to load product categories");
            $('#category_id').html('<option value="">เลือกประเภทย่อย</option>');
            if (callback) callback();
        }
    });
}

$('#type_id').on('change', function() {
    var selectedTypeId = $(this).val();
    console.log("Selected type_id:", selectedTypeId);
    if (selectedTypeId) {
        loadProductCategories(selectedTypeId);
    } else {
        $('#category_id').html('<option value="">เลือกประเภทย่อย</option>');
    }
});

function updateProduct() {
    var formData = new FormData($('#editProductForm')[0]);
    formData.append('oldProductId', $('#oldProductId').val());
    
    // ตรวจสอบว่ามีการเลือกรูปภาพใหม่หรือไม่
    var imageFile = $('#productImage')[0].files[0];
    if (imageFile) {
        formData.append('productImage', imageFile);
    }
    
    console.log("Form data:");
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    $.ajax({
        url: '../system/update_product.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('Success', 'อัปเดตข้อมูลสินค้าสำเร็จ', 'success')
                    .then(() => {
                        window.location.href = '<?php echo base_url()?>/views/productlist';
                    });
            } else {
                Swal.fire('Error', response.message || 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลสินค้า', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
        }
    });
}
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