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
    margin: 2px;
}

.permission-badge.active {
    background-color: #ffcc80;
    color: #424242;
}

.permission-badge.inactive {
    background-color: #ff6b6b; 
    color: #ffffff;
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
                                    <th>#</th>
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
                            <label class="form-label">สิทธิ์การใช้งาน</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_products"
                                    name="permissions[]" value="manage_products">
                                <label class="form-check-label" for="manage_products">จัดการสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_receiving"
                                    name="permissions[]" value="manage_receiving">
                                <label class="form-check-label" for="add_manage_receiving">จัดการการรับสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_inventory"
                                    name="permissions[]" value="manage_inventory">
                                <label class="form-check-label" for="add_manage_inventory">จัดการคลังสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_projects"
                                    name="permissions[]" value="manage_projects">
                                <label class="form-check-label" for="add_manage_projects">จัดการโครงการ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_customers"
                                    name="permissions[]" value="manage_customers">
                                <label class="form-check-label" for="add_manage_customers">จัดการลูกค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_transfers"
                                    name="permissions[]" value="manage_transfers">
                                <label class="form-check-label" for="add_manage_transfers">จัดการโอนย้ายสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_reports"
                                    name="permissions[]" value="manage_reports">
                                <label class="form-check-label" for="add_manage_reports">จัดการรายงาน</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_users"
                                    name="permissions[]" value="manage_users">
                                <label class="form-check-label" for="add_manage_users">จัดการผู้ใช้งาน</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_manage_settings"
                                    name="permissions[]" value="manage_settings">
                                <label class="form-check-label" for="add_manage_settings">จัดการการตั้งค่าทั่วไป</label>
                            </div>
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
                            <label class="form-label">สิทธิ์การใช้งาน</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_products"
                                    name="permissions[]" value="manage_products">
                                <label class="form-check-label" for="edit_manage_products">จัดการสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_receiving"
                                    name="permissions[]" value="manage_receiving">
                                <label class="form-check-label" for="edit_manage_receiving">จัดการการรับสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_inventory"
                                    name="permissions[]" value="manage_inventory">
                                <label class="form-check-label" for="edit_manage_inventory">จัดการคลังสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_projects"
                                    name="permissions[]" value="manage_projects">
                                <label class="form-check-label" for="edit_manage_projects">จัดการโครงการ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_customers"
                                    name="permissions[]" value="manage_customers">
                                <label class="form-check-label" for="edit_manage_customers">จัดการลูกค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_transfers"
                                    name="permissions[]" value="manage_transfers">
                                <label class="form-check-label" for="edit_manage_transfers">จัดการโอนย้ายสินค้า</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_reports"
                                    name="permissions[]" value="manage_reports">
                                <label class="form-check-label" for="edit_manage_reports">จัดการรายงาน</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_users"
                                    name="permissions[]" value="manage_users">
                                <label class="form-check-label" for="edit_manage_users">จัดการผู้ใช้งาน</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_manage_settings"
                                    name="permissions[]" value="manage_settings">
                                <label class="form-check-label"
                                    for="edit_manage_settings">จัดการการตั้งค่าทั่วไป</label>
                            </div>
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

            // เรียกใช้ฟังก์ชันเริ่มต้นตารางข้อมูล
            initializeDataTable();

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
        });

    function formatPermissions(row) {
    if (!row) return "ไม่มีข้อมูล";
    var permissions = [];
    var permissionMap = {
        manage_products: "จัดการสินค้า",
        manage_receiving: "จัดการการรับสินค้า",
        manage_inventory: "จัดการคลังสินค้า",
        manage_projects: "จัดการโครงการ",
        manage_customers: "จัดการลูกค้า",
        manage_transfers: "จัดการโอนย้ายสินค้า",
        manage_reports: "จัดการรายงาน",
        manage_users: "จัดการผู้ใช้งาน",
        manage_settings: "จัดการการตั้งค่าทั่วไป"
    };

    for (var key in permissionMap) {
        if (row[key] == 1) {
            permissions.push('<span class="permission-badge active">' + permissionMap[key] + '</span>');
        }
    }

    return permissions.length > 0 ? permissions.join(" ") : '<span class="permission-badge inactive">ไม่มีสิทธิ์</span>';
}

        function saveNewRole() {
            var formData = $('#addRoleForm').serialize();
            $.ajax({
                url: '../system/add_role.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#addRoleModal').modal('hide');
                        Swal.fire('สำเร็จ', 'เพิ่มบทบาทใหม่เรียบร้อยแล้ว', 'success').then(() => {
                            if ($.fn.DataTable.isDataTable('#roleTable')) {
                                $('#roleTable').DataTable().ajax.reload(null, false);
                            } else {
                                initializeDataTable();
                            }
                        });
                        $('#addRoleForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message || 'ไม่สามารถเพิ่มบทบาทใหม่ได้', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการเพิ่มบทบาทใหม่', 'error');
                }
            });
        }

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
                    setPermissionCheckboxes(role);
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

function setPermissionCheckboxes(role) {
    $('#edit_manage_products').prop('checked', role.manage_products);
    $('#edit_manage_receiving').prop('checked', role.manage_receiving);
    $('#edit_manage_inventory').prop('checked', role.manage_inventory);
    $('#edit_manage_projects').prop('checked', role.manage_projects);
    $('#edit_manage_customers').prop('checked', role.manage_customers);
    $('#edit_manage_transfers').prop('checked', role.manage_transfers);
    $('#edit_manage_reports').prop('checked', role.manage_reports);
    $('#edit_manage_users').prop('checked', role.manage_users);
    $('#edit_manage_settings').prop('checked', role.manage_settings);
}
function saveRoleChanges() {
    var formData = $('#editRoleForm').serialize();
    $.ajax({
        url: '../system/update_role.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#editRoleModal').modal('hide');
                Swal.fire('สำเร็จ', 'อัปเดตบทบาทเรียบร้อยแล้ว', 'success').then(() => {
                    $('#roleTable').DataTable().ajax.reload(null, false);
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
                                    $('#roleTable').DataTable().ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถลบบทบาทได้', 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>