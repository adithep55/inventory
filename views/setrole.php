<?php
require_once '../config/permission.php';
requirePermission(['manage_users']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <title>จัดการบทบาท</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
.permission-badge {
    display: inline-block;
    padding: 0.25em 0.4em;
    font-size: 100%;
    line-height: 1.1; 
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    cursor: pointer;
    margin: 2px;
}

.permission-badge.active {
    color: #fff;
    background-color: #28a745;
}

.permission-badge.inactive {
    color: #fff;
    background-color: #6c757d;
}
.permission-container {
    display: inline-block;
}
.permission-summary, .permission-full {
    display: inline-block;
}
.permission-expand {
    cursor: pointer;
    margin-left: 5px;
    background-color: #f0f0f0;
    padding: 2px 5px;
    border-radius: 10px;
    font-size: 0.8em;
}
.permission-badge {
    margin-right: 3px;
}

</style>
<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>
    <div class="page-wrapper">
    <div class="content container-fluid">
    <?php require_once '../includes/notification.php'; ?>
            <div class="page-header">
                <div class="page-title">
                    <h4>จัดการบทบาท</h4>
                    <h6>กำหนดและจัดการบทบาทของผู้ใช้</h6>
                </div>
                <div class="page-btn">
                    <a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <img src="../assets/img/icons/plus.svg" alt="img" class="me-1">เพิ่มบทบาทใหม่
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                    </div>
                    <div class="table-responsive">
                        <table class="table datanew" id="roleTable">
                            <thead>
                                <tr>
                                    <th>รหัสบทบาท</th>
                                    <th>ชื่อบทบาท</th>
                                    <th>สิทธิ์การใช้งาน</th>
                                    <th>การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ข้อมูลจะถูกเพิ่มด้วย JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <!-- Modal สำหรับเพิ่มบทบาทใหม่ -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มบทบาทใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRoleForm">
                    <div class="mb-3">
                        <label for="add_role_name" class="form-label">ชื่อบทบาท</label>
                        <input type="text" class="form-control" id="add_role_name" name="role_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สิทธิ์ที่เลือก</label>
                        <div id="add_selected_permissions" class="mb-2"></div>
                        <label class="form-label">สิทธิ์ที่สามารถเลือก</label>
                        <div id="add_available_permissions"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="saveNewRole">บันทึก</button>
            </div>
        </div>
    </div>
</div>

   <!-- Modal สำหรับแก้ไขบทบาท -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขบทบาท</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRoleForm">
                    <input type="hidden" id="edit_role_id" name="role_id">
                    <div class="mb-3">
                        <label for="edit_role_name" class="form-label">ชื่อบทบาท</label>
                        <input type="text" class="form-control" id="edit_role_name" name="role_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สิทธิ์ที่เลือก</label>
                        <div id="selected_permissions" class="mb-2"></div>
                        <label class="form-label">สิทธิ์ที่สามารถเลือก</label>
                        <div id="available_permissions"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="saveRoleChanges">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    var roleTable;

    // Initialize DataTable
    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#roleTable')) {
            $('#roleTable').DataTable().destroy();
        }

        roleTable = $('#roleTable').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "../api/get_roles.php",
                "type": "GET",
                "dataType": "json",
                "dataSrc": function (json) {
                    if (json.status === 'success' && Array.isArray(json.data)) {
                        return json.data;
                    } else {
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล: โครงสร้างข้อมูลไม่ถูกต้อง', 'error');
                        return [];
                    }
                },
                "error": function (xhr, status, error) {
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
                }
            },
            "columns": [
                { "data": "RoleID" },
                { "data": "RoleName" },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return formatPermissions(row);
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return '<a class="me-3 edit-role" href="javascript:void(0);" data-id="' + row.RoleID + '"><img src="../assets/img/icons/edit.svg" alt="Edit"></a>' +
                            '<a class="me-3 confirm-text" href="javascript:void(0);" onclick="deleteRole(' + row.RoleID + ')"><img src="../assets/img/icons/delete.svg" alt="Delete"></a>';
                    },
                    "orderable": false
                }
            ],
            "language": {
                "emptyTable": "ไม่พบข้อมูลบทบาท",
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
    }

    // Initialize DataTable on page load
    initializeDataTable();
    function formatPermissions(row) {
    if (!row) return "ไม่มีข้อมูล";
    var permissions = [];
    for (var key in permissionMap) {
        if (row[key] == 1) {
            permissions.push(permissionMap[key]);
        }
    }
    if (permissions.length === 0) {
        return '<span class="permission-badge inactive">ไม่มีสิทธิ์</span>';
    }
    return '<div class="permission-container" data-permissions="' + permissions.join(',') + '">' +
           '<div class="permission-summary"></div>' +
           '<button class="permission-expand btn btn-sm btn-light" style="display:none;">+<span class="count"></span> <i class="fas fa-chevron-down"></i></button>' +
           '<div class="permission-full" style="display:none;"></div>' +
           '</div>';
}

