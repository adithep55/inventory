<!DOCTYPE html>
<html lang="th">

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>รายงานสรุปสินค้าคงเหลือ</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- เพิ่ม CSS สำหรับ datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">รายงานสรุปสินค้าคงเหลือ</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active">รายงานสรุปสินค้าคงเหลือ</li>
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
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select id="categoryFilter" class="form-control select2">
                                        <option value="">ทุกหมวดหมู่</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select id="subCategoryFilter" class="form-control select2" disabled>
                                        <option value="">ทุกหมวดหมู่ย่อย</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="selectedDate" class="form-control" placeholder="เลือกวันที่">
                                </div>
                                <div class="col-md-3">
                                    <button id="exportExcel" class="btn btn-primary">Export to Excel</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                               <table id="inventoryTable" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>รหัสสินค้า</th>
            <th>ชื่อสินค้า (ไทย)</th>
            <th>ชื่อสินค้า (อังกฤษ)</th>
            <th>หมวดหมู่</th>
            <th>คลังสินค้า</th>
            <th>จำนวนคงเหลือ</th>
            <th>หน่วย</th>
            <th>รายละเอียดการเคลื่อนไหว</th>
        </tr>
    </thead>
                                    <tbody>
                                        <!-- ข้อมูลจะถูกเพิ่มโดย JavaScript -->
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
    <!-- เพิ่ม JavaScript สำหรับ datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
