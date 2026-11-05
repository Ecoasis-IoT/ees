<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/authorization.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

if (!isAdmin()) {
    http_response_code(403);
    include __DIR__ . '/error-404.php';
    exit;
}

$csrf_token = generateCSRFToken();
$current_user_id = (int)($_SESSION['id'] ?? 0);
$groups = [];
try {
    $groups = getDB('admin')->query('SELECT id, name FROM tbl_user_group ORDER BY level, id')->fetchAll();
} catch (Throwable $e) {
    error_log('user-management groups: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

<link rel="stylesheet" href="assets/css/dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">

<style>
.tfa-badge-on  { display:inline-block; background:rgba(112,173,71,.15); color:#3d6d1f; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.tfa-badge-off { display:inline-block; background:rgba(100,116,139,.12); color:#475569; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.user-actions { white-space: nowrap; }
.user-actions .btn { margin-right: 4px; }
#user-edit-modal { display:none; position:fixed; inset:0; z-index:99990; align-items:center; justify-content:center; background:rgba(0,0,0,.45); }
#user-edit-modal .user-edit-card { background:#fff; border-radius:8px; padding:22px 24px 18px; width:min(480px, 92vw); box-shadow:0 8px 32px rgba(0,0,0,.2); }
#user-edit-modal label { display:block; font-size:12px; font-weight:600; margin:10px 0 4px; }
#user-edit-modal input, #user-edit-modal select { width:100%; height:34px; padding:4px 8px; }
#user-edit-modal .hint { color:#64748b; font-size:12px; margin-top:4px; }
</style>
</head>

<body data-theme="theme-cyan">

<!-- Page Loader -->
<?php include_once("common/page-loader.php") ?>

<!-- Overlay For Sidebars -->

<div id="wrapper">
    <!-- Header -->
    <?php include_once("common/header.php") ?>

    <!-- Left sidebar-->
    <?php include_once("common/sidebar.php") ?>

    <div id="main-content">
        <div class="container-fluid">
            <div class="block-header">
                <div class="row g-3">
                    <div class="col-lg-5 col-md-8 col-sm-12">                        
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Users</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">User Management</li>
                            <li class="breadcrumb-item active">Users</li>
                        </ul>
                    </div>            
                </div>
            </div>
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;">List of Users</h2> 
                            <a href="add-user" class="btn btn-primary mb-2" style="float: right;"><i class="fa fa-plus"></i> Add User</a>                                               
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="tbl_users" class="table table-bordered table-striped table-hover w-100 table-custom">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>2FA</th>
                                            <th>Date Joined</th>
                                            <th>Actions</th>
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

            <!-- Footer -->
            <?php include_once("common/footer.php") ?>
        </div>
    </div>
    
</div>

<div id="user-edit-modal" role="dialog" aria-modal="true" aria-labelledby="user-edit-title">
    <form class="user-edit-card" id="user-edit-form" autocomplete="off">
        <h4 id="user-edit-title" style="margin:0 0 8px;">Edit user</h4>
        <input type="hidden" id="edit-id" value="">
        <label for="edit-firstname">First name</label>
        <input type="text" id="edit-firstname" maxlength="50" required>
        <label for="edit-lastname">Last name</label>
        <input type="text" id="edit-lastname" maxlength="50" required>
        <label for="edit-email">Email</label>
        <input type="email" id="edit-email" maxlength="60" required>
        <label for="edit-group">Role</label>
        <select id="edit-group" required>
            <?php foreach ($groups as $group): ?>
                <option value="<?= (int)$group['id'] ?>"><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <label for="edit-password">New password</label>
        <input type="password" id="edit-password" maxlength="30" autocomplete="new-password">
        <div class="hint">Leave blank to keep the current password.</div>
        <div style="margin-top:16px; text-align:right;">
            <button type="button" class="btn btn-outline-dark" id="edit-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary" id="edit-save">Save</button>
        </div>
    </form>
</div>


<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>

<script src="assets/bundles/datatablescripts.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>

<script>
var USER_CSRF = <?= json_encode($csrf_token) ?>;
var CURRENT_USER_ID = <?= (int)$current_user_id ?>;
var userRows = [];

function _esc(str) {
    return String(str == null ? '' : str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function initUserListDataTable() {
    if ($('#tbl_users').length === 0 || typeof $.fn.DataTable !== 'function') return;
    if ($.fn.DataTable.isDataTable('#tbl_users')) {
        $('#tbl_users').DataTable().destroy();
    }
    $('#tbl_users').DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
        order: [[0, 'asc']],
        autoWidth: false,
        dom: 'lfrtip',
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
            search: 'Search',
            lengthMenu: 'Show _MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ users',
            infoEmpty: 'No users',
            infoFiltered: '(filtered from _MAX_ total)',
            zeroRecords: 'No matching users found'
        }
    });
}

function closeUserEdit() {
    document.getElementById('user-edit-modal').style.display = 'none';
    document.getElementById('edit-password').value = '';
}

function openUserEdit(id) {
    var row = null;
    for (var i = 0; i < userRows.length; i++) {
        if (parseInt(userRows[i].id, 10) === id) { row = userRows[i]; break; }
    }
    if (!row) return;
    document.getElementById('edit-id').value = row.id;
    document.getElementById('edit-firstname').value = row.firstname || '';
    document.getElementById('edit-lastname').value = row.lastname || '';
    document.getElementById('edit-email').value = row.email || '';
    document.getElementById('edit-group').value = String(row.group_id || '');
    document.getElementById('edit-password').value = '';
    document.getElementById('user-edit-modal').style.display = 'flex';
    document.getElementById('edit-firstname').focus();
}

function loadUsers() {
    $.ajax({
        type: 'POST',
        url: 'scripts/get_all_users',
        dataType: 'json',
        success: function (data) {
            if (data && data.status === 'Err') {
                var msg = data.message || 'You do not have permission to load this list.';
                if (typeof EES !== 'undefined' && EES.alert) EES.alert(msg, 'error');
                return;
            }
            userRows = Array.isArray(data) ? data : [];
            if ($.fn.DataTable.isDataTable('#tbl_users')) {
                $('#tbl_users').DataTable().destroy();
            }
            var $tb = $('#tbl_users tbody');
            $tb.empty();
            for (var i = 0; i < userRows.length; i++) {
                var row = userRows[i];
                var id = parseInt(row.id, 10);
                var on = parseInt(row.tfa_enabled, 10) === 1;
                var badge = on
                    ? '<span class="tfa-badge-on">On</span>'
                    : '<span class="tfa-badge-off">Off</span>';
                var del = id === CURRENT_USER_ID
                    ? ''
                    : '<button type="button" class="btn btn-sm btn-outline-danger btn-user-delete" data-id="' + id + '">Delete</button>';
                var html = '<tr>'
                    + '<td>' + _esc(row.fullname) + '</td>'
                    + '<td>' + _esc(row.email) + '</td>'
                    + '<td>' + _esc(row.group_name || '') + '</td>'
                    + '<td>' + badge + '</td>'
                    + '<td>' + _esc(row.date_added) + '</td>'
                    + '<td class="user-actions">'
                    + '<button type="button" class="btn btn-sm btn-outline-primary btn-user-edit" data-id="' + id + '">Edit</button>'
                    + del
                    + '</td></tr>';
                $tb.append(html);
            }
        },
        error: function (xhr) {
            var msg = 'Could not load users.';
            try {
                var j = typeof xhr.responseJSON === 'object' ? xhr.responseJSON : JSON.parse(xhr.responseText || '{}');
                if (j && j.message) msg = j.message;
            } catch (e) { /* ignore */ }
            if (typeof EES !== 'undefined' && EES.alert) EES.alert(msg, 'error');
        },
        complete: function () {
            initUserListDataTable();
        }
    });
}

$(function () {
    loadUsers();

    $('#tbl_users').on('click', '.btn-user-edit', function () {
        openUserEdit(parseInt($(this).data('id'), 10));
    });

    $('#tbl_users').on('click', '.btn-user-delete', function () {
        var id = parseInt($(this).data('id'), 10);
        var row = null;
        for (var i = 0; i < userRows.length; i++) {
            if (parseInt(userRows[i].id, 10) === id) { row = userRows[i]; break; }
        }
        var label = row ? (row.fullname || row.email) : 'this user';
        if (!window.confirm('Delete ' + label + '? This cannot be undone.')) return;
        $.ajax({
            type: 'POST',
            url: 'scripts/admin/user_delete',
            dataType: 'json',
            data: { csrf_token: USER_CSRF, id: id },
            success: function (res) {
                if (!res || res.status !== 'ok') {
                    EES.alert((res && res.message) || 'Could not delete the user.', 'error');
                    return;
                }
                EES.alert(res.message || 'User deleted', 'success');
                loadUsers();
            },
            error: function () { EES.alert('Could not delete the user.', 'error'); }
        });
    });

    $('#edit-cancel').on('click', closeUserEdit);
    $('#user-edit-modal').on('click', function (e) {
        if (e.target === this) closeUserEdit();
    });

    $('#user-edit-form').on('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('edit-save');
        EES.btnLoad(btn, 'Saving…');
        $.ajax({
            type: 'POST',
            url: 'scripts/admin/user_update',
            dataType: 'json',
            data: {
                csrf_token: USER_CSRF,
                id: document.getElementById('edit-id').value,
                firstname: document.getElementById('edit-firstname').value,
                lastname: document.getElementById('edit-lastname').value,
                email: document.getElementById('edit-email').value,
                group_id: document.getElementById('edit-group').value,
                password: document.getElementById('edit-password').value
            },
            success: function (res) {
                EES.btnReset(btn);
                if (!res || res.status !== 'ok') {
                    EES.alert((res && res.message) || 'Could not save the user.', 'error');
                    return;
                }
                closeUserEdit();
                EES.alert(res.message || 'User updated', 'success');
                loadUsers();
            },
            error: function (xhr) {
                EES.btnReset(btn);
                var msg = 'Could not save the user.';
                try {
                    var j = typeof xhr.responseJSON === 'object' ? xhr.responseJSON : JSON.parse(xhr.responseText || '{}');
                    if (j && j.message) msg = j.message;
                } catch (err) { /* ignore */ }
                EES.alert(msg, 'error');
            }
        });
    });
});
</script>
</body>
</html>
