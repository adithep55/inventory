<?php
require_once '../config/permission.php';
requirePermission(['manage_transfers']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>ประวัติการโอนย้าย</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <h3 class="page-title">ประวัติการโอนย้าย</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="transferHistoryTable" class="table table-hover table-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>เลขบิล</th>
                                            <th>วันที่โอนย้าย</th>
                                            <th>จากคลัง</th>
                                            <th>ไปคลัง</th>
                                            <th>จำนวนรายการ</th>
                                            <th>ปริมาณรวม</th>
                                            <th>ผู้ดำเนินการ</th>
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

    <script>
        $(document).ready(function () {
            var transferHistoryTable = $('#transferHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '../api/get_transfer_history.php',
                    type: 'POST'
                },
                columns: [
                    { data: 'bill_number' },
                    { data: 'transfer_date' },
                    { data: 'from_location' },
                    { data: 'to_location' },
                    { data: 'item_count' },
                    { data: 'total_quantity' },
                    { data: 'username' },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']]
            });

            $(document).on('click', '.delete-transfer', function() {
                var transferId = $(this).data('id');
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: "คุณกำลังจะลบรายการโอนย้ายนี้ การดำเนินการนี้ไม่สามารถย้อนกลับได้!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../system/delete_transfer.php',
                            type: 'POST',
                            data: { transfer_id: transferId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire(
                                        'ลบแล้ว!',
                                        'รายการโอนย้ายถูกลบเรียบร้อยแล้ว',
                                        'success'
                                    ).then(() => {
                                        transferHistoryTable.ajax.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'เกิดข้อผิดพลาด!',
                                        'ไม่สามารถลบรายการโอนย้ายได้: ' + response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error:", status, error);
                                console.error("Response Text:", xhr.responseText);
                                Swal.fire(
                                    'เกิดข้อผิดพลาด!',
                                    'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์: ' + error,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>