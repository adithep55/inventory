<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, invoice, html5, responsive, Projects">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>รายการผู้ใช้</title>
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
                    <h4>รายการผู้ใช้</h4>
                    <h6>จัดการผู้ใช้ของคุณ</h6>
                </div>
                <div class="page-btn">
                    <a href="adduser" class="btn btn-added"><img src="../assets/img/icons/plus.svg" alt="img" class="me-1">เพิ่มผู้ใช้ใหม่</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-path">
                                <a class="btn btn-filter" id="filter_search">
                                    <img src="../assets/img/icons/filter.svg" alt="img">
                                    <span><img src="../assets/img/icons/closes.svg" alt="img"></span>
                                </a>
                            </div>
                            <div class="search-input">
                                <a class="btn btn-searchset"><img src="../assets/img/icons/search-white.svg" alt="img"></a>
                            </div>
                        </div>
                        <div class="wordset">
                            <ul>
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="../assets/img/icons/pdf.svg" alt="img"></a>
                                </li>
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="../assets/img/icons/excel.svg" alt="img"></a>
                                </li>
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="../assets/img/icons/printer.svg" alt="img"></a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table datanew" id="userTable">
                            <thead>
                                <tr>
                                    <th>
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>รูปภาพ</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>ชื่อ</th>
                                    <th>นามสกุล</th>
                                    <th>บทบาท</th>
                                    <th>การกระทำ</th>
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

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">แก้ไขข้อมูลผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_fname" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="edit_fname" name="fname" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lname" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="edit_lname" name="lname" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">บทบาท</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <!-- ตัวเลือกบทบาทจะถูกเพิ่มด้วย JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">รหัสผ่านใหม่ (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="saveUserChanges">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">เพิ่มผู้ใช้ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="add_username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control" id="add_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_password" class="form-label">รหัสผ่าน</label>
                        <input type="password" class="form-control" id="add_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_fname" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="add_fname" name="fname" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_lname" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="add_lname" name="lname" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_role" class="form-label">บทบาท</label>
                        <select class="form-select" id="add_role" name="role" required>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="saveNewUser">บันทึก</button>
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
    if ($.fn.DataTable.isDataTable('#userTable')) {
        $('#userTable').DataTable().destroy();
    }
    $('#editUserModal').on('hidden.bs.modal', function () {
        resetEditUserForm();
    });

    $('.btn-added').on('click', function(e) {
        e.preventDefault();
        loadRoles();  // โหลดข้อมูลบทบาทใหม่ทุกครั้งที่เปิด Modal เพิ่มผู้ใช้
        $('#addUserModal').modal('show');
    });
    loadRoles();

    var userTable = $('#userTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/api/get_users.php?for_user_list=true",
            "type": "POST",
            "dataType": "json",
            "error": function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                let errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage += ': ไม่พบ API endpoint';
                } else if (xhr.status === 500) {
                    errorMessage += ': เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์';
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        },
            "columns": [
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return '<label class="checkboxs"><input type="checkbox" class="user-checkbox" value="' + row.UserID + '"><span class="checkmarks"></span></label>';
                    },
                    "orderable": false
                },
                { 
                    "data": "img",
                    "render": function(data, type, row) {
                        return '<img src="../img/profile/' + data + '" alt="User Image" class="avatar-xs rounded-circle" onerror="this.onerror=null;this.src=\'../img/profile/user.png\';">';
                    }
                },
                { "data": "Username" },
                { "data": "fname" },
                { "data": "lname" },
                { "data": "RoleName" },
                { 
                    "data": "UserID",
                    "render": function (data, type, row) {
                        return '<a class="me-3 edit-user" href="javascript:void(0);" data-id="' + data + '"><img src="../assets/img/icons/edit.svg" alt="Edit"></a>' +
                               '<a class="me-3 confirm-text" href="javascript:void(0);" onclick="deleteUser(' + data + ')"><img src="../assets/img/icons/delete.svg" alt="Delete"></a>';
                    },
                    "orderable": false
                }
            ],
            "drawCallback": function(settings) {
                updateSelectAllCheckbox();
            },
            "language": {
                "emptyTable": "ไม่พบข้อมูลผู้ใช้",
                "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "lengthMenu": "แสดง _MENU_ รายการ",
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
        

        $('#select-all').on('click', function() {
        $('.user-checkbox').prop('checked', this.checked);
    });

    $('#userTable').on('change', '.user-checkbox', function() {
        updateSelectAllCheckbox();
    });

    // เพิ่ม event listener สำหรับปุ่มแก้ไข
    $('#userTable').on('click', '.edit-user', function() {
        var userId = $(this).data('id');
        editUser(userId);
    });

    // Event listener สำหรับปุ่มบันทึกการเปลี่ยนแปลง
    $('#saveUserChanges').on('click', function() {
        saveUserChanges();
    });

    // Event listener สำหรับปุ่มบันทึกผู้ใช้ใหม่
    $('#saveNewUser').on('click', function() {
        saveNewUser();
    });
});

