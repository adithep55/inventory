<?php
// รับ error code จาก URL parameter
$errorCode = $_GET['code'] ?? '404';

// กำหนดข้อความ error ตาม error code
switch ($errorCode) {
    case '400':
        $errorTitle = 'Bad Request';
        $errorMessage = 'The server cannot process the request due to something that is perceived to be a client error.';
        break;
    case '401':
        $errorTitle = 'Unauthorized';
        $errorMessage = 'You are not authorized to access this page. Please log in and try again.';
        break;
    case '403':
        $errorTitle = 'Forbidden';
        $errorMessage = 'You do not have permission to access this page.';
        break;
    case '404':
        $errorTitle = 'Page not found';
        $errorMessage = 'The page you requested was not found.';
        break;
    case '500':
        $errorTitle = 'Internal Server Error';
        $errorMessage = 'The server encountered an unexpected condition that prevented it from fulfilling the request.';
        break;
    default:
        $errorTitle = 'Oops! An error occurred';
        $errorMessage = 'We encountered an unexpected error. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Error Page">
    <meta name="keywords" content="admin, error, bootstrap, business, corporate, management, minimal, modern, html5, responsive">
    <meta name="author" content="Your Company Name">
    <meta name="robots" content="noindex, nofollow">
    <title>Error <?php echo htmlspecialchars($errorCode); ?> - <?php echo htmlspecialchars($errorTitle); ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="error-page">
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>

    <div class="main-wrapper">
        <div class="error-box">
            <h1><?php echo htmlspecialchars($errorCode); ?></h1>
            <h3 class="h2 mb-3"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorTitle); ?></h3>
            <p class="h4 font-weight-normal"><?php echo htmlspecialchars($errorMessage); ?></p>
            <a href="index.php" class="btn btn-primary">กลับไปหน้าหลัก</a>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>