<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>เพิ่มลูกค้าใหม่</title>
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
                        <h3 class="page-title">เพิ่มลูกค้าใหม่</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="customerlist.php">รายการลูกค้า</a></li>
                            <li class="breadcrumb-item active">เพิ่มลูกค้าใหม่</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="addCustomerForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>คำนำหน้า</label>
                                            <select class="select" id="prefix" name="prefix" required>
                                                <!-- จะถูกเติมด้วย JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ชื่อ</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ประเภทลูกค้า</label>
                                            <select class="select" id="customerType" name="customerType" required>
                                                <!-- จะถูกเติมด้วย JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ที่อยู่</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>เบอร์โทรศัพท์</label>
                                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>เลขประจำตัวผู้เสียภาษี</label>
                                            <input type="number" class="form-control" id="taxId" name="taxId">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ผู้ติดต่อ</label>
                                            <input type="text" class="form-control" id="contactPerson" name="contactPerson">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>วงเงินเครดิต</label>
                                            <input type="number" class="form-control" id="creditLimit" name="creditLimit" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>เงื่อนไขการชำระเงิน</label>
                                            <input type="text" class="form-control" id="creditTerms" name="creditTerms">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="history.back()">ยกเลิก</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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
        $('.select').select2();

        loadPrefixes();
        loadCustomerTypes();

        $('#addCustomerForm').on('submit', function(e) {
            e.preventDefault();
            addCustomer();
        });
    });

    function loadPrefixes() {
        $.ajax({
            url: '../api/get_prefixes.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var options = '<option value="">เลือกคำนำหน้า</option>';
                    $.each(response.data, function(index, prefix) {
                        options += '<option value="' + prefix.prefix_id + '">' + prefix.prefix + '</option>';
                    });
                    $('#prefix').html(options);
                }
            }
        });
    }

    function loadCustomerTypes() {
        $.ajax({
            url: '../api/get_customer_types.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var options = '<option value="">เลือกประเภทลูกค้า</option>';
                    $.each(response.data, function(index, type) {
                        options += '<option value="' + type.type_id + '">' + type.name + '</option>';
                    });
                    $('#customerType').html(options);
                }
            }
        });
    }

    function addCustomer() {
        var formData = $('#addCustomerForm').serialize();
        $.ajax({
            url: '../system/add_customer.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('สำเร็จ', 'เพิ่มลูกค้าใหม่เรียบร้อยแล้ว', 'success')
                        .then(() => {
                            window.location.href = '<?php echo base_url(); ?>/views/customers';
                        });
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถเพิ่มลูกค้าได้', 'error');
                }
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
            }
        });
    }
    </script>
</body>
</html>