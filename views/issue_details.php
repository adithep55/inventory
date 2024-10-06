<?php
require_once '../config/permission.php';
requirePermission(['manage_issue']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายละเอียดการเบิกสินค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                        <h3 class="page-title">การเบิกสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="issue_history.php">เบิกสินค้า</a></li>
                            <li class="breadcrumb-item active">รายละเอียดการเบิกสินค้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">
                                    <i class="fas fa-info-circle mr-2"></i> ข้อมูลการเบิกสินค้า
                                </h4>
                                <div class="wordset">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item">
                                            <a id="pdfButton" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
                                                <img src="../assets/img/icons/pdf.svg" alt="PDF">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-file-alt mr-2"></i>  เลขที่เอกสารเบิกสินค้า</label>
                                        <input type="text" class="form-control" id="billNumber" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt mr-2"></i>  วันที่เบิกสินค้า</label>
                                        <input type="text" class="form-control" id="issueDate" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-tags mr-2"></i>  ประเภทการเบิก</label>
                                        <input type="text" class="form-control" id="issueType" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user mr-2"></i>  ผู้เบิกสินค้า</label>
                                        <input type="text" class="form-control" id="requesterName" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-clock mr-2"></i>  เวลาอัพเดตล่าสุด</label>
                                        <input type="text" class="form-control" id="updatedAt" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

          <!-- ส่วนข้อมูลลูกค้า -->
<div class="row" id="customerDetails" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user mr-2"></i> ข้อมูลลูกค้า</span>
                    <button id="toggleCustomerDetails" class="btn btn-primary btn-sm">
                        <i class="fas fa-chevron-down"></i> แสดงข้อมูลเพิ่มเติม
                    </button>
                </h4>
                <div id="customerDetailsContent" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อลูกค้า</label>
                                <input type="text" class="form-control" id="customerName" readonly>
                            </div>
                            <div class="form-group">
                                <label>ที่อยู่</label>
                                <textarea class="form-control" id="customerAddress" rows="3" readonly></textarea>
                            </div>
                            <div class="form-group">
                                <label>เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" id="customerPhone" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>เลขประจำตัวผู้เสียภาษี</label>
                                <input type="text" class="form-control" id="customerTaxId" readonly>
                            </div>
                            <div class="form-group">
                                <label>ผู้ติดต่อ</label>
                                <input type="text" class="form-control" id="customerContactPerson" readonly>
                            </div>
                            <div class="form-group">
                                <label>วงเงินเครดิต</label>
                                <input type="text" class="form-control" id="customerCreditLimit" readonly>
                            </div>
                            <div class="form-group">
                                <label>เงื่อนไขเครดิต</label>
                                <input type="text" class="form-control" id="customerCreditTerms" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ส่วนข้อมูลโครงการ -->
<div class="row" id="projectDetails" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-project-diagram mr-2"></i> ข้อมูลโครงการ</span>
                    <button id="toggleProjectDetails" class="btn btn-primary btn-sm">
                        <i class="fas fa-chevron-down"></i> แสดงข้อมูลเพิ่มเติม
                    </button>
                </h4>
                <div id="projectDetailsContent" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อโครงการ</label>
                                <input type="text" class="form-control" id="projectName" readonly>
                            </div>
                            <div class="form-group">
                                <label>รายละเอียดโครงการ</label>
                                <textarea class="form-control" id="projectDescription" rows="3" readonly></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>วันที่เริ่มต้น</label>
                                <input type="text" class="form-control" id="projectStartDate" readonly>
                            </div>
                            <div class="form-group">
                                <label>วันที่สิ้นสุด</label>
                                <input type="text" class="form-control" id="projectEndDate" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">
                                <i class="fas fa-list mr-2"></i>  รายการสินค้าที่เบิก
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0" id="issueItemsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-barcode mr-2"></i>  รหัสสินค้า</th>
                                            <th><i class="fas fa-box mr-2"></i>  ชื่อสินค้า</th>
                                            <th><i class="fas fa-hashtag mr-2"></i>  จำนวน</th>
                                            <th><i class="fas fa-balance-scale mr-2"></i>  หน่วย</th>
                                            <th><i class="fas fa-warehouse mr-2"></i>  คลังสินค้า</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
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
    const urlParams = new URLSearchParams(window.location.search);
    const issueId = urlParams.get('id');

    // เริ่มต้น tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    if (issueId) {
        loadIssueDetails(issueId);
    } else {
        showAlert('ไม่พบรหัสการเบิกสินค้า', 'error');
    }

    // PDF button
    $('#pdfButton').on('click', function(e) {
        e.preventDefault();
        if (issueId) {
            window.open(`../report/generate_issue_report.php?id=${issueId}`, '_blank');
        } else {
            showAlert('ไม่พบรหัสการเบิกสินค้า', 'error');
        }
    });

    function loadIssueDetails(issueId) {
        $.ajax({
            url: '../api/get_issue_details.php',
            type: 'GET',
            data: { id: issueId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    displayIssueDetails(response.data);
                } else {
                    showAlert('เกิดข้อผิดพลาด: ' + response.message, 'error');
                }
            },
            error: function() {
                showAlert('ไม่สามารถโหลดข้อมูลได้', 'error');
            }
        });
    }

    function displayIssueDetails(data) {
        $('#billNumber').val(data.bill_number);
        $('#issueDate').val(data.issue_date);
        $('#issueType').val(data.issue_type === 'sale' ? 'เบิกเพื่อขาย' : 'เบิกเพื่อโครงการ');
        $('#requesterName').val(data.full_name);
        $('#updatedAt').val(data.updated_at);
        
        if (data.issue_type === 'sale') {
            $('#customerDetails').show();
            $('#projectDetails').hide();
            if (data.customer_details) {
                $('#customerName').val(data.customer_details.name);
                $('#customerAddress').val(data.customer_details.address);
                $('#customerPhone').val(data.customer_details.phone_number);
                $('#customerTaxId').val(data.customer_details.tax_id);
                $('#customerContactPerson').val(data.customer_details.contact_person || '-');
                $('#customerCreditLimit').val(data.customer_details.credit_limit);
                $('#customerCreditTerms').val(data.customer_details.credit_terms);
            }
        } else {
            $('#customerDetails').hide();
            $('#projectDetails').show();
            if (data.project_details) {
                $('#projectName').val(data.project_details.project_name);
                $('#projectDescription').val(data.project_details.project_description);
                $('#projectStartDate').val(data.project_details.start_date);
                $('#projectEndDate').val(data.project_details.end_date);
            }
        }

        const itemsTable = $('#issueItemsTable tbody');
        itemsTable.empty();
        
        data.items.forEach(function(item) {
            itemsTable.append(`
                <tr>
                    <td>${item.product_id}</td>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.unit}</td>
                    <td>${item.location_name}</td>
                </tr>
            `);
        });
    }

    function showAlert(message, icon) {
        Swal.fire({
            text: message,
            icon: icon,
            confirmButtonText: 'ตกลง'
        });
    }
});
 // ฟังก์ชันสำหรับสลับการแสดง/ซ่อนข้อมูล
 function toggleDetails(contentId, buttonId) {
        $(`#${contentId}`).slideToggle();
        var $button = $(`#${buttonId}`);
        var $icon = $button.find('i');
        if ($icon.hasClass('fa-chevron-down')) {
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $button.html('<i class="fas fa-chevron-up"></i> ซ่อนข้อมูลเพิ่มเติม');
        } else {
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $button.html('<i class="fas fa-chevron-down"></i> แสดงข้อมูลเพิ่มเติม');
        }
    }
      // การจัดการการคลิกปุ่มแสดง/ซ่อนข้อมูลลูกค้า
      $('#toggleCustomerDetails').click(function() {
        toggleDetails('customerDetailsContent', 'toggleCustomerDetails');
    });

    // การจัดการการคลิกปุ่มแสดง/ซ่อนข้อมูลโครงการ
    $('#toggleProjectDetails').click(function() {
        toggleDetails('projectDetailsContent', 'toggleProjectDetails');
    });
</script>
</body>

</html>