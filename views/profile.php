<?php
require_once '../config/permission.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>โปรไฟล์ผู้ใช้</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .profile-set .profile-top .profile-contentimg {
  margin-top: -40px;
  width: 130px;
  height: 130px; /* Add this line to make it square */
  position: relative;
  border: 10px solid #fff;
  border-radius: 50%;
  box-shadow: 0 4px 4px 0 #00000040;
  overflow: hidden; /* Add this to ensure the image doesn't overflow */
}

.profile-set .profile-top .profile-contentimg img {
  width: 100%; /* Ensure the image fills the container */
  height: 100%; /* Ensure the image fills the container */
  object-fit: cover; /* This will maintain aspect ratio and cover the area */
  border-radius: 50%; /* This is not strictly necessary but adds a safeguard */
}
</style>
<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>
    
    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title"> <i class="fa fa-user" aria-hidden="true"></i> โปรไฟล์</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url();?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">โปรไฟล์ผู้ใช้</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="profile-set">
                                <div class="profile-head">
                                </div>
                                <div class="profile-top">
                                    <div class="profile-content">
                                        <div class="profile-contentimg">
                                            <img src="../img/users/user.png" alt="img" id="profileImage">
                                            <div class="profileupload">
                                                <input type="file" id="imgInp" name="img" accept="image/*">
                                                <a href="javascript:void(0);"><img src="../assets/img/icons/edit-set.svg" alt="img"></a>
                                            </div>
                                        </div>
                                        <div class="profile-contentname">
                                            <h2 id="fullName"></h2>
                                            <h4>อัพเดทรูปภาพและข้อมูลส่วนตัวของคุณ</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <form id="profileForm" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-lg-6 col-sm-12">
                                        <div class="form-group">
                                            <label>ชื่อผู้ใช้</label>
                                            <input type="text" id="username" name="username" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-12">
                                        <div class="form-group">
                                            <label>ชื่อ</label>
                                            <input type="text" id="fname" name="fname">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-12">
                                        <div class="form-group">
                                            <label>นามสกุล</label>
                                            <input type="text" id="lname" name="lname">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-12">
                                        <div class="form-group">
                                            <label>รหัสผ่านใหม่</label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-input" id="password" name="password">
                                                <span class="fas toggle-password fa-eye-slash"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                    <button type="submit" class="btn btn-submit me-2">บันทึก</button>
                       <a href="javascript:history.back();" class="btn btn-cancel">ยกเลิก</a>
                                </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
    $(document).ready(function() {
        loadUserData();

        $("#profileForm").on('submit', function(e) {
            e.preventDefault();
            updateProfile();
        });

        $("#imgInp").change(function() {
            readURL(this);
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#profileImage').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function loadUserData() {
            $.ajax({
                type: 'GET',
                url: '../system/profile.php',
                data: { action: 'getUserInfo' },
                dataType: 'json',
                success: function(result) {
                    if (result.status == "success") {
                        $("#username").val(result.data.Username);
                        $("#fname").val(result.data.fname);
                        $("#lname").val(result.data.lname);
                        $("#fullName").text(result.data.fname + " " + result.data.lname);
                        $("#profileImage").attr('src', '../img/profile/' + result.data.img);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: 'ไม่สามารถโหลดข้อมูลผู้ใช้ได้'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                    });
                }
            });
        }

        function updateProfile() {
    var formData = new FormData($("#profileForm")[0]);
    formData.append('action', 'updateProfile');

    // ตรวจสอบว่ามีการเลือกรูปภาพหรือไม่
    var fileInput = $('#imgInp')[0];
    if(fileInput.files.length > 0) {
        console.log("File selected:", fileInput.files[0].name);
        formData.append('img', fileInput.files[0]);
    } else {
        console.log("No file selected");
    }

    $.ajax({
        type: 'POST',
        url: '../system/profile.php',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(result) {
            console.log("Server response:", result);
            if (result.status == "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: result.message
                }).then(function() {
                    loadUserData();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: result.message
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log("Response Text:", xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
            });
        }
    });
}
});
    </script>
</body>
</html>