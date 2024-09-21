<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>คลังสินค้า</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
</head>

<body>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">คลังสินค้า</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="inventoryTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr> 
                                            <th>รูปสินค้า</th>
                                            <th>รหัสสินค้า</th>
                                            <th>ชื่อสินค้า (ไทย)</th>
                                            <th>จำนวนสินค้า</th>
                                            <th>หน่วย</th>
                                            <th>เลขบิลการรับ ต้องลบออกเพราะไว้ในประวัติการรับ</th>
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
    var inventoryTable = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/get_inventory.php',
            type: 'POST'
        },
        columns: [
            {
                "data": "image_url",
                "render": function (data, type, row) {
                    return '<img src="' + data + '" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">';
                }
            },
            { data: 'product_id' },
            { data: 'name_th' },
            { data: 'total_quantity' },
            { data: 'unit' },
            { data: 'bill_number' },
            {
                data: null,
                render: function (data, type, row) {
                    var url = '/inventory/locationInfo/' + encodeURIComponent(row.product_id);
                    return '<a href="' + url + '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> ดูรายละเอียด</a>';
                }
            }
        ],
        order: [[1, 'asc']]
    });
});
    </script>
</body>
</html>