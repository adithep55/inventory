<?php
require_once '../config/permission.php';
requirePermission(['manage_issue']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>ประวัติการเบิกสินค้า</title>
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
                <div class="page-title">
                    <h4><i class="fas fa-history"></i> การเบิกสินค้า</h4>
                    <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url();?>">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url();?>/views/issue">เบิกสินค้า</a></li>
                            <li class="breadcrumb-item active">จัดการประวัติการเบิกสินค้า</li>
               </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="issueHistoryTable" class="table table-hover table-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label>
                                            </th>
                                            <th>เลขที่เบิก</th>
                                            <th>วันที่เบิก</th>
                                            <th>ประเภทการเบิก</th>
                                            <th>ลูกค้า/โครงการ</th>
                                            <th>รายการสินค้า</th>
                                            <th>ผู้เบิก</th>
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
            var issueHistoryTable = $('#issueHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '../api/get_issue_history.php',
                    type: 'POST'
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row) {
                            return '<label class="checkboxs"><input type="checkbox" value="' + row.issue_id + '"><span class="checkmarks"></span></label>';
                        },
                        orderable: false,
                        searchable: false
                    },
                    { data: 'bill_number' },
                    { data: 'issue_date' },
                    { data: 'issue_type' },
                    { data: 'customer_project' },
                    { data: 'items' },
                    { data: 'user_name' },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return `
            <a href="issue_details.php?id=${row.issue_id}" class="me-3">
                <img src="../assets/img/icons/eye.svg" alt="img">
            </a>
            <a href="edit_issue.php?id=${row.issue_id}"><img src="../assets/img/icons/edit.svg" alt="img" class="me-3"></a>
           <img src="../assets/img/icons/delete.svg" alt="ลบ" class="delete-issue" data-id="${row.issue_id}" style="cursor: pointer;">
        `;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[1, 'desc']]
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

            // Handle "Select All" checkbox
            $('#select-all').on('click', function () {
                var rows = issueHistoryTable.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // Handle view details button click
            $('#issueHistoryTable').on('click', '.view-details', function (e) {
                e.preventDefault();
                var issueId = $(this).data('id');
                showIssueDetails(issueId);
            });


            function showIssueDetails(issueId) {
                $.ajax({
                    url: '../api/get_issue_details.php',
                    type: 'GET',
                    data: { id: issueId },
                    success: function (response) {
                        if (response.status === 'success') {
                            var detailsHtml = '<h4>รายละเอียดการเบิก</h4>';
                            detailsHtml += '<p>เลขที่เบิก: ' + response.data.bill_number + '</p>';
                            detailsHtml += '<p>วันที่เบิก: ' + response.data.issue_date + '</p>';
                            detailsHtml += '<p>ประเภทการเบิก: ' + response.data.issue_type + '</p>';
                            detailsHtml += '<p>ผู้เบิก: ' + response.data.user_name + '</p>';
                            detailsHtml += '<h5>รายการสินค้า:</h5>';
                            detailsHtml += '<ul>';
                            response.data.items.forEach(function (item) {
                                detailsHtml += '<li>' + item.product_name + ' - จำนวน: ' + item.quantity + ' ' + item.unit + '</li>';
                            });
                            detailsHtml += '</ul>';

                            Swal.fire({
                                title: 'รายละเอียดการเบิก',
                                html: detailsHtml,
                                icon: 'info',
                                confirmButtonText: 'ปิด'
                            });
                        } else {
                            Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    }
                });
            }
            $('#issueHistoryTable').on('click', '.delete-issue', function () {
                var issueId = $(this).data('id');
                deleteIssue(issueId);
            });
            function deleteIssue(issueId) {
                Swal.fire({
                    title: 'ยืนยันการลบ',
                    text: "คุณแน่ใจหรือไม่ที่จะลบรายการเบิกนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../system/delete_issue.php',
                            type: 'POST',
                            data: { id: issueId },
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire(
                                        'ลบสำเร็จ!',
                                        'รายการเบิกถูกลบและคลังสินค้าถูกปรับปรุงแล้ว',
                                        'success'
                                    ).then(() => {
                                        issueHistoryTable.ajax.reload();
                                    });
                                } else {
                                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถลบรายการได้', 'error');
                            }
                        });
                    }
                });
            }
        });

    </script>
</body>

</html>