$(document).ready(function () {
    $('.select2').select2();

    $('#selectedDate').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        language: 'th',
        todayHighlight: true
    });

    var table = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/report/get_inventory_summary.php',
            type: 'POST',
            data: function (d) {
                d.location = $('#locationFilter').val();
                d.category = $('#categoryFilter').val();
                d.subCategory = $('#subCategoryFilter').val();
                d.selectedDate = $('#selectedDate').val();
            }
        },
        columns: [
            { data: 'product_id' },
            { data: 'name_th' },
            { data: 'name_en' },
            { data: 'category' },
            { data: 'location' },
            { data: 'current_quantity' },
            { data: 'unit' },
            {
    data: 'movements',
    render: function (data, type, row) {
        if (type === 'display') {
            if (!data || data.length === 0) {
                return 'ไม่มีข้อมูลการเคลื่อนไหว';
            }
            try {
                var movements = Array.isArray(data) ? data : JSON.parse(data);
                var movementHtml = '<ul>';
                movements.forEach(function(movement) {
                    var typeText = {
                        'receive': 'รับเข้า',
                        'issue': 'เบิกออก',
                        'transfer_out': 'โอนออก',
                        'transfer_in': 'โอนเข้า'
                    }[movement.type] || movement.type;
                    
                    movementHtml += '<li>' + movement.date + ': ' + typeText + 
                        ' (' + movement.bill_number + ') - จำนวน: ' + movement.quantity + '</li>';
                });
                movementHtml += '</ul>';
                return movementHtml;
            } catch (e) {
                console.error('Error parsing movements data:', e, data);
                return 'ข้อมูลไม่ถูกต้อง';
            }
        }
        return data;
    }
}
        ],
        order: [[0, 'asc']]
    });

    $('#locationFilter, #categoryFilter, #subCategoryFilter, #selectedDate').change(function () {
        table.ajax.reload();
    });
           

            $('#exportExcel').click(function () {
                $.ajax({
                    url: '../api/report/get_inventory_summary.php',
                    type: 'POST',
                    data: {
                        location: $('#locationFilter').val(),
                        category: $('#categoryFilter').val(),
                        subCategory: $('#subCategoryFilter').val(),
                        selectedDate: $('#selectedDate').val(),
                        export: true
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success' && Array.isArray(response.data)) {
                            const workbook = new ExcelJS.Workbook();
                            const worksheet = workbook.addWorksheet('รายงานสรุปสินค้าคงเหลือ');

                            const headers = ['รหัสสินค้า', 'ชื่อสินค้า (ไทย)', 'ชื่อสินค้า (อังกฤษ)', 'หมวดหมู่', 'คลังสินค้า', 'จำนวนรวม', 'หน่วย'];
                            const headerRow = worksheet.addRow(headers);

                            headerRow.eachCell((cell) => {
                                cell.fill = {
                                    type: 'pattern',
                                    pattern: 'solid',
                                    fgColor: { argb: 'FFD9D9D9' }
                                };
                                cell.font = { bold: true, color: { argb: 'FF000000' } };
                                cell.alignment = { vertical: 'middle', horizontal: 'center' };
                                cell.border = {
                                    top: { style: 'thin' },
                                    left: { style: 'thin' },
                                    bottom: { style: 'thin' },
                                    right: { style: 'thin' }
                                };
                            });

                            response.data.forEach(item => {
                                const row = worksheet.addRow(Object.values(item));
                                row.eachCell((cell) => {
                                    cell.border = {
                                        top: { style: 'thin' },
                                        left: { style: 'thin' },
                                        bottom: { style: 'thin' },
                                        right: { style: 'thin' }
                                    };
                                    if (typeof cell.value === 'number') {
                                        cell.numFmt = '#,##0';
                                    }
                                });
                            });

                            worksheet.columns = [
                                { width: 15 },
                                { width: 30 },
                                { width: 30 },
                                { width: 20 },
                                { width: 30 },
                                { width: 15 },
                                { width: 10 }
                            ];

                            workbook.xlsx.writeBuffer().then(buffer => {
                                const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = 'รายงานสรุปสินค้าคงเหลือ.xlsx';
                                a.click();
                                window.URL.revokeObjectURL(url);
                            });
                        } else {
                            console.error('Invalid data received for export:', response);
                            alert('เกิดข้อผิดพลาดในการ export ข้อมูล: ข้อมูลไม่ถูกต้อง');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error exporting data:', error);
                        alert('เกิดข้อผิดพลาดในการ export ข้อมูล: ' + error);
                    }
                });
            });

            $.ajax({
                url: '../api/get_product_types.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success' && Array.isArray(response.data)) {
                        var options = '<option value="">ทุกหมวดหมู่</option>';
                        $.each(response.data, function (index, category) {
                            if (category && category.type_id && category.name) {
                                options += '<option value="' + category.type_id + '">' + category.name + '</option>';
                            }
                        });
                        $('#categoryFilter').html(options);
                    } else {
                        console.error("Invalid data format received from API:", response);
                        $('#categoryFilter').html('<option value="">ไม่สามารถโหลดหมวดหมู่ได้</option>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error loading categories:", error);
                    $('#categoryFilter').html('<option value="">ไม่สามารถโหลดหมวดหมู่ได้</option>');
                }
            });

            $('#categoryFilter').change(function () {
                var selectedTypeId = $(this).val();
                if (selectedTypeId) {
                    $.ajax({
                        url: '../api/get_categories.php',
                        type: 'GET',
                        data: { type_id: selectedTypeId },
                        dataType: 'json',
                        success: function (data) {
                            var options = '<option value="">ทุกหมวดหมู่ย่อย</option>';
                            $.each(data, function (index, category) {
                                options += '<option value="' + category.category_id + '">' + category.name + '</option>';
                            });
                            $('#subCategoryFilter').html(options).prop('disabled', false);
                        },
                        error: function (xhr, status, error) {
                            console.error("Error loading sub-categories:", error);
                        }
                    });
                } else {
                    $('#subCategoryFilter').html('<option value="">ทุกหมวดหมู่ย่อย</option>').prop('disabled', true);
                }
                table.ajax.reload();
            });
            $.ajax({
                url: '../api/get_locations.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success' && Array.isArray(response.data)) {
                        var options = '<option value="">ทุกคลังสินค้า</option>';
                        $.each(response.data, function (index, location) {
                            options += '<option value="' + location.location_id + '">' + location.location + '</option>';
                        });
                        $('#locationFilter').html(options);
                    } else {
                        console.error("Invalid data format received from API:", response);
                        $('#locationFilter').html('<option value="">ไม่สามารถโหลดคลังสินค้าได้</option>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error loading locations:", error);
                    $('#locationFilter').html('<option value="">ไม่สามารถโหลดคลังสินค้าได้</option>');
                }
            });
            $('#locationFilter, #categoryFilter, #subCategoryFilter').change(function () {
                table.ajax.reload();
            });
        });
    </script>
</body>

</html>