<?php
require_once '../config/permission.php';
requirePermission(['manage_inventory']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการตำแหน่งคลังสินค้า</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <style>
        .location-id-group {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .location-id-label {
            margin-right: 10px;
            white-space: nowrap;
        }

        .location-id-input-wrapper {
            position: relative;
            flex-grow: 1;
        }

        .location-id-input {
            width: 100%;
        }

        .btn-generate-small {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            white-space: nowrap;
            z-index: 10;
        }
    </style>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>
    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">จัดการตำแหน่งคลังสินค้า</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">เพิ่มตำแหน่งคลังสินค้าใหม่</h4>
                            <div class="card-body">
                                <form id="addLocationForm">
                                    <label for="locationId" class="location-id-label">รหัสคลัง</label>
                                    <div class="location-id-group">

                                        <div class="location-id-input-wrapper">
                                            <input type="text" class="form-control location-id-input" id="locationId"
                                                name="locationId" required>
                                            <button class="btn btn-outline-secondary btn-generate-small" type="button"
                                                id="generateId">
                                                <i class="fas fa-magic"></i> สร้างรหัส
                                            </button>
                                            <button class="btn btn-outline-secondary btn-generate-small" type="button"
                                                id="regenerateId">
                                                <i class="fas fa-magic"></i> สร้างรหัส
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="locationName">ชื่อคลัง</label>
                                        <input type="text" class="form-control" id="locationName" name="locationName"
                                            required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">บันทึก</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">รายการตำแหน่งคลังสินค้า</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>รหัสคลัง</th>
                                                <th>ชื่อคลัง</th>
                                                <th>การดำเนินการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="locationTableBody">
                                            <!-- ข้อมูลจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal สำหรับแก้ไขตำแหน่งคลัง -->
        <div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขตำแหน่งคลัง</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editLocationForm">
                            <input type="hidden" id="oldLocationId" name="old_location_id">
                            <div class="form-group">
                                <label for="editLocationId">รหัสคลัง</label>
                                <input type="text" class="form-control" id="editLocationId" name="editLocationId"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="editLocationName">ชื่อคลัง</label>
                                <input type="text" class="form-control" id="editLocationName" name="editLocationName"
                                    required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-primary" id="saveLocationEdit">บันทึก</button>
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

            $(document).ready(function () {
                function generateLocationId() {
                    $.ajax({
                        url: '../system/gen_locat.php',
                        type: 'GET',
                        dataType: 'json',
                        cache: false,
                        data: { _: new Date().getTime() },
                        success: function (response) {
                            console.log("Server response:", response); // Log ค่าที่ได้รับจากเซิร์ฟเวอร์
                            if (response && response.status === 'success' && response.generated_id) {
                                $('#locationId').val(response.generated_id);
                                $('#generateId').hide();
                                $('#regenerateId').show();
                            } else {
                                console.error("Invalid server response:", response);
                                Swal.fire('ข้อผิดพลาด', 'การตอบกลับจากเซิร์ฟเวอร์ไม่ถูกต้อง', 'error');
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error("AJAX error:", textStatus, errorThrown);
                            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                        }
                    });
                }

                $('#generateId').click(generateLocationId);

                $('#regenerateId').click(function () {
                    generateLocationId();
                });

                $('#locationId').on('input', function () {
                    if ($(this).val() === '') {
                        $('#generateId').show();
                        $('#regenerateId').hide();
                    } else {
                        $('#generateId').hide();
                        $('#regenerateId').show();
                    }
                });
                // โหลดข้อมูลตำแหน่งคลัง
                function loadLocations() {
                    $.ajax({
                        url: '../api/get_locations.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                var tableBody = $('#locationTableBody');
                                tableBody.empty();
                                if (response.data && response.data.length > 0) {
                                    $.each(response.data, function (index, location) {
                                        tableBody.append(`
                            <tr>
                                <td>${location.location_id}</td>
                                <td>${location.location}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-location" data-id="${location.location_id}" data-name="${location.location}">แก้ไข</button>
                                    <button class="btn btn-sm btn-danger delete-location" data-id="${location.location_id}">ลบ</button>
                                </td>
                            </tr>
                        `);
                                    });
                                } else {
                                    tableBody.append(`
                        <tr>
                            <td colspan="3" class="text-center">ไม่พบข้อมูลสถานที่</td>
                        </tr>
                    `);
                                }
                            } else {
                                console.error('Error loading locations:', response.message);
                                $('#locationTableBody').html(`
                    <tr>
                        <td colspan="3" class="text-danger text-center">ไม่พบข้อมูลสถานที่</td>
                    </tr>
                `);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error fetching locations:", error);
                            $('#locationTableBody').html(`
                <tr>
                    <td colspan="3" class="text-center">เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์</td>
                </tr>
            `);
                        }
                    });
                }

                // เพิ่มตำแหน่งคลังใหม่
                $('#addLocationForm').submit(function (e) {
                    e.preventDefault();
                    $.ajax({
                        url: '../system/add_location.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire('สำเร็จ', 'เพิ่มตำแหน่งคลังเรียบร้อยแล้ว', 'success');
                                $('#addLocationForm')[0].reset();
                                loadLocations();
                            } else {
                                Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถเพิ่มตำแหน่งคลังได้', 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error adding location:", error);
                            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเพิ่มตำแหน่งคลัง', 'error');
                        }
                    });
                });

                // เปิด Modal แก้ไขตำแหน่งคลัง
                $(document).on('click', '.edit-location', function () {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    $('#oldLocationId').val(id);
                    $('#editLocationId').val(id);
                    $('#editLocationName').val(name);
                    $('#editLocationModal').modal('show');
                });

                // บันทึกการแก้ไขตำแหน่งคลัง
                $('#saveLocationEdit').click(function () {
                    var formData = {
                        old_location_id: $('#oldLocationId').val(),
                        editLocationId: $('#editLocationId').val(),
                        editLocationName: $('#editLocationName').val()
                    };

                    $.ajax({
                        url: '../system/update_location.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'confirm') {
                                Swal.fire({
                                    title: 'ยืนยันการอัปเดต',
                                    text: response.message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'ใช่, อัปเดตทั้งหมด',
                                    cancelButtonText: 'ยกเลิก'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        formData.confirm_update = 'true';
                                        $.ajax({
                                            url: '../system/update_location.php',
                                            type: 'POST',
                                            data: formData,
                                            dataType: 'json',
                                            success: function (updateResponse) {
                                                if (updateResponse.status === 'success') {
                                                    Swal.fire('สำเร็จ', updateResponse.message, 'success');
                                                    $('#editLocationModal').modal('hide');
                                                    loadLocations();
                                                } else {
                                                    Swal.fire('ข้อผิดพลาด', updateResponse.message, 'error');
                                                }
                                            },
                                            error: function () {
                                                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล', 'error');
                                            }
                                        });
                                    }
                                });
                            } else if (response.status === 'success') {
                                Swal.fire('สำเร็จ', response.message, 'success');
                                $('#editLocationModal').modal('hide');
                                loadLocations();
                            } else {
                                Swal.fire('ข้อผิดพลาด', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                        }
                    });
                });
                // ลบตำแหน่งคลัง
                $(document).on('click', '.delete-location', function () {
                    var id = $(this).data('id');
                    Swal.fire({
                        title: 'คุณแน่ใจหรือไม่?',
                        text: "คุณต้องการลบตำแหน่งคลังนี้หรือไม่?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ใช่, ลบเลย!',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '../system/delete_location.php',
                                type: 'POST',
                                data: { id: id },
                                dataType: 'json',
                                success: function (response) {
                                    if (response.status === 'success') {
                                        Swal.fire('สำเร็จ', 'ลบตำแหน่งคลังเรียบร้อยแล้ว', 'success');
                                        loadLocations();
                                    } else {
                                        Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถลบตำแหน่งคลังได้', 'error');
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error("Error deleting location:", error);
                                    Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการลบตำแหน่งคลัง', 'error');
                                    loadLocations();
                                }
                            });
                        }
                    });
                });

                // โหลดข้อมูลตำแหน่งคลังเมื่อโหลดหน้า
                loadLocations();

            });
            //ปุ่มปิดModal
            $('#editLocationModal .btn-secondary').on('click', function () {
                $('#editLocationModal').modal('hide');
            });

            $('#editLocationModal .close').on('click', function () {
                $('#editLocationModal').modal('hide');
            });
        </script>
</body>

</html>