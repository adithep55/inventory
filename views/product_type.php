<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>จัดการหมวดหมู่และประเภทย่อย</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .category-container {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;

        }
        .main-category {
            background-color: #f8f9fa;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #FF9F43;
            border-radius: 1.5px;
            
        }
        .main-category h5 {
            margin: 0;
        }
        .sub-categories {
            padding: 10px;
        }
        .sub-category {
            margin-bottom: 5px;
            padding: 5px;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sub-category:last-child {
            margin-bottom: 0;
        }
        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin-left: 5px;
            font-size: 1rem;
        }
        .icon-button:hover {
            opacity: 0.7;
        }
        .icon-button.edit {
            color: #007bff;
        }
        .icon-button.delete {
            color: #dc3545;
        }
        .icon-button.add {
            color: #28a745;
        }
    </style>
</head>

<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">จัดการหมวดหมู่และประเภทย่อย</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="addMainCategoryForm" class="mb-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="mainCategoryName" name="name" placeholder="ชื่อหมวดหมู่หลัก" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-plus-circle"></i> เพิ่มหมวดหมู่หลัก
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div id="categoryList">
                                <!-- หมวดหมู่และประเภทย่อยจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal สำหรับแก้ไขหมวดหมู่หลัก -->
    <div class="modal fade" id="editMainCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขหมวดหมู่หลัก</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editMainCategoryForm">
                        <input type="hidden" id="editMainCategoryId" name="id">
                        <div class="form-group">
                            <label for="editMainCategoryName">ชื่อหมวดหมู่หลัก</label>
                            <input type="text" class="form-control" id="editMainCategoryName" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="saveMainCategoryEdit">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับเพิ่มหรือแก้ไขประเภทย่อย -->
    <div class="modal fade" id="subCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subCategoryModalTitle">เพิ่ม/แก้ไขประเภทย่อย</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="subCategoryForm">
                        <input type="hidden" id="subCategoryId" name="id">
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
        $(document).ready(function() {
            loadCategories();

            $('#addMainCategoryForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                console.log('Sending data:', formData);
                $.ajax({
                    url: '../system/add_type.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.status === 'success') {
                            Swal.fire('สำเร็จ', 'เพิ่มหมวดหมู่หลักเรียบร้อยแล้ว', 'success');
                            $('#mainCategoryName').val('');
                            loadCategories();
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถเพิ่มหมวดหมู่หลักได้', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.log('Response Text:', xhr.responseText);
                        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            $('#saveMainCategoryEdit').click(function() {
                $.ajax({
                    url: '../system/update_type.php',
                    type: 'POST',
                    data: $('#editMainCategoryForm').serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('สำเร็จ', 'แก้ไขหมวดหมู่หลักเรียบร้อยแล้ว', 'success');
                            $('#editMainCategoryModal').modal('hide');
                            loadCategories();
                        } else {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถแก้ไขหมวดหมู่หลักได้', 'error');
                        }
                    }
                });
            });

            $('#saveSubCategory').click(function() {
                var url = $('#subCategoryId').val() ? '../system/update_category.php' : '../system/add_category.php';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $('#subCategoryForm').serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('สำเร็จ', 'บันทึกประเภทย่อยเรียบร้อยแล้ว', 'success');
                            $('#subCategoryModal').modal('hide');
                            loadCategories();
                        } else {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถบันทึกประเภทย่อยได้', 'error');
                        }
                    }
                });
            });
            $('#editMainCategoryModal .btn-secondary').click(function() {
        $('#editMainCategoryModal').modal('hide');
    });

    $('#subCategoryModal .btn-secondary').click(function() {
        $('#subCategoryModal').modal('hide');
    });
    $('.modal .close').click(function() {
        $(this).closest('.modal').modal('hide');
    });
            function loadCategories() {
                $.ajax({
                    url: '../api/get_types.php',
                    type: 'GET',
                    success: function(mainCategories) {
                        var categoryList = $('#categoryList');
                        categoryList.empty();
                        
                        mainCategories.forEach(function(mainCategory) {
                            var categoryHtml = `
                                <div class="category-container" data-id="${mainCategory.type_id}">
                                    <div class="main-category">
                                        <h5>${mainCategory.name}</h5>
                                        <div>
                                            <button class="icon-button edit edit-main-category" data-id="${mainCategory.type_id}" data-name="${mainCategory.name}" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="icon-button delete delete-main-category" data-id="${mainCategory.type_id}" title="ลบ">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <button class="icon-button add btn-add-sub" data-id="${mainCategory.type_id}" title="เพิ่มประเภทย่อย">
                                                <i class="fas fa-plus-circle fa-beat"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="sub-categories" id="sub-${mainCategory.type_id}">
                                        <!-- ประเภทย่อยจะถูกเพิ่มที่นี่ -->
                                    </div>
                                </div>
                            `;
                            categoryList.append(categoryHtml);
                            loadSubCategories(mainCategory.type_id);
                        });
                    }
                });
            }

            function loadSubCategories(mainCategoryId) {
                $.ajax({
                    url: '../api/get_categories.php',
                    type: 'GET',
                    data: { type_id: mainCategoryId },
                    success: function(subCategories) {
                        var subCategoryContainer = $(`#sub-${mainCategoryId}`);
                        subCategoryContainer.empty();
                        
                        subCategories.forEach(function(subCategory) {
                            var subCategoryHtml = `
                                <div class="sub-category" data-id="${subCategory.category_id}">
                                    <span>${subCategory.name}</span>
                                    <div>
                                        <button class="icon-button edit edit-sub-category" data-id="${subCategory.category_id}" data-name="${subCategory.name}" data-main-id="${mainCategoryId}" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="icon-button delete delete-sub-category" data-id="${subCategory.category_id}" title="ลบ">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            subCategoryContainer.append(subCategoryHtml);
                        });
                    }
                });
            }

            // Event handlers
            $(document).on('click', '.edit-main-category', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#editMainCategoryId').val(id);
                $('#editMainCategoryName').val(name);
                $('#editMainCategoryModal').modal('show');
            });

            $(document).on('click', '.btn-add-sub', function() {
                var mainCategoryId = $(this).data('id');
                $('#subCategoryId').val('');
                $('#subCategoryMainId').val(mainCategoryId);
                $('#subCategoryName').val('');
                $('#subCategoryModalTitle').text('เพิ่มประเภทย่อย');
                $('#subCategoryModal').modal('show');
            });

            $(document).on('click', '.edit-sub-category', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var mainCategoryId = $(this).data('main-id');
                $('#subCategoryId').val(id);
                $('#subCategoryMainId').val(mainCategoryId);
                $('#subCategoryName').val(name);
                $('#subCategoryModalTitle').text('แก้ไขประเภทย่อย');
                $('#subCategoryModal').modal('show');
            });

            $(document).on('click', '.delete-main-category', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: "การลบหมวดหมู่หลักจะลบประเภทย่อยทั้งหมดด้วย",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../system/delete_type.php',
                            type: 'POST',
                            data: { id: id },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('สำเร็จ', 'ลบหมวดหมู่หลักเรียบร้อยแล้ว', 'success');
                                    loadCategories();
                                } else {
                                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.delete-sub-category', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: "คุณต้องการลบประเภทย่อยนี้หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../system/delete_category.php',
                            type: 'POST',
                            data: { id: id },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('สำเร็จ', 'ลบประเภทย่อยเรียบร้อยแล้ว', 'success');
                                    loadCategories();
                                } else {
                                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>