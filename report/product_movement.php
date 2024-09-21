<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายงานการเคลื่อนไหวสินค้า</title>

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
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">รายงานการเคลื่อนไหวสินค้า</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">รายงานการเคลื่อนไหวสินค้า</li>
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
                                    <input type="date" id="startDate" class="form-control" placeholder="วันที่เริ่มต้น">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" id="endDate" class="form-control" placeholder="วันที่สิ้นสุด">
                                </div>
                                <div class="col-md-3">
                                    <select id="productFilter" class="form-control select2">
                                        <option value="">เลือกสินค้า</option>
                                        <!-- Options will be populated by JavaScript -->
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button id="exportExcel" class="btn btn-primary">Export to Excel</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="movementTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>วันที่</th>
                                            <th>รหัสสินค้า</th>
                                            <th>ชื่อสินค้า</th>
                                            <th>ประเภทการเคลื่อนไหว</th>
                                            <th>จำนวน</th>
                                            <th>คลังสินค้า</th>
                                            <th>ผู้ดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be populated by JavaScript -->
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
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
    $(document).ready(function() {
    $('.select2').select2();

    // Initialize DataTable
    var table = $('#movementTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/report/get_product_movement.php',
            type: 'POST',
            data: function(d) {
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
                d.productId = $('#productFilter').val();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'product_id' },
            { data: 'product_name' },
            { data: 'movement_type' },
            { data: 'quantity' },
            { data: 'location' },
            { data: 'user' }
        ],
        order: [[0, 'desc']]
    });

    // Reload table when filters change
    $('#startDate, #endDate, #productFilter').change(function() {
        table.ajax.reload();
    });

    // Load product options
    $.ajax({
        url: '../api/get_products.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var options = '<option value="">เลือกสินค้า</option>';
                $.each(response.data, function(index, product) {
                    options += '<option value="' + product.product_id + '">' + product.name_th + ' (' + product.product_id + ')</option>';
                });
                $('#productFilter').html(options);
            }
        }
    });

    // Export to Excel
    $('#exportExcel').click(function() {
        $.ajax({
            url: '../api/report/get_product_movement.php',
            type: 'POST',
            data: {
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val(),
                productId: $('#productFilter').val(),
                export: true
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var wb = XLSX.utils.book_new();
                    var ws = XLSX.utils.json_to_sheet(response.data);
                    XLSX.utils.book_append_sheet(wb, ws, "Product Movement");
                    XLSX.writeFile(wb, "ProductMovementReport.xlsx");
                } else {
                    alert('เกิดข้อผิดพลาดในการ export ข้อมูล');
                }
            }
        });
    });
});
</script>
</body>
</html>