function updateSelectAllCheckbox() {
    var allChecked = $('.user-checkbox:checked').length === $('.user-checkbox').length && $('.user-checkbox').length > 0;
    $('#select-all').prop('checked', allChecked);
}

function loadRoles() {
    $.ajax({
        url: '../api/get_roles.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var options = '';
                $.each(response.data, function(i, role) {
                    options += '<option value="' + role.RoleID + '">' + role.RoleName + '</option>';
                });
                $('#edit_role, #add_role').html(options);
            } else {
                console.error('Failed to load roles:', response.message);
                Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลบทบาทได้', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            let errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูลบทบาท';
            if (xhr.responseText) {
                errorMessage += ': ' + xhr.responseText;
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

function editUser(userId) {
    $.ajax({
        url: '../api/get_users.php',
        type: 'GET',
        data: { id: userId, action: 'get_single_user' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                var user = response.data;
                $('#edit_user_id').val(user.UserID);
                $('#edit_username').val(user.Username);
                $('#edit_fname').val(user.fname);
                $('#edit_lname').val(user.lname);
                loadRoles();  // โหลดข้อมูลบทบาทใหม่ทุกครั้งที่เปิด Modal แก้ไข
                $('#edit_role').val(user.RoleID);
                $('#editUserModal').modal('show');
            } else {
                Swal.fire('Error', response.message || 'ไม่สามารถโหลดข้อมูลผู้ใช้ได้', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            let errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้';
            if (xhr.responseText) {
                errorMessage += ': ' + xhr.responseText;
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}
function resetEditUserForm() {
    $('#editUserForm')[0].reset();
    $('#edit_user_id').val('');
    $('#edit_role').val('').trigger('change'); 
}
function saveUserChanges() {
    var formData = $('#editUserForm').serialize();
    $.ajax({
        url: '../system/update_user.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#editUserModal').modal('hide');
                Swal.fire('สำเร็จ', 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว', 'success').then(() => {
                    $('#userTable').DataTable().ajax.reload(null, false);
                });
                resetEditUserForm(); 
            } else {
                Swal.fire('Error', response.message || 'ไม่สามารถอัปเดตข้อมูลผู้ใช้ได้', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            let errorMessage = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลผู้ใช้';
            if (xhr.responseText) {
                errorMessage += ': ' + xhr.responseText;
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}
function deleteUser(userId) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: "คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../system/delete_user.php',
                type: 'POST',
                data: { id: userId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('ลบสำเร็จ!', response.message, 'success').then(() => {
                            $('#userTable').DataTable().ajax.reload(null, false);
                        });
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    let errorMessage = 'เกิดข้อผิดพลาดในการลบผู้ใช้';
                    if (xhr.responseText) {
                        errorMessage += ': ' + xhr.responseText;
                    }
                    Swal.fire('เกิดข้อผิดพลาด!', errorMessage, 'error');
                }
            });
        }
    });

    
}
$('#saveNewUser').on('click', function() {
    var formData = $('#addUserForm').serialize();
    $.ajax({
        url: '../system/add_user.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#addUserModal').modal('hide');
                Swal.fire('สำเร็จ', 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว', 'success').then(() => {
                    $('#userTable').DataTable().ajax.reload(null, false);
                });
                $('#addUserForm')[0].reset();
            } else {
                Swal.fire('Error', response.message || 'ไม่สามารถเพิ่มผู้ใช้ใหม่ได้', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            let errorMessage = 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้ใหม่';
            if (xhr.responseText) {
                errorMessage += ': ' + xhr.responseText;
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
});
</script>