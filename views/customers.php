<?php
require_once '../config/permission.php';
requirePermission(['manage_customers']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายการลูกค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
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
                <div class="page-title">
                    <h4> <i class="fas fa-user"></i> รายการลูกค้า</h4>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url();?>"> หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">จัดการลูกค้า</li>
                    </ul>
                </div>
                
                <div class="page-btn">
                    <a href="addcustomer" class="btn btn-added"><img src="../assets/img/icons/plus.svg" alt="img" class="me-1">เพิ่มลูกค้าใหม่</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                    </div>

                    <div class="table-responsive">
                        <table class="table datanew" id="customerTable">
                            <thead>
                                <tr>
                                    <th>
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>คำนำหน้า</th>
                                    <th>ชื่อ</th>
                                    <th>ประเภทลูกค้า</th>
                                    <th>ที่อยู่</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th>เลขประจำตัวผู้เสียภาษี</th>
                                    <th>ผู้ติดต่อ</th>
                                    <th>วงเงินเครดิต</th>
                                    <th>เงื่อนไขการชำระเงิน</th>
                                    <th>การกระทำ</th>
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
$(document).ready(function () {
    // ตรวจสอบว่าตารางมี DataTable อยู่แล้วหรือไม่
    if ($.fn.DataTable.isDataTable('#customerTable')) {
        // หากมี DataTable อยู่แล้ว ให้ทำลายมันก่อน
        $('#customerTable').DataTable().destroy();
    }

    // สร้าง DataTable ใหม่
    var customerTable = $('#customerTable').DataTable({
        "processing": true,
        "serverSide": false, // เปลี่ยนเป็น false เพราะเราจะจัดการข้อมูลทั้งหมดที่ client-side
        "ajax": {
            "url": "../api/get_customers.php",
            "type": "GET",
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columns": [
            {
                "data": null,
                "render": function (data, type, row) {
                    return '<label class="checkboxs"><input type="checkbox" class="customer-checkbox" value="' + row.customer_id + '"><span class="checkmarks"></span></label>';
                },
                "orderable": false
            },
            { 
                "data": "full_name",
                "render": function(data, type, row) {
                    var nameParts = data.split(' ');
                    return nameParts[0]; // คำนำหน้า
                }
            },
            { 
                "data": "full_name",
                "render": function(data, type, row) {
                    var nameParts = data.split(' ');
                    return nameParts.slice(1).join(' '); // ชื่อ (ไม่รวมคำนำหน้า)
                }
            },
            { "data": "customer_type" },
            { "data": "address" },
            { "data": "phone_number" },
            { "data": "tax_id" },
            { "data": "contact_person" },
            { 
                "data": "credit_limit",
                "render": function(data, type, row) {
                    return parseFloat(data).toFixed(2);
                }
            },
            { "data": "credit_terms" },
            { 
                "data": "customer_id",
                "render": function (data, type, row) {
                    return '<a class="me-3" href="edit_customer.php?id=' + data + '"><img src="../assets/img/icons/edit.svg" alt="Edit"></a>' +
                           '<a class="me-3 confirm-text" href="javascript:void(0);" onclick="deleteCustomer(' + data + ')"><img src="../assets/img/icons/delete.svg" alt="Delete"></a>';
                },
                "orderable": false
            }
        ],
        "drawCallback": function(settings) {
            updateSelectAllCheckbox();
        },
        "language": {
            "emptyTable": "ไม่พบข้อมูลลูกค้า",
            "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
            "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
            "lengthMenu": "แสดง _MENU_ รายการ",
            "search": "ค้นหา:",
            "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
            "paginate": {
                "first": "หน้าแรก",
                "last": "หน้าสุดท้าย",
                "next": "ถัดไป",
                "previous": "ก่อนหน้า"
            }
        }
    });

    // จัดการกับการคลิกที่ checkbox ทั้งหมด
    $('#select-all').on('click', function() {
        $('.customer-checkbox').prop('checked', this.checked);
    });

    // จัดการกับการคลิกที่ checkbox แต่ละรายการ
    $('#customerTable').on('change', '.customer-checkbox', function() {
        updateSelectAllCheckbox();
    });

    // อัพเดทสถานะของ checkbox ทั้งหมด
    function updateSelectAllCheckbox() {
        var allChecked = $('.customer-checkbox:checked').length === $('.customer-checkbox').length && $('.customer-checkbox').length > 0;
        $('#select-all').prop('checked', allChecked);
    }
});

function deleteCustomer(customerId) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: "คุณแน่ใจหรือไม่ที่จะลบลูกค้านี้?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../system/delete_customer.php',
                type: 'POST',
                data: { id: customerId },
                dataType: 'json',
                success: function (response) {
                    console.log('Raw response:', response);
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถประมวลผลการตอบสนองจากเซิร์ฟเวอร์ได้', 'error');
                            return;
                        }
                    }
                    if (response.status === 'success') {
                        Swal.fire(
                            'ลบสำเร็จ!',
                            response.message,
                            'success'
                        ).then(() => {
                            $('#customerTable').DataTable().ajax.reload(null, false);
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'เกิดข้อผิดพลาดในการลบลูกค้า: ' + error,
                        'error'
                    );
                }
            });
        }
    });
}
    </script>
    
</body>
</html>