// ปรับปรุงฟังก์ชัน updatePermissionDisplay
function updatePermissionDisplay() {
    $('.permission-container').each(function() {
        var container = $(this);
        var permissions = container.data('permissions').split(',');
        var summary = container.find('.permission-summary');
        var expand = container.find('.permission-expand');
        var full = container.find('.permission-full');
        var count = expand.find('.count');

        summary.empty();
        full.empty();

        if (permissions.length <= 2) {
            permissions.forEach(function(p) {
                summary.append('<span class="permission-badge active">' + p + '</span> ');
            });
            expand.hide();
        } else {
            permissions.slice(0, 2).forEach(function(p) {
                summary.append('<span class="permission-badge active">' + p + '</span> ');
            });
            count.text(permissions.length - 2);
            expand.show();
            permissions.slice(2).forEach(function(p) {
                full.append('<span class="permission-badge active">' + p + '</span> ');
            });
        }
    });
}

$(document).ready(function() {
    $(document).on('click', '.permission-expand', function(e) {
        e.preventDefault();
        var container = $(this).closest('.permission-container');
        var full = container.find('.permission-full');
        var icon = $(this).find('i');
        
        full.toggle();
        icon.toggleClass('fa-chevron-down fa-chevron-up');
        
        if (full.is(':visible')) {
            $(this).html('แสดงน้อยลง <i class="fas fa-chevron-up"></i>');
        } else {
            var count = container.data('permissions').split(',').length - 2;
            $(this).html('+' + count + ' <i class="fas fa-chevron-down"></i>');
        }
    });

    // เรียกใช้ฟังก์ชันหลังจาก DataTable โหลดข้อมูลเสร็จ
    roleTable.on('draw', updatePermissionDisplay);
});

    // Permission mapping
    const permissionMap = {
        manage_products: "จัดการสินค้า ",
        manage_receiving: "จัดการการรับสินค้า ",
        manage_issue: "จัดการการเบิกสินค้า ",
        manage_inventory: "จัดการคลังสินค้า ",
        manage_projects: "จัดการโครงการ ",
        manage_customers: "จัดการลูกค้า ",
        manage_transfers: "จัดการโอนย้ายสินค้า ",
        manage_reports: "จัดการรายงาน ",
        manage_users: "จัดการผู้ใช้งาน ",
        manage_settings: "จัดการการตั้งค่าทั่วไป "
    };

   // แก้ไขฟังก์ชัน initializeAddRolePermissions
