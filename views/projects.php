<?php
require_once '../config/permission.php';
requirePermission(['manage_projects']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>จัดการโครงการ</title>
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
                    <h4>จัดการโครงการ</h4>
                    <h6>ดูและจัดการโครงการของคุณ</h6>
                </div>
                <div class="page-btn">
                    <a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add_project">
                        <img src="../assets/img/icons/plus.svg" alt="img" class="me-1">เพิ่มโครงการใหม่
                    </a>
                </div>
            </div>
            <div class="card">
                        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0" id="projectsTable">
                            <thead>
                                <tr>
                                    <th>รหัสโครงการ</th>
                                    <th>ชื่อโครงการ</th>
                                    <th>รายละเอียด</th>
                                    <th>วันที่เริ่ม</th>
                                    <th>วันที่สิ้นสุด</th>
                                    <th>ผู้รับผิดชอบ</th>
                                    <th>การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ข้อมูลโครงการจะถูกเพิ่มที่นี่โดย JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
    </div>
    </div>
    <!-- Modal เพิ่มโครงการ -->
    <div class="modal fade" id="add_project" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel">เพิ่มโครงการใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add_project_form">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>ชื่อโครงการ <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="project_name" id="project_name" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>ผู้รับผิดชอบ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="user_id" id="user_id" required>
                                        <option value="">เลือกผู้รับผิดชอบ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>รายละเอียดโครงการ</label>
                            <textarea rows="4" class="form-control" name="project_description" id="project_description"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>วันที่เริ่ม <span class="text-danger">*</span></label>
                                    <input class="form-control" type="date" name="start_date" id="start_date" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>วันที่สิ้นสุด <span class="text-danger">*</span></label>
                                    <input class="form-control" type="date" name="end_date" id="end_date" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="submit_project">บันทึก</button>
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
        $('.close').on('click', function() {
        $('#add_project').modal('hide');
    });

    var projectsTable = $('#projectsTable').DataTable({
        ajax: {
            url: '../api/get_projects.php',
            dataSrc: function(json) {
 
                if (json.status === 'success' && json.data) {
                    return json.data;
                } else {
                    console.error('Error fetching projects:', json.message);
                    return [];
                }
            }
        },
        columns: [
            { data: 'project_id' },
            { data: 'project_name' },
            { data: 'project_description', defaultContent: '' },
            { data: 'start_date' },
            { data: 'end_date' },
            { data: 'user_name', defaultContent: '' },
            {
                data: null,
                render: function(data, type, row) {
                    return '<a href="edit_project.php?id=' + row.project_id + '" class="me-3 edit-project" data-id="' + row.project_id + '"><img src="../assets/img/icons/edit.svg" alt="แก้ไข"></a>' +
                    '<img src="../assets/img/icons/delete.svg" alt="ลบ" class="delete-project" data-id="' + row.project_id + '" style="cursor: pointer;">';
                }
            }
        ]
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

    $('.select').select2({
        dropdownParent: $('#add_project')
    });

    loadUsers();
    setupDateInputs();

    // Event Listeners
    $('.add-btn').on('click', function(e) {
        e.preventDefault();
        $('#add_project').modal('show');
    });

    $('#add_project').on('show.bs.modal', function (e) {
        console.log('Modal is showing');
    });

    $('#add_project').on('shown.bs.modal', function (e) {
        console.log('Modal is shown');
        $('select[name="user_id"]').select2({
            dropdownParent: $('#add_project')
        });
    });

    $('#add_project_form').on('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            addProject();
        }
    });

    function setupDateInputs() {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const maxDate = today.toISOString().split('T')[0]; 

    $('#start_date').attr('max', maxDate);

    $('#start_date').on('change', function() {
        const selectedStartDate = new Date($(this).val());
        if (selectedStartDate > today) { 
            $(this).val(maxDate);
            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเลือกวันที่เริ่มโครงการในอนาคตได้', 'error');
        }
        $('#end_date').attr('min', $(this).val());
    });

    $('#end_date').on('change', function() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($(this).val());
        if (endDate < startDate) {
            $(this).val($('#start_date').val());
            Swal.fire('ข้อผิดพลาด', 'วันที่สิ้นสุดโครงการต้องไม่น้อยกว่าวันที่เริ่มโครงการ', 'error');
        }
    });
}

