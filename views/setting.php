<?php
require_once '../config/permission.php';
requirePermission(['manage_settings']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>ตั้งค่าเว็บไซต์</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
    </style>
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
                        <h3 class="page-title">ตั้งค่าเว็บไซต์</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">แดชบอร์ด</a></li>
                            <li class="breadcrumb-item active">ตั้งค่าเว็บไซต์</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title">
                                    <i class="fas fa-cog me-2"></i> การตั้งค่าเว็บไซต์
                                </h4>
                            </div>
                            <form id="settingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="company_name" class="form-label"><i class="fas fa-building me-2"></i> ชื่อบริษัท</label>
                                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="company_contact" class="form-label"><i class="fas fa-phone me-2"></i> ข้อมูลติดต่อ</label>
                                            <input type="text" class="form-control" id="company_contact" name="company_contact" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="company_address" class="form-label"><i class="fas fa-map-marker-alt me-2"></i> ที่อยู่บริษัท</label>
                                            <textarea class="form-control" id="company_address" name="company_address" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="logo" class="form-label"><i class="fas fa-image me-2"></i> โลโก้หลัก</label>
                                            <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                                            <div class="mt-2">
                                                <img id="logoPreview" class="preview-image" src="" alt="Logo Preview">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="small_logo" class="form-label"><i class="fas fa-image me-2"></i> โลโก้ขนาดเล็ก</label>
                                            <input class="form-control" type="file" id="small_logo" name="small_logo" accept="image/*">
                                            <div class="mt-2">
                                                <img id="smallLogoPreview" class="preview-image" src="" alt="Small Logo Preview">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> บันทึกการตั้งค่า
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        function loadSettings() {
            $.ajax({
                url: '../api/get_website_settings.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        $('#company_name').val(response.data.company_name || '');
                        $('#company_address').val(response.data.company_address || '');
                        $('#company_contact').val(response.data.company_contact || '');
                        if (response.data.logo) {
                            $('#logoPreview').attr('src', '../assets/img/' + response.data.logo).css('display', 'block');
                        }
                        if (response.data.small_logo) {
                            $('#smallLogoPreview').attr('src', '../assets/img/' + response.data.small_logo).css('display', 'block');
                        }
                    } else {
                        console.error('เกิดข้อผิดพลาดในการโหลดการตั้งค่า:', response.message);
                        Swal.fire('Error', 'ไม่สามารถโหลดการตั้งค่าได้', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์:', error);
                    Swal.fire('Error', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                }
            });
        }

        $('#settingsForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: '../system/update_settings.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
            Swal.fire('Success', 'อัปเดตการตั้งค่าเรียบร้อยแล้ว', 'success').then(function() {
                location.reload(); 
            });
                loadSettings();
            } else {
                Swal.fire('Error', 'เกิดข้อผิดพลาด: ' + response.message, 'error');
                console.error('Server error:', response);
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
            console.error('AJAX error:', status, error);
            console.log('Response:', xhr.responseText);
        }
    });
});

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + input.id + 'Preview').attr('src', e.target.result).css('display', 'block');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#logo, #small_logo").change(function() {
            readURL(this);
        });

        loadSettings();
    });
    </script>
</body>
</html>