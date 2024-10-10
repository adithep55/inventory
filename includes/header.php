
<?php

if (!isset($_SESSION['UserID'])) {
    header("Location: " . base_url() . "/login");
    exit();
}
function base_url()
{
  return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}
$links_re = base_url();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<div id="global-loader">
    <div class="whirly-loader"> </div>
</div>
<div class="main-wrapper">

    <div class="header">

    <div class="header-left active">
    <a href="<?php echo base_url(); ?>" class="logo">
        <img src="" alt="Logo" id="mainLogo">
    </a>
    <a href="<?php echo base_url(); ?>" class="logo-small">
        <img src="" alt="Small Logo" id="smallLogo">
    </a>
    <a id="toggle_btn" href="javascript:void(0);">
    </a>
</div>

        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>

        <ul class="nav user-menu">

            <li class="nav-item dropdown has-arrow main-drop">
    <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
        <span class="user-img"><img id="userProfileImage" src="../assets/img/profiles/avator1.jpg" alt="">
            <span class="status online"></span></span>
    </a>
    <div class="dropdown-menu menu-drop-user">
        <div class="profilename">
            <div class="profileset">
                <span class="user-img"><img id="userProfileImageDropdown" src="../assets/img/profiles/avator1.jpg" alt="">
                    <span class="status online"></span></span>
                <div class="profilesets">
                    <h6 id="userName">Loading...</h6>
                    <h5 id="userRole">Loading...</h5>
                </div>
            </div>
            <hr class="m-0">
            <a class="dropdown-item" href="<?php echo base_url(); ?>/views/profile"> <i class="me-2" data-feather="user"></i> โปรไฟล์ของฉัน</a>
            <hr class="m-0">
            <a class="dropdown-item logout pb-0" href="<?php echo base_url(); ?>/logout"><img
                    src="../assets/img/icons/log-out.svg" class="me-2" alt="img">Logout</a>
        </div>
    </div>
</li>
        </ul>


        <div class="dropdown mobile-user-menu">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="<?php echo base_url(); ?>/views/profile">โปรไฟล์ของฉัน</a>
                <a class="dropdown-item" href="<?php echo base_url(); ?>/logout">Logout</a>
            </div>
        </div>

    </div>
    </div>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    loadUserProfile();
});

function loadUserProfile() {
    $.ajax({
        url: '../system/profile.php',
        type: 'GET',
        data: { action: 'getUserInfo' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                updateProfileUI(response.data);
            } else {
                console.error('Failed to load user profile:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
        }
    });
}

function updateProfileUI(userData) {
    $('#userProfileImage, #userProfileImageDropdown').attr('src', '../img/profile/' + userData.img);
    $('#userName').text(userData.fname);
    $('#userRole').text(userData.role);
}
$.ajax({
        url: '../api/get_website_settings.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                if (response.data.logo) {
                    $('#mainLogo').attr('src', '<?php echo base_url(); ?>/assets/img/' + response.data.logo);
                }
                if (response.data.small_logo) {
                    $('#smallLogo').attr('src', '<?php echo base_url(); ?>/assets/img/' + response.data.small_logo);
                }
            } else {
                console.error('เกิดข้อผิดพลาดในการโหลดการตั้งค่า:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์:', error);
        }
    });
</script>

</body>
</html>