function loadUsers() {
    $.ajax({
        url: '../api/get_users.php?for_project_dropdown=true',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data && response.data.length > 0) {
                var options = '<option value="">เลือกผู้รับผิดชอบ</option>';
                $.each(response.data, function(i, user) {
                    options += '<option value="' + user.UserID + '">' + user.full_name + '</option>';
                });
                $('#user_id').html(options);
            } else {
                console.error('Failed to load users: No data returned');
                Swal.fire('Error', 'ไม่พบข้อมูลผู้ใช้', 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error loading users:', textStatus, errorThrown);
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้', 'error');
        }
    });
}

    function validateForm() {
        var isValid = true;
        var errorMessages = [];

        // ตรวจสอบชื่อโครงการ
        if ($('#project_name').val().trim() === '') {
            isValid = false;
            errorMessages.push('กรุณากรอกชื่อโครงการ');
            $('#project_name').addClass('is-invalid');
        } else {
            $('#project_name').removeClass('is-invalid');
        }

        // ตรวจสอบผู้รับผิดชอบ
        if ($('#user_id').val() === '') {
            isValid = false;
            errorMessages.push('กรุณาเลือกผู้รับผิดชอบ');
            $('#user_id').next('.select2-container').addClass('is-invalid');
        } else {
            $('#user_id').next('.select2-container').removeClass('is-invalid');
        }

        // ตรวจสอบวันที่เริ่มโครงการ
        if ($('#start_date').val() === '') {
            isValid = false;
            errorMessages.push('กรุณาเลือกวันที่เริ่มโครงการ');
            $('#start_date').addClass('is-invalid');
        } else {
            $('#start_date').removeClass('is-invalid');
        }

        // ตรวจสอบวันที่สิ้นสุดโครงการ
        if ($('#end_date').val() === '') {
            isValid = false;
            errorMessages.push('กรุณาเลือกวันที่สิ้นสุดโครงการ');
            $('#end_date').addClass('is-invalid');
        } else {
            $('#end_date').removeClass('is-invalid');
        }

        // ตรวจสอบว่าวันที่สิ้นสุดต้องมาหลังวันที่เริ่ม
        if ($('#start_date').val() !== '' && $('#end_date').val() !== '') {
            if (new Date($('#start_date').val()) > new Date($('#end_date').val())) {
                isValid = false;
                errorMessages.push('วันที่สิ้นสุดโครงการต้องมาหลังวันที่เริ่มโครงการ');
                $('#end_date').addClass('is-invalid');
            }
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'กรุณาตรวจสอบข้อมูล',
                html: errorMessages.join('<br>'),
            });
        }

        return isValid;
    }
    function resetProjectForm() {
    $('#project_name').val('');
    $('#user_id').val('').trigger('change');
    $('#project_description').val('');
    $('#start_date').val('');
    $('#end_date').val('');
    
    $('.is-invalid').removeClass('is-invalid');
}
function addProject() {
    var formData = $('#add_project_form').serialize();
    $.ajax({
        url: '../system/add_project.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('สำเร็จ', 'เพิ่มโครงการใหม่เรียบร้อยแล้ว', 'success')
                .then(() => {
                    $('#add_project').modal('hide');
                    projectsTable.ajax.reload();
                    resetProjectForm(); // เรียกใช้ฟังก์ชันรีเซ็ตฟอร์ม
                });
            } else {
                Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถเพิ่มโครงการได้', 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเพิ่มโครงการ', 'error');
        }
    });
}

    $('#projectsTable').on('click', '.delete-project', function(e) {
    e.preventDefault();
    var projectId = $(this).data('id');
    deleteProject(projectId);
});
    function deleteProject(projectId) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณไม่สามารถยกเลิกการดำเนินการนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../system/delete_project.php',
                type: 'POST',
                data: { project_id: projectId },  // เปลี่ยนจาก projectId เป็น project_id
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        projectsTable.ajax.reload();
                        Swal.fire('ลบแล้ว!', 'โครงการถูกลบเรียบร้อยแล้ว', 'success');
                    } else {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถลบโครงการได้: ' + response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                }
            });
        }
    });
}
});
</script>
</body>
</html>