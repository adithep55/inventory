<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Kanit', sans-serif;
    }
</style>

<div class="main-wrapper">
    <div id="mobile_btn" class="mobile_btn"></div>
    <!-- sidebar.html -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-inner slimscroll">
            <div id="sidebar-menu" class="sidebar-menu">
                <ul>
                    <li>    
                        <a href="<?php echo base_url(); ?>"><img src="<?php echo base_url(); ?>/assets/img/icons/dashboard.svg" alt="img"><span> หน้าแรก</span></a>
                    </li>
                    
                    <?php if (checkPermission(['manage_products'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/product.svg" alt="img"><span>สินค้า</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>/views/productlist" <?php echo (basename($_SERVER['PHP_SELF']) == 'productlist.php') ? 'class="active"' : ''; ?>>รายการสินค้า</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/product_type" <?php echo (basename($_SERVER['PHP_SELF']) == 'product_type.php') ? 'class="active"' : ''; ?>>หมวดหมู่และประเภทสินค้า</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (checkPermission(['manage_receiving'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/quotation1.svg" alt="img"><span>การรับสินค้า</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>/views/recieve" <?php echo (basename($_SERVER['PHP_SELF']) == 'recieve.php') ? 'class="active"' : ''; ?>>รับสินค้า</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/receive_history" <?php echo (basename($_SERVER['PHP_SELF']) == 'receive_history.php' || basename($_SERVER['PHP_SELF']) == 'receive_details.php') ? 'class="active"' : ''; ?>>รายการรับสินค้า</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php if (checkPermission(['manage_issue'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/expense1.svg" alt="img"><span>การเบิกสินค้า</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>/views/issue" <?php echo (basename($_SERVER['PHP_SELF']) == 'issue.php') ? 'class="active"' : ''; ?>>เบิกสินค้า</a></li>
                                                      <li><a href="<?php echo base_url(); ?>/views/issue_history" <?php echo (basename($_SERVER['PHP_SELF']) == 'issue_history.php' || basename($_SERVER['PHP_SELF']) == 'issue_details.php') ? 'class="active"' : ''; ?>>ประวัติการเบิก</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php if (checkPermission(['manage_inventory'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/product.svg" alt="img"><span>คลังสินค้า</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>/views/inventory" <?php echo (basename($_SERVER['PHP_SELF']) == 'inventory.php' || basename($_SERVER['PHP_SELF']) == 'inventory_details.php') ? 'class="active"' : ''; ?>>สต็อคสินค้า</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/locations" <?php echo (basename($_SERVER['PHP_SELF']) == 'locations.php') ? 'class="active"' : ''; ?>>ตำแหน่งคลังสินค้า</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (checkPermission(['manage_projects'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/quotation1.svg" alt="img"><span>โครงการ</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>/views/projects" <?php echo (basename($_SERVER['PHP_SELF']) == 'projects.php') ? 'class="active"' : ''; ?>>จัดการโครงการ</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (checkPermission(['manage_customers'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/users1.svg" alt="img"><span>ลูกค้า</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>/views/customers" <?php echo (basename($_SERVER['PHP_SELF']) == 'customers.php') ? 'class="active"' : ''; ?>>การจัดการลูกค้า</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/customers_type" <?php echo (basename($_SERVER['PHP_SELF']) == 'customers_type.php') ? 'class="active"' : ''; ?>>ประเภทลูกค้า</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/prefixes" <?php echo (basename($_SERVER['PHP_SELF']) == 'prefixes.php') ? 'class="active"' : ''; ?>>คำนำหน้า</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (checkPermission(['manage_transfers'])): ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/transfer1.svg" alt="img"><span>โอนย้ายสินค้า</span> <span class="menu-arrow"></span></a>
                        <ul>
                        <li><a href="<?php echo base_url(); ?>/views/addtransfer" <?php echo (basename($_SERVER['PHP_SELF']) == 'addtransfer.php') ? 'class="active"' : ''; ?>>ย้ายสินค้า</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/transfer_history" <?php echo (basename($_SERVER['PHP_SELF']) == 'transfer_history.php' || basename($_SERVER['PHP_SELF']) == 'transfer_details.php') ? 'class="active"' : ''; ?>>รายการย้ายสินค้า</a></li>
                        </ul>
                    </li>

                    <?php if (checkPermission(['manage_reports'])): ?>
                    <?php endif; ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="../assets/img/icons/time.svg" alt="img"><span> รายงาน</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="<?php echo base_url();?>/report/product_movement" <?php echo (basename($_SERVER['PHP_SELF']) == 'product_movement.php') ? 'class="active"' : ''; ?>>รายงานสินค้าเคลื่อนไหว</a></li>
                        </ul>
                    </li>

                    <?php if (checkPermission(['manage_users'])): ?>
                    <?php endif; ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="../assets/img/icons/users1.svg" alt="img"><span> ผู้ใช้งาน</span> <span class="menu-arrow"></span></a>
                        <ul>
                        <li><a href="<?php echo base_url(); ?>/views/setrole" <?php echo (basename($_SERVER['PHP_SELF']) == 'setrole.php') ? 'class="active"' : ''; ?>>บทบาท</a></li>
                            <li><a href="<?php echo base_url(); ?>/views/userlist" <?php echo (basename($_SERVER['PHP_SELF']) == 'userlist.php') ? 'class="active"' : ''; ?>>จัดการผู้ใช้</a></li>
                        </ul>
                    </li>

                    <?php if (checkPermission(['manage_settings'])): ?>
                    <?php endif; ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="../assets/img/icons/settings.svg" alt="img"><span>ตั้งค่า</span> <span class="menu-arrow"></span></a>
                        <ul>
                        <li><a href="<?php echo base_url(); ?>/views/setting" <?php echo (basename($_SERVER['PHP_SELF']) == 'setting.php') ? 'class="active"' : ''; ?>>ตั้งค่าเว็ปไซต์</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
