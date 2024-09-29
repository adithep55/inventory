<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>แก้ไขข้อมูลโครงการ</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
</head>
<style>
    .is-invalid {
        border-color: #dc3545;
    }

    .select2-container--default.is-invalid .select2-selection--single {
        border-color: #dc3545;
    }
</style>

<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">แก้ไขข้อมูลโครงการ</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="projects.php">รายการโครงการ</a></li>
                            <li class="breadcrumb-item active">แก้ไขข้อมูลโครงการ</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="editProjectForm">
                                <input type="hidden" id="projectId" name="projectId">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ชื่อโครงการ</label>
                                            <input type="text" class="form-control" id="projectName" name="projectName"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ผู้รับผิดชอบ</label>
                                            <select class="select" id="userId" name="userId">
                                                <!-- จะถูกเติมด้วย JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>รายละเอียดโครงการ</label>
                                            <textarea class="form-control" id="projectDescription"
                                                name="projectDescription" rows="4"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>วันที่เริ่มโครงการ</label>
                                            <input type="date" class="form-control" id="startDate" name="startDate"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>วันที่สิ้นสุดโครงการ</label>
                                            <input type="date" class="form-control" id="endDate" name="endDate"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="history.back()">ยกเลิก</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        $(document).ready(function () {
            $('.select').select2();

            loadUsers();
            setupDateInputs();

            var projectId = new URLSearchParams(window.location.search).get('id');
            if (projectId) {
                loadProjectData(projectId);
            }

            $('#editProjectForm').on('submit', function (e) {
                e.preventDefault();
                if (validateForm()) {
                    updateProject();
                }
            });

            function setupDateInputs() {
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                const maxDate = tomorrow.toISOString().split('T')[0];

                $('#startDate').attr('max', maxDate);

                $('#startDate').on('change', function () {
                    const selectedStartDate = new Date($(this).val());
                    if (selectedStartDate > tomorrow) {
                        $(this).val(maxDate);
                        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเลือกวันที่เริ่มโครงการในอนาคตได้', 'error');
                    }
                    $('#endDate').attr('min', $(this).val());
                });

                $('#endDate').on('change', function () {
                    const startDate = new Date($('#startDate').val());
                    const endDate = new Date($(this).val());
                    if (endDate < startDate) {
                        $(this).val($('#startDate').val());
                        Swal.fire('ข้อผิดพลาด', 'วันที่สิ้นสุดโครงการต้องไม่น้อยกว่าวันที่เริ่มโครงการ', 'error');
                    }
                });
            }

            function loadUsers() {
                $.ajax({
                    url: '../api/get_users.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.data && response.data.length > 0) {
                            var options = '<option value="">เลือกผู้รับผิดชอบ</option>';
                            $.each(response.data, function (i, user) {
                                options += '<option value="' + user.UserID + '">' + user.full_name + '</option>';
                            });
                            $('#userId').html(options);
                        } else {
                            console.error('Failed to load users: No data returned');
                            Swal.fire('Error', 'ไม่พบข้อมูลผู้ใช้', 'error');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error loading users:', textStatus, errorThrown);
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้', 'error');
                    }
                });
            }

            function loadProjectData(projectId) {
                $.ajax({
                    url: '../api/get_projects.php',
                    type: 'GET',
                    data: { id: projectId },
                    dataType: 'json',
                    success: function (response) {
                        console.log('API Response:', response); // Log ข้อมูลที่ได้รับ
                        if (response.status === 'success' && response.data) {
                            var project = response.data;
                            console.log('Project Data:', project); // Log ข้อมูลโครงการ

                            $('#projectId').val(project.project_id);
                            $('#projectName').val(project.project_name);
                            $('#projectDescription').val(project.project_description);

                            // แปลงรูปแบบวันที่
                            var startDate = formatDateForInput(project.start_date);
                            var endDate = formatDateForInput(project.end_date);

                            $('#startDate').val(startDate);
                            $('#endDate').val(endDate);

                            $('#userId').val(project.user_id).trigger('change');
                        } else {
                            console.error('Failed to load project data:', response.message);
                            Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูลโครงการ: ' + (response.message || 'Unknown error'), 'error');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error:', textStatus, errorThrown);
                        Swal.fire('Error', 'เกิดข้อผิดพลาดขณะโหลดข้อมูลโครงการ: ' + textStatus, 'error');
                    }
                });
            }

            function formatDateForInput(dateString) {
                if (!dateString) return '';
                var parts = dateString.split('/');
                if (parts.length === 3) {
                    return parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                }
                return dateString;
            }

            function validateForm() {
                var isValid = true;
                var errorMessages = [];

                // ตรวจสอบชื่อโครงการ
                if ($('#projectName').val().trim() === '') {
                    isValid = false;
                    errorMessages.push('กรุณากรอกชื่อโครงการ');
                    $('#projectName').addClass('is-invalid');
                } else {
                    $('#projectName').removeClass('is-invalid');
                }

                // ตรวจสอบผู้รับผิดชอบ
                if ($('#userId').val() === '') {
                    isValid = false;
                    errorMessages.push('กรุณาเลือกผู้รับผิดชอบ');
                    $('#userId').next('.select2-container').addClass('is-invalid');
                } else {
                    $('#userId').next('.select2-container').removeClass('is-invalid');
                }

                // ตรวจสอบวันที่เริ่มโครงการ
                if ($('#startDate').val() === '') {
                    isValid = false;
                    errorMessages.push('กรุณาเลือกวันที่เริ่มโครงการ');
                    $('#startDate').addClass('is-invalid');
                } else {
                    $('#startDate').removeClass('is-invalid');
                }

                // ตรวจสอบวันที่สิ้นสุดโครงการ
                if ($('#endDate').val() === '') {
                    isValid = false;
                    errorMessages.push('กรุณาเลือกวันที่สิ้นสุดโครงการ');
                    $('#endDate').addClass('is-invalid');
                } else {
                    $('#endDate').removeClass('is-invalid');
                }

                // ตรวจสอบว่าวันที่สิ้นสุดต้องมาหลังวันที่เริ่ม
                if ($('#startDate').val() !== '' && $('#endDate').val() !== '') {
                    if (new Date($('#startDate').val()) > new Date($('#endDate').val())) {
                        isValid = false;
                        errorMessages.push('วันที่สิ้นสุดโครงการต้องมาหลังวันที่เริ่มโครงการ');
                        $('#endDate').addClass('is-invalid');
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

            function updateProject() {
                var formData = $('#editProjectForm').serialize();
                $.ajax({
                    url: '../system/update_project.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire('สำเร็จ', 'อัปเดตข้อมูลโครงการเรียบร้อยแล้ว', 'success')
                                .then(() => {
                                    window.location.href = '<?php echo base_url(); ?>/views/projects.php';
                                });
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถอัปเดตข้อมูลโครงการได้', 'error');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error:', textStatus, errorThrown);
                        console.log('Response Text:', jqXHR.responseText);
                        var errorMessage = 'เกิดข้อผิดพลาดขณะอัปเดตข้อมูลโครงการ';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMessage += ': ' + jqXHR.responseJSON.message;
                        } else if (textStatus === 'parsererror') {
                            errorMessage += ': ข้อมูลที่ได้รับจากเซิร์ฟเวอร์ไม่ถูกต้อง';
                        } else {
                            errorMessage += ': ' + textStatus;
                        }
                        Swal.fire('ข้อผิดพลาด', errorMessage, 'error');
                    }
                });
            }
        });
    </script>
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
</body>

</html>