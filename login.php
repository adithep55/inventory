<?php
session_start();
if (isset($_SESSION['UserID'])) {
    header("Location: " . base_url());
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="login-form">
        <h1>เข้าสู่ระบบ</h1>
        <div class="container">
            <div class="main">
                <div class="form-img">
                    <img src="img/construct.png" alt="">
                </div>
                <div class="content">
                    <h2>Log In</h2>
                    <div id="loginForm">
                        <input type="text" id="user" name="user" placeholder="UserName" required autofocus>
                        <div id="userInfoContainer" style="display: none;">
                            <input type="text" id="userInfo" name="userInfo" disabled>
                        </div>
                        <input type="password" id="pass" name="pass" placeholder="User Password" required>
                        <button id="btn_regis" class="btn" type="button">
                            Login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        $(document).ready(function () {

            $(document).ready(function () {
                // ซ่อน userInfoContainer เมื่อโหลดหน้า
                $("#userInfoContainer").hide();

                $("#user").on('blur', function () {
                    var username = $(this).val().trim();
                    if (username) {
                        $.ajax({
                            type: 'GET',
                            url: 'system/login.php',
                            data: { action: 'getUserInfo', username: username },
                            dataType: 'json',
                            success: function (result) {
                                if (result.status == "success") {
                                    showUserInfo(result.message.fname + " " + result.message.lname, false);
                                } else {
                                    showUserInfo("❌ ไม่พบผู้ใช้", true);
                                }
                            },
                            error: function () {
                                showUserInfo("❌ เกิดข้อผิดพลาดในการตรวจสอบผู้ใช้", true);
                            }
                        });
                    } else {
                        hideUserInfo();
                    }
                });
            });

            function showUserInfo(message, isError) {
                var $userInfo = $("#userInfo");
                if (isError) {
                    $userInfo.val(message).addClass('error');
                } else {
                    $userInfo.val("ยินดีต้อนรับ🤗 : " + message).removeClass('error');
                }
                $("#userInfoContainer").show();
            }

            function hideUserInfo() {
                $("#userInfoContainer").hide();
                $("#userInfo").val("");
            }

            $("#user").on('input', function () {
                if ($(this).val().trim() === "") {
                    hideUserInfo();
                }
            });

            $("#btn_regis").on('click', function (e) {
                var formData = new FormData();
                formData.append('user', $("#user").val());
                formData.append('pass', $("#pass").val());

                $('#btn_regis').attr('disabled', 'disabled');

                $.ajax({
                    type: 'POST',
                    url: 'system/login.php',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'text',
                }).done(function (res) {
                    result = JSON.parse(res);
                    console.log(result);

                    if (result.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: result.message
                        }).then(function () {
                            window.location.href = 'index';
                        });
                    } else if (result.status == "fail") {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: result.message
                        });
                        $('#btn_regis').removeAttr('disabled');
                    }
                }).fail(function (jqXHR) {
                    console.log(jqXHR);
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                    });
                    $('#btn_regis').removeAttr('disabled');
                });
            });
        });
    </script>
</body>
<?php
function base_url()
{
  return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}
$links_re = base_url();
?>
</html>