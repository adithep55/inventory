<?php
require_once '../config/permission.php';
requirePermission(['manage_customers']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>ประเภทลูกค้า</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
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
                <div class="row">
                    <div class="col">
                        <h3 class="page-title"><i class="fas fa-users"></i> ประเภทลูกค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url()?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">จัดการประเภทลูกค้า</li>
                        </ul>
                    </div>
                </div>
            </div>


            <!-- ประเภทลูกค้า -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><i class="fas fa-bars"></i> ประเภทลูกค้า</h4>
                            <div class="page-btn">
                            <button class="btn btn-primary float-right" onclick="showAddCustomerTypeModal()">เพิ่มประเภทลูกค้า</button>
                </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0" id="customerTypeTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ชื่อประเภท</th>
                                            <th>ส่วนลด (%)</th>
                                            <th>การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal สำหรับเพิ่ม/แก้ไขประเภทลูกค้า -->
    <div class="modal fade" id="customerTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerTypeModalTitle">เพิ่มประเภทลูกค้า</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="customerTypeForm">
                        <input type="hidden" id="customerTypeId" name="customerTypeId">
                        <div class="form-group">
                            <label for="customerTypeName">ชื่อประเภท</label>
                            <input type="text" class="form-control" id="customerTypeName" name="customerTypeName" required>
                        </div>
                        <div class="form-group">
                            <label for="discountRate">ส่วนลด (%)</label>
                            <input type="number" class="form-control" id="discountRate" name="discountRate" min="0" max="100" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="saveCustomerType()">บันทึก</button>
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
        loadCustomerTypes();
    });

    $('#customerTypeModal .btn-secondary').on('click', function() {
        $('#customerTypeModal').modal('hide');
    });
    $('#customerTypeModal .close').on('click', function() {
        $('#customerTypeModal').modal('hide');
    });

    function loadCustomerTypes() {
        $('#customerTypeTable').DataTable({
            ajax: {
                url: '../api/get_customer_types.php',
                dataSrc: 'data'
            },
            columns: [
                { data: 'type_id' },
                { data: 'name' },
                { data: 'discount_rate' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<button class="btn btn-sm btn-primary" onclick="editCustomerType(' + row.type_id + ', \'' + row.name + '\', ' + row.discount_rate + ')">แก้ไข</button> ' +
                               '<button class="btn btn-sm btn-danger" onclick="deleteCustomerType(' + row.type_id + ')">ลบ</button>';
                    }
                }
            ]
            ,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "emptyTable": "ไม่พบข้อมูลสินค้า",
                        "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                        "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
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
    }


    function showAddCustomerTypeModal() {
        $('#customerTypeId').val('');
        $('#customerTypeName').val('');
        $('#discountRate').val('');
        $('#customerTypeModalTitle').text('เพิ่มประเภทลูกค้า');
        $('#customerTypeModal').modal('show');
    }

    function editCustomerType(id, name, discountRate) {
        $('#customerTypeId').val(id);
        $('#customerTypeName').val(name);
        $('#discountRate').val(discountRate);
        $('#customerTypeModalTitle').text('แก้ไขประเภทลูกค้า');
        $('#customerTypeModal').modal('show');
    }

    function saveCustomerType() {
        var id = $('#customerTypeId').val();
        var name = $('#customerTypeName').val();
        var discountRate = $('#discountRate').val();
        var url = id ? '../system/update_customer_type.php' : '../system/add_customer_type.php';
        $.ajax({
            url: url,
            type: 'POST',
            data: { id: id, name: name, discount_rate: discountRate },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('สำเร็จ', response.message, 'success');
                    $('#customerTypeModal').modal('hide');
                    $('#customerTypeTable').DataTable().ajax.reload();
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการดำเนินการ', 'error');
            }
        });
    }


    function deleteCustomerType(id) {
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: "คุณแน่ใจหรือไม่ที่จะลบประเภทลูกค้านี้?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../system/delete_customer_type.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('ลบสำเร็จ', response.message, 'success');
                            $('#customerTypeTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
                    }
                });
            }
        });
    }

</script>
</body>
</html>