<?php
require_once '../config/permission.php';
requirePermission(['manage_inventory']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>คลังสินค้า</title>
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
            <div class="row">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-box"></i> คลังสินค้า</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url();?>">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">คลังสินค้า</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select id="locationFilter" class="form-control select2">
                                  <option value="">ทุกคลังสินค้า</option>
                                    <!-- ตัวเลือกคลังสินค้าจะถูกเพิ่มด้วย JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="inventoryTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr> 
                                        <th>รูปสินค้า</th>
                                        <th>รหัสสินค้า</th>
                                        <th>ชื่อสินค้า (ไทย)</th>
                                        <th>จำนวนสินค้า</th>
                                        <th>หน่วย</th>
                                        <th>การดำเนินการ</th>
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
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    // โหลดข้อมูลคลังสินค้า
    $.ajax({
        url: '../api/get_locations.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var options = '<option value="">ทุกคลังสินค้า</option>';
                $.each(response.data, function(index, location) {
                    options += '<option value="' + location.location_id + '">' + location.location + '</option>';
                });
                $('#locationFilter').html(options);
            } else {
                console.error('Failed to load locations:', response.message);
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'ไม่สามารถโหลดข้อมูลคลังสินค้าได้: ' + response.message
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'เกิดข้อผิดพลาดในการโหลดข้อมูลคลังสินค้า'
            });
        }
    });

    var inventoryTable = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/get_inventory.php',
            type: 'POST',
            data: function(d) {
                d.location_id = $('#locationFilter').val();
            }
        },
        columns: [
            {
                data: 'image_url',
                render: function (data, type, row) {
                    return '<img src="' + data + '" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">';
                }
            },
            { data: 'product_id' },
            { data: 'name_th' },
            { data: 'total_quantity' },
            { data: 'unit' },
            {
                data: null,
                render: function (data, type, row) {
                    var url = '/inventory/locationInfo/' + row.product_id;
                    return '<a href="' + url + '" style="height: 100%;"><img src="../assets/img/icons/eye.svg" ></a>';
                }
            }
        ],
        order: [[1, 'asc']],
        language: {
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

    // เพิ่ม event listener สำหรับการเปลี่ยนแปลงคลังสินค้า
    $('#locationFilter').change(function() {
        inventoryTable.ajax.reload();
    });

});
</script>
</body>
</html>