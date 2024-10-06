<?php
require_once '../config/permission.php';
requirePermission(['manage_receiving']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>ประวัติการรับสินค้า</title>
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
                        <h3 class="page-title">ประวัติการรับสินค้า</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="receiveHistoryTable" class="table table-hover table-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label>
                                            </th>
                                            <th>เลขที่รับ</th>
                                            <th>วันที่รับ</th>
                                            <th>รายการสินค้า</th>
                                            <th>ผู้รับ</th>
                                            <th>สถานะ</th>
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
    var receiveHistoryTable = $('#receiveHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/get_receive_history.php',
            type: 'POST'
        },
        columns: [
            { 
                data: null,
                render: function (data, type, row) {
                    return '<label class="checkboxs"><input type="checkbox" value="' + row.receive_header_id + '"><span class="checkmarks"></span></label>';
                },
                orderable: false,
                searchable: false
            },
            { data: 'bill_number' },
            { data: 'received_date' },
            { data: 'items' },
            { data: 'user_name' },
            { data: 'status' },
            { 
                data: null,
                render: function (data, type, row) {
                    return '<a href="receive_details.php?id=' + row.receive_header_id + '" class="me-3"><img src="../assets/img/icons/eye.svg" alt="รายละเอียด"></a>' +
       '<a href="edit_receive.php?id=' + row.receive_header_id + '" class="me-3"><img src="../assets/img/icons/edit.svg" alt="แก้ไข"></a>' +
       '<img src="../assets/img/icons/delete.svg" alt="ลบ" class="delete-receive" data-id="' + row.receive_header_id + '" style="cursor: pointer;">';
                },
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'desc']]
    });

    // Handle "Select All" checkbox
    $('#select-all').on('click', function () {
        var rows = receiveHistoryTable.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // Handle delete button click
    $('#receiveHistoryTable').on('click', '.delete-receive', function() {
        var receiveId = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: "คุณแน่ใจหรือไม่ที่จะลบรายการรับสินค้านี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteReceive(receiveId);
            }
        });
    });
    function deleteReceive(receiveId) {
        $.ajax({
            url: '../system/delete_receive.php',
            type: 'POST',
            data: { id: receiveId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire(
                        'ลบสำเร็จ!',
                        'รายการรับสินค้าได้ถูกลบแล้ว',
                        'success'
                    );
                    receiveHistoryTable.ajax.reload();
                } else {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        response.message,
                        'error'
                    );
                }
            },
            error: function() {
                Swal.fire(
                    'เกิดข้อผิดพลาด!',
                    'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    'error'
                );
            }
        });
    }
            function showReceiveDetails(receiveId) {
                $.ajax({
                    url: '../api/get_receive_details.php',
                    type: 'GET',
                    data: { id: receiveId },
                    success: function(response) {
                        if (response.status === 'success') {
                            var detailsHtml = '<h4>รายละเอียดการรับสินค้า</h4>';
                            detailsHtml += '<p>เลขที่รับ: ' + response.data.bill_number + '</p>';
                            detailsHtml += '<p>วันที่รับ: ' + response.data.received_date + '</p>';
                            detailsHtml += '<p>ผู้รับ: ' + response.data.user_name + '</p>';
                            detailsHtml += '<h5>รายการสินค้า:</h5>';
                            detailsHtml += '<ul>';
                            response.data.items.forEach(function(item) {
                                detailsHtml += '<li>' + item.product_name + ' - จำนวน: ' + item.quantity + ' ' + item.unit + '</li>';
                            });
                            detailsHtml += '</ul>';

                            Swal.fire({
                                title: 'รายละเอียดการรับสินค้า',
                                html: detailsHtml,
                                icon: 'info',
                                confirmButtonText: 'ปิด'
                            });
                        } else {
                            Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    }
                });
            }
        });
    </script>
</body>

</html>