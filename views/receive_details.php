<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายละเอียดการรับสินค้า</title>

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
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">รายละเอียดการรับสินค้า</h3>
                    </div>
                    <div class="col-auto">
                        <a href="receive_list.php" class="btn btn-secondary">กลับไปยังรายการ</a>
                        <button id="printButton" class="btn btn-primary">พิมพ์รายการ</button>
                    </div>
                </div>
            </div>

            <div id="alertMessage" class="alert" style="display:none;"></div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>เลขที่เอกสารรับสินค้า:</label>
                                        <p id="billNumber" class="form-control-static"></p>
                                    </div>
                                    <div class="form-group">
                                        <label>วันที่รับสินค้า:</label>
                                        <p id="receivedDate" class="form-control-static"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ผู้รับสินค้า:</label>
                                        <p id="receiverName" class="form-control-static"></p>
                                    </div>
                                    <div class="form-group">
                                        <label>สถานะ:</label>
                                        <p id="status" class="form-control-static"></p>
                                    </div>
                                    <div class="form-group">
                                        <label>สถานะการตรวจสอบ:</label>
                                        <p id="inspectionStatus" class="form-control-static"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h4>รายการสินค้าที่รับ</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ชื่อสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>หน่วย</th>
                                                    <th>สถานที่จัดเก็บ</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsTable">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-right"><strong>รวม</strong></td>
                                                    <td id="totalQuantity"><strong></strong></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
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
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const receiveId = urlParams.get('id');

            if (receiveId) {
                loadReceiveDetails(receiveId);
            } else {
                showAlert('ไม่พบรหัสการรับสินค้า', 'danger');
            }

            $('#printButton').click(printReceiveDetails);
        });

        function loadReceiveDetails(receiveId) {
            $.ajax({
                url: '../api/get_receive_details.php',
                type: 'GET',
                data: { id: receiveId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        displayReceiveDetails(response.data);
                    } else {
                        showAlert('เกิดข้อผิดพลาด: ' + response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('ไม่สามารถโหลดข้อมูลได้', 'danger');
                }
            });
        }

        function displayReceiveDetails(data) {
            $('#billNumber').text(data.bill_number);
            $('#receivedDate').text(data.received_date);
            $('#receiverName').text(data.user_name);
            $('#status').text(data.status);
            $('#inspectionStatus').text(data.inspection_status || 'ไม่มีข้อมูล');


            const itemsTable = $('#itemsTable');
            itemsTable.empty();
            
            let totalQuantity = 0;
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
                totalQuantity += parseFloat(item.quantity);
            });
            $('#totalQuantity').text(totalQuantity.toFixed(2));
        }

        function setStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'completed':
                    return '<span class="badge badge-success">เสร็จสมบูรณ์</span>';
                case 'pending':
                    return '<span class="badge badge-warning">รอดำเนินการ</span>';
                default:
                    return '<span class="badge badge-secondary">' + status + '</span>';
            }
        }

        function showAlert(message, type) {
            const alertDiv = $('#alertMessage');
            alertDiv.removeClass('alert-success alert-danger alert-warning')
                    .addClass('alert-' + type)
                    .text(message)
                    .show();
        }

        function printReceiveDetails() {
    const receiveId = new URLSearchParams(window.location.search).get('id');
    if (receiveId) {
        window.open(`../report/generate_receive_pdf.php?receive_id=${receiveId}`, '_blank');
    } else {
        showAlert('ไม่พบรหัสการรับสินค้า', 'danger');
    }
}

    </script>
</body>

</html>