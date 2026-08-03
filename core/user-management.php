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
                                            <th>Date Joined</th>
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


<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>

<script src="assets/bundles/datatablescripts.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>

<script>
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

$(function users() {
    $.ajax({
        type: 'POST',
        url: 'scripts/get_all_users',
        dataType: 'json',
        success: function (data) {
            if (data && data.status === 'Err') {
                var msg = data.message || 'You do not have permission to load this list.';
                if (typeof EES !== 'undefined' && EES.alert) {
                    EES.alert(msg, 'error');
                }
                return;
            }
            var rows = Array.isArray(data) ? data : [];
            var $tb = $('#tbl_users tbody');
            $tb.empty();
            for (var i = 0; i < rows.length; i++) {
                var row = '<tr><td>' + _esc(rows[i].fullname) + '</td><td>' + _esc(rows[i].email) + '</td><td>' + _esc(rows[i].date_added) + '</td></tr>';
                $tb.append(row);
            }
        },
        error: function (xhr) {
            var msg = 'Could not load users.';
            try {
                var j = typeof xhr.responseJSON === 'object' ? xhr.responseJSON : JSON.parse(xhr.responseText || '{}');
                if (j && j.message) {
                    msg = j.message;
                }
            } catch (e) { /* ignore */ }
            if (typeof EES !== 'undefined' && EES.alert) {
                EES.alert(msg, 'error');
            }
        },
        complete: function () {
            initUserListDataTable();
        }
    });
});
</script>
</body>
</html>
