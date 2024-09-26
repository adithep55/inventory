<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
    font-family: 'Kanit', sans-serif;
}

</style>

<div class="main-wrapper">
        <div id="mobile_btn" class="mobile_btn">

        </div>
<!-- sidebar.html -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
    
            <ul>
                <li>    
                    <a href="<?php echo base_url(); ?>"><img src="<?php echo base_url(); ?>/assets/img/icons/dashboard.svg" alt="img"><span> หน้าแรก</span></a>
                </li>
                
                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/product.svg" alt="img"><span>สินค้า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/productlist">รายการสินค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/product_type">หมวดหมู่และประเภทสินค้า</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/sales1.svg" alt="img"><span> ขาย</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="saleslist.html">รายการการขาย</a></li>
                    </ul>
                </li>
        
                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/expense1.svg" alt="img"><span>การเบิกสินค้า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/issue">เบิกสินค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/issue_history">ประวัติการเบิก</a></li>
                        <li><a href="#">โครงการ</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/quotation1.svg" alt="img"><span>การรับสินค้า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/recieve"<?php if (basename($_SERVER['PHP_SELF']) == 'recieve.php') { echo 'class="active"';} ?>>รับสินค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/receive_history">รายการรับสินค้า</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/product.svg" alt="img"><span>คลังสินค้า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/inventory.php"<?php if (basename($_SERVER['PHP_SELF']) == 'inventory.php') { echo 'class="active"';} ?>>สต็อคสินค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/locations"<?php if (basename($_SERVER['PHP_SELF']) == 'locations') { echo 'class="active"';} ?>>ตำแหน่งคลังสินค้า</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/quotation1.svg" alt="img"><span>โครงการ</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/projects"<?php if (basename($_SERVER['PHP_SELF']) == 'projects.php') { echo 'class="active"';} ?>>จัดการโครงการ</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/users1.svg" alt="img"><span>ลูกค้า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/customers"<?php if (basename($_SERVER['PHP_SELF']) == 'customers.php') { echo 'class="active"';} ?>>การจัดการลูกค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/custumers_type"<?php if (basename($_SERVER['PHP_SELF']) == 'custumers_type.php') { echo 'class="active"';} ?>>ประเภทลูกค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/prefixes"<?php if (basename($_SERVER['PHP_SELF']) == 'prefixes.php') { echo 'class="active"';} ?>>คำนำหน้า</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="<?php echo base_url(); ?>/assets/img/icons/transfer1.svg" alt="img"><span>โอนย้ายสินค้า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>/views/transfer_history"<?php if (basename($_SERVER['PHP_SELF']) == 'transfer_history.php') { echo 'class="active"';} ?>>รายการย้ายสินค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>/views/addtransfer"<?php if (basename($_SERVER['PHP_SELF']) == 'addtransfer.php') { echo 'class="active"';} ?>>ย้ายสินค้า</a></li>
                        <li><a href="<?php echo base_url(); ?>importtransfer.html">Import Transfer </a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="../assets/img/icons/time.svg" alt="img"><span> รายงาน</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="purchaseorderreport.html">Purchase order report</a></li>
                        <li><a href="inventoryreport.html">Inventory Report</a></li>
                        <li><a href="salesreport.html">Sales Report</a></li>
                        <li><a href="invoicereport.html">Invoice Report</a></li>
                        <li><a href="purchasereport.html">Purchase Report</a></li>
                        <li><a href="supplierreport.html">Supplier Report</a></li>
                        <li><a href="customerreport.html">Customer Report</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="../assets/img/icons/users1.svg" alt="img"><span> ผู้ใช้งาน</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="newuser.html">New User </a></li>
                        <li><a href="userlists.html">Users List</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><img src="../assets/img/icons/settings.svg" alt="img"><span>ตั้งค่า</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="generalsettings.html">General Settings</a></li>
                        <li><a href="emailsettings.html">Email Settings</a></li>
                        <li><a href="paymentsettings.html">Payment Settings</a></li>
                        <li><a href="currencysettings.html">Currency Settings</a></li>
                        <li><a href="grouppermissions.html">Group Permissions</a></li>
                        <li><a href="taxrates.html">Tax Rates</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var mobileBtn = document.getElementById('mobile_btn');
        var sidebar = document.getElementById('sidebar');

        mobileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !mobileBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        var submenuItems = document.querySelectorAll('.submenu > a');
        submenuItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var parent = this.parentElement;
                parent.classList.toggle('active');
            });
        });
    });
    </script>