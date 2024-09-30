<?php
session_start();
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
                <img src="../assets/img/logo.png" alt="">
            </a>
            <a href="<?php echo base_url(); ?>" class="logo-small">
                <img src="../assets/img/logo-small.png" alt="">
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
            <a class="dropdown-item" href="<?php echo base_url(); ?>/views/profile"> <i class="me-2" data-feather="user"></i> My Profile</a>
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
                <a class="dropdown-item" href="<?php echo base_url(); ?>/views/profile">My Profile</a>
                <a class="dropdown-item" href="signin.html">Logout</a>
            </div>
        </div>

    </div>
    </div>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    loadUserProfile();
    loadNotifications();
    
    // Refresh notifications every 5 minutes
    setInterval(loadNotifications, 300000);

    // Add click event for showing low stock details
    $('#show-low-stock-details-header').on('click', function(e) {
        e.preventDefault();
        // Implement the logic to show low stock details
        // For example, you might want to open a modal or navigate to a specific page
        console.log('Show low stock details clicked');
    });
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

function loadNotifications() {
    $.ajax({
        url: '../api/dashboard.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data && response.data.notifications) {
                updateNotificationUI(response.data.notifications);
            } else {
                console.error('Failed to load notifications:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
        }
    });
}

function updateNotificationUI(notifications) {
    const lowStockNotifications = notifications.filter(n => n.type === 'low_stock');
    const lowStockCount = lowStockNotifications.length;

    // Update low stock notification count
    $('.low-stock-notification-count').text(lowStockCount);

    // Show/hide low stock notification
    if (lowStockCount > 0) {
        $('.low-stock-notification').show();
    } else {
        $('.low-stock-notification').hide();
    }

    // Update total notification count
    const totalNotifications = notifications.length;
    $('.badge.rounded-pill').text(totalNotifications);

    // Update notification list (if needed)
    updateNotificationList(notifications);
    console.log('Notifications:', notifications);
console.log('Low stock count:', lowStockCount);
}

function updateNotificationList(notifications) {
    const notificationList = $('.notification-list');
    notificationList.empty();

    notifications.forEach(notification => {
        let icon = 'info-circle';
        if (notification.type === 'low_stock') icon = 'exclamation-triangle';
        else if (notification.type === 'recent_issue') icon = 'arrow-right';
        else if (notification.type === 'recent_receive') icon = 'arrow-left';

        const notificationItem = `
            <li class="notification-message">
                <a href="${notification.link}">
                    <div class="media d-flex">
                        <span class="avatar flex-shrink-0">
                            <i class="fas fa-${icon}"></i>
                        </span>
                        <div class="media-body flex-grow-1">
                            <p class="noti-details"><span class="noti-title">${notification.title}</span> ${notification.message}</p>
                            <p class="noti-time"><span class="notification-time">${notification.time}</span></p>
                        </div>
                    </div>
                </a>
            </li>
        `;
        notificationList.append(notificationItem);
    });
}



// Load notifications when the page loads
$(document).ready(function() {
    loadNotifications();
});

// Refresh notifications every 5 minutes
setInterval(loadNotifications, 300000);
</script>

</body>
</html>