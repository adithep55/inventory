<?php
require_once '../config/permission.php';
requirePermission(['manage_customers']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขข้อมูลลูกค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title"><i class="fas fa-edit"></i> แก้ไขข้อมูลลูกค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url()?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url()?>/views/customers">รายการลูกค้า</a></li>
                            <li class="breadcrumb-item active">แก้ไขข้อมูลลูกค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="editCustomerForm">
                                <input type="hidden" id="customerId" name="customerId">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>คำนำหน้า</label>
                                            <select class="select" id="prefix" name="prefix">
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
                                            <select class="select" id="customerType" name="customerType">
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
                                            <input type="number" id="taxId" name="taxId" class="form-control">
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
                                            <input type="number" class="form-control" id="creditLimit" name="creditLimit" step="1">
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
                                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
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

    var customerId = new URLSearchParams(window.location.search).get('id');
    if (customerId) {
        loadPrefixes();
        loadCustomerTypes();
        loadCustomerData(customerId);
    }

    $('#editCustomerForm').on('submit', function(e) {
        e.preventDefault();
        updateCustomer();
    });
});

function loadCustomerData(customerId) {
    $.ajax({
        url: '../api/get_customers.php',
        type: 'GET',
        data: { id: customerId },
        dataType: 'json',
        success: function(response) {
            console.log('API Response:', response);
            if (response.status === 'success' && response.data) {
                var customer = response.data;
                $('#customerId').val(customer.customer_id);
                $('#name').val(customer.name);
                $('#address').val(customer.address);
                $('#phoneNumber').val(customer.phone_number);
                $('#taxId').val(customer.tax_id);
                $('#contactPerson').val(customer.contact_person);
                $('#creditLimit').val(customer.credit_limit);
                $('#creditTerms').val(customer.credit_terms);

                // Set prefix and customer type after options are loaded
                $('#prefix').val(customer.prefix_id).trigger('change');
                $('#customerType').val(customer.customer_type_id).trigger('change');
            } else {
                Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูลลูกค้า: ' + (response.message || 'Unknown error'), 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Error', 'เกิดข้อผิดพลาดขณะโหลดข้อมูลลูกค้า: ' + textStatus, 'error');
        }
    });
}

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
                
                // Re-trigger change event to ensure the correct option is selected
                var selectedPrefixId = $('#prefix').val();
                if (selectedPrefixId) {
                    $('#prefix').val(selectedPrefixId).trigger('change');
                }
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
                
                var selectedTypeId = $('#customerType').val();
                if (selectedTypeId) {
                    $('#customerType').val(selectedTypeId).trigger('change');
                }
            }
        }
    });
}



function updateCustomer() {
    var formData = $('#editCustomerForm').serialize();
    $.ajax({
        url: '../system/update_customer.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('สำเร็จ', 'อัปเดตข้อมูลลูกค้าเรียบร้อยแล้ว', 'success')
                    .then(() => {
                        window.location.href = '<?php echo base_url(); ?>/views/customers';
                    });
            } else {
                Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถอัปเดตข้อมูลลูกค้าได้', 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดขณะอัปเดตข้อมูลลูกค้า: ' + textStatus, 'error');
        }
    });
}
</script>
</body>
</html>