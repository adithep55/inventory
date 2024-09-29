<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>จัดการคำนำหน้า</title>
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
                    <div class="col"></h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">จัดการคำนำหน้า</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- คำนำหน้า -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">คำนำหน้า</h4>
                            <button class="btn btn-primary float-right" onclick="showAddPrefixModal()">เพิ่มคำนำหน้า</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0" id="prefixTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>คำนำหน้า</th>
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

    <!-- Modal สำหรับเพิ่ม/แก้ไขคำนำหน้า -->
    <div class="modal fade" id="prefixModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prefixModalTitle">เพิ่มคำนำหน้า</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="prefixForm">
                        <input type="hidden" id="prefixId" name="prefixId">
                        <div class="form-group">
                            <label for="prefixName">คำนำหน้า</label>
                            <input type="text" class="form-control" id="prefixName" name="prefixName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="savePrefix()">บันทึก</button>
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
        loadPrefixes();
    });
    $('#prefixModal .btn-secondary').click(function() {
        $('#prefixModal').modal('hide');
    });
    $('#prefixModal .close').click(function() {
        $('#prefixModal').modal('hide');
    });
    function loadPrefixes() {
        $('#prefixTable').DataTable({
            ajax: {
                url: '../api/get_prefixes.php',
                dataSrc: 'data'
            },
            columns: [
                { data: 'prefix_id' },
                { data: 'prefix' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<button class="btn btn-sm btn-primary" onclick="editPrefix(' + row.prefix_id + ', \'' + row.prefix + '\')">แก้ไข</button> ' +
                               '<button class="btn btn-sm btn-danger" onclick="deletePrefix(' + row.prefix_id + ')">ลบ</button>';
                    }
                }
            ]
        });
    }


    function showAddPrefixModal() {
        $('#prefixId').val('');
        $('#prefixName').val('');
        $('#prefixModalTitle').text('เพิ่มคำนำหน้า');
        $('#prefixModal').modal('show');
    }

    function editPrefix(id, name) {
        $('#prefixId').val(id);
        $('#prefixName').val(name);
        $('#prefixModalTitle').text('แก้ไขคำนำหน้า');
        $('#prefixModal').modal('show');
    }


    function savePrefix() {
        var id = $('#prefixId').val();
        var name = $('#prefixName').val();
        var url = id ? '../system/update_prefix.php' : '../system/add_prefix.php';

        $.ajax({
            url: url,
            type: 'POST',
            data: { id: id, name: name },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('สำเร็จ', response.message, 'success');
                    $('#prefixModal').modal('hide');
                    $('#prefixTable').DataTable().ajax.reload();
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการดำเนินการ', 'error');
            }
        });
    }



    function deletePrefix(id) {
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: "คุณแน่ใจหรือไม่ที่จะลบคำนำหน้านี้?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../system/delete_prefix.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('ลบสำเร็จ', response.message, 'success');
                            $('#prefixTable').DataTable().ajax.reload();
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