function initializeAddRolePermissions() {
    let availablePermissions = $('#add_available_permissions');
    availablePermissions.empty();

    for (let key in permissionMap) {
        let badge = $('<span>')
            .addClass('permission-badge inactive m-1')
            .text(permissionMap[key])
            .attr('data-permission', key)
            .append($('<i>').addClass('fas fa-plus-circle ml-2').css('cursor', 'pointer'));
        availablePermissions.append(badge);
    }

    // Event listener for adding permissions
    availablePermissions.on('click', '.permission-badge', function() {
        let badge = $(this);
        badge.removeClass('inactive').addClass('active')
            .find('i').removeClass('fa-plus-circle').addClass('fa-times-circle');
        $('#add_selected_permissions').append(badge);
    });

    // Event listener for removing permissions
    $('#add_selected_permissions').on('click', '.fa-times-circle', function(e) {
        e.stopPropagation();
        let badge = $(this).parent();
        badge.removeClass('active').addClass('inactive')
            .find('i').removeClass('fa-times-circle').addClass('fa-plus-circle');
        availablePermissions.append(badge);
    });
}

    // Call this function when the add role modal is shown
    $('#addRoleModal').on('show.bs.modal', function() {
        initializeAddRolePermissions();
    });

    // Save new role
    function saveNewRole() {
        var formData = new FormData($('#addRoleForm')[0]);
        
        // Add selected permissions to formData
        $('#add_selected_permissions .permission-badge').each(function() {
            formData.append('permissions[]', $(this).data('permission'));
        });

        $.ajax({
            url: '../system/add_role.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#addRoleModal').modal('hide');
                    Swal.fire('สำเร็จ', 'เพิ่มบทบาทใหม่เรียบร้อยแล้ว', 'success').then(() => {
                        roleTable.ajax.reload(null, false);
                    });
                    $('#addRoleForm')[0].reset();
                    $('#add_selected_permissions').empty();
                    initializeAddRolePermissions();
                } else {
                    Swal.fire('Error', response.message || 'ไม่สามารถเพิ่มบทบาทใหม่ได้', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'เกิดข้อผิดพลาดในการเพิ่มบทบาทใหม่', 'error');
            }
        });
    }

    // Edit role
    function editRole(roleId) {
        $.ajax({
            url: '../api/get_roles.php',
            type: 'GET',
            data: { id: roleId },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    var role = response.data.find(r => r.RoleID == roleId);
                    if (role) {
                        $('#edit_role_id').val(role.RoleID);
                        $('#edit_role_name').val(role.RoleName);
                        updatePermissionBadges(role);
                        $('#editRoleModal').modal('show');
                    } else {
                        Swal.fire('Error', 'ไม่พบข้อมูลบทบาทที่ต้องการแก้ไข', 'error');
                    }
                } else {
                    Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลบทบาทได้', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'เกิดข้อผิดพลาดในการโหลดข้อมูลบทบาท', 'error');
            }
        });
    }

    function updatePermissionBadges(role) {
    let selectedPermissions = $('#selected_permissions');
    let availablePermissions = $('#available_permissions');
    selectedPermissions.empty();
    availablePermissions.empty();

    for (let key in permissionMap) {
        let badge = $('<span>')
            .addClass('permission-badge m-1')
            .text(permissionMap[key])
            .attr('data-permission', key);

        if (role[key] == 1) {
            badge.addClass('active')
                .append($('<i>').addClass('fas fa-times-circle ml-2').css('cursor', 'pointer'));
            selectedPermissions.append(badge);
        } else {
            badge.addClass('inactive')
                .append($('<i>').addClass('fas fa-plus-circle ml-2').css('cursor', 'pointer'));
            availablePermissions.append(badge);
        }
    }

    // Event listener for adding permissions
    availablePermissions.on('click', '.permission-badge', function() {
        let badge = $(this);
        badge.removeClass('inactive').addClass('active')
            .find('i').removeClass('fa-plus-circle').addClass('fa-times-circle');
        selectedPermissions.append(badge);
        ensureSingleIcon(badge);
    });

    // Event listener for removing permissions
    selectedPermissions.on('click', '.fa-times-circle', function(e) {
        e.stopPropagation();
        let badge = $(this).parent();
        badge.removeClass('active').addClass('inactive')
            .find('i').removeClass('fa-times-circle').addClass('fa-plus-circle');
        availablePermissions.append(badge);
        ensureSingleIcon(badge);
    });
}

function ensureSingleIcon(badge) {
    let icons = badge.find('i');
    if (icons.length > 1) {
        icons.slice(1).remove();
    }
}


    // Save role changes
    function saveRoleChanges() {
        var formData = $('#editRoleForm').serialize();
        
        // Add selected permissions to formData
        $('#selected_permissions .permission-badge').each(function() {
            formData += '&permissions[]=' + $(this).data('permission');
        });

        $.ajax({
            url: '../system/update_role.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#editRoleModal').modal('hide');
                    Swal.fire('สำเร็จ', 'อัปเดตบทบาทเรียบร้อยแล้ว', 'success').then(() => {
                        roleTable.ajax.reload(null, false);
                    });
                } else {
                    Swal.fire('Error', response.message || 'ไม่สามารถอัปเดตบทบาทได้', 'error');
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = 'เกิดข้อผิดพลาดในการอัปเดตบทบาท';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    }

    // Delete role
    function deleteRole(roleId) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: "คุณแน่ใจหรือไม่ที่จะลบบทบาทนี้?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../system/delete_role.php',
                type: 'POST',
                data: { id: roleId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('ลบสำเร็จ!', response.message, 'success').then(() => {
                            roleTable.ajax.reload(null, false);
                        });
                    } else {
                        let errorMessage = response.message;
                        let errorTitle = 'เกิดข้อผิดพลาด!';
                        
                        if (response.code === 'ROLE_IN_USE' || response.code === 'INTEGRITY_CONSTRAINT_VIOLATION') {
                            errorTitle = 'ไม่สามารถลบบทบาทได้';
                        }
                        
                        Swal.fire(errorTitle, errorMessage, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง', 'error');
                }
            });
        }
    });
}

    // Event listeners
    $(document).on('click', '#saveNewRole', function () {
        saveNewRole();
    });

    $(document).on('click', '.edit-role', function () {
        var roleId = $(this).data('id');
        editRole(roleId);
    });

    $(document).on('click', '#saveRoleChanges', function () {
        saveRoleChanges();
    });

    // Make deleteRole function global
    window.deleteRole = deleteRole;
});
    </script>
</body>
</html>