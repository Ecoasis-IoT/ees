<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

if (!isset($_SESSION['group_id']) || (int)$_SESSION['group_id'] !== (int)ADMIN_USERGROUP_ID) {
    header('Location: ' . ees_url_path('dashboard.php'));
    exit;
}

$csrf_token = generateCSRFToken();

$stats = ['total' => 0, 'user' => 0, 'system' => 0, 'error' => 0, 'today' => 0];
try {
    $pdo = getDB('admin');
    $chk = $pdo->query("SHOW TABLES LIKE 'tbl_audit_logs'");
    if ($chk && $chk->rowCount() > 0) {
        $stats['total']  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_audit_logs")->fetchColumn();
        $stats['user']   = (int)$pdo->query("SELECT COUNT(*) FROM tbl_audit_logs WHERE category = 'user'")->fetchColumn();
        $stats['system'] = (int)$pdo->query("SELECT COUNT(*) FROM tbl_audit_logs WHERE category = 'system'")->fetchColumn();
        $stats['error']  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_audit_logs WHERE category = 'error'")->fetchColumn();
        $stats['today']  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_audit_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    }
} catch (Throwable $e) {
    error_log('audit_logs stats: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Audit Logs | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .stat-card { border-left: 4px solid #26a69a; }
        .stat-card.user   { border-left-color: #36A2EB; }
        .stat-card.system { border-left-color: #9966FF; }
        .stat-card.error  { border-left-color: #dc3545; }
        .stat-card.today  { border-left-color: #ffc107; }
        .stat-val { font-size: 2rem; font-weight: 700; line-height: 1.1; }
        .stat-label { color: #888; font-size: 13px; margin-top: 4px; }
        .log-filters .form-control { font-size: 13px; height: 32px; padding: 4px 8px; }
        .log-filters label { font-size: 12px; font-weight: 600; margin-bottom: 2px; }
        .badge-severity-INFO     { background:#17a2b8; color:#fff; }
        .badge-severity-WARNING  { background:#ffc107; color:#212529; }
        .badge-severity-ERROR    { background:#fd7e14; color:#fff; }
        .badge-severity-CRITICAL { background:#dc3545; color:#fff; }
        .badge-cat-user   { background:#36A2EB; color:#fff; }
        .badge-cat-system { background:#9966FF; color:#fff; }
        .badge-cat-error  { background:#dc3545; color:#fff; }
        #audit-table_wrapper .dataTables_filter { display: none; }
        #audit-table_wrapper .dt-buttons { margin-bottom: 10px; }
        #audit-table_wrapper .dt-buttons .btn { margin-right: 6px; }
        .severity-info     { color: #17a2b8; }
        .severity-warning  { color: #ffc107; }
        .severity-error    { color: #fd7e14; }
        .severity-critical { color: #dc3545; font-weight: 600; }
        .log-detail-pre { white-space: pre-wrap; word-break: break-word; font-size: 12px;
                          max-height: 200px; overflow-y: auto; background: #f8f9fa;
                          border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; margin: 0; }
    </style>
</head>
<body data-theme="theme-cyan">

<?php include_once("common/page-loader.php") ?>

<div id="wrapper">
    <?php include_once("common/header.php") ?>
    <?php include_once("common/sidebar.php") ?>

    <div id="main-content">
        <div class="container-fluid">
            <div class="block-header">
                <div class="row g-3">
                    <div class="col-lg-8 col-md-8 col-sm-12">
                        <h2>Audit Logs <small>User actions, system events &amp; errors</small></h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item active">Audit Logs</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card stat-card">
                        <div class="body text-center py-3">
                            <div class="stat-val"><?= number_format($stats['total']) ?></div>
                            <div class="stat-label">Total entries</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card stat-card today">
                        <div class="body text-center py-3">
                            <div class="stat-val"><?= number_format($stats['today']) ?></div>
                            <div class="stat-label">Today</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card stat-card user">
                        <div class="body text-center py-3">
                            <div class="stat-val"><?= number_format($stats['user']) ?></div>
                            <div class="stat-label">User actions</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card stat-card system">
                        <div class="body text-center py-3">
                            <div class="stat-val"><?= number_format($stats['system']) ?></div>
                            <div class="stat-label">System events</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card stat-card error">
                        <div class="body text-center py-3">
                            <div class="stat-val"><?= number_format($stats['error']) ?></div>
                            <div class="stat-label">Errors</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2>All Audit Logs <small>Page views, edits, reports, cron, webhooks &amp; PHP errors</small></h2>
                            <ul class="header-dropdown m-r--5">
                                <li>
                                    <button type="button" id="audit-export-csv" class="btn btn-sm btn-default" title="Export all rows matching current filters">
                                        <i class="fa fa-download"></i> Export CSV
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="body">
                            <div class="row log-filters mb-3">
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Category</label>
                                    <select id="audit-filter-category" class="form-control">
                                        <option value="">All categories</option>
                                        <option value="user">User</option>
                                        <option value="system">System</option>
                                        <option value="error">Error</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Action</label>
                                    <input type="text" id="audit-filter-action" class="form-control" placeholder="e.g. page_view">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Severity</label>
                                    <select id="audit-filter-severity" class="form-control">
                                        <option value="">All severities</option>
                                        <option value="INFO">INFO</option>
                                        <option value="WARNING">WARNING</option>
                                        <option value="ERROR">ERROR</option>
                                        <option value="CRITICAL">CRITICAL</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>IP Address</label>
                                    <input type="text" id="audit-filter-ip" class="form-control" placeholder="e.g. 192.168.1.1">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Date From</label>
                                    <input type="date" id="audit-filter-date-from" class="form-control">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Date To</label>
                                    <input type="date" id="audit-filter-date-to" class="form-control">
                                </div>
                                <div class="col-lg-12 mb-2" style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <div style="flex:1;min-width:200px;">
                                        <label>Search</label>
                                        <input type="text" id="audit-filter-search" class="form-control" placeholder="Search message, resource, user…">
                                    </div>
                                    <div style="display:flex;align-items:flex-end;gap:6px;">
                                        <button id="audit-filter-apply" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Apply</button>
                                        <button id="audit-filter-clear"  class="btn btn-sm btn-default"><i class="fa fa-times"></i> Clear</button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped" id="audit-table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width:155px;">Timestamp</th>
                                            <th style="width:80px;">Category</th>
                                            <th>Action</th>
                                            <th style="width:85px;">Severity</th>
                                            <th>User</th>
                                            <th style="width:120px;">IP</th>
                                            <th>Resource / Message</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once("common/footer.php") ?>
        </div>
    </div>
</div>

<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
var CSRF_TOKEN = '<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>';
var auditTable = null;

function auditFilterPayload() {
    return {
        csrf_token:       CSRF_TOKEN,
        filter_category:  $('#audit-filter-category').val(),
        filter_action:    $('#audit-filter-action').val(),
        filter_severity:  $('#audit-filter-severity').val(),
        filter_ip:        $('#audit-filter-ip').val(),
        filter_date_from: $('#audit-filter-date-from').val(),
        filter_date_to:   $('#audit-filter-date-to').val(),
        search:           $('#audit-filter-search').val()
    };
}

function exportAuditCsv() {
    var $btn = $('#audit-export-csv');
    var origHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting…');

    $.ajax({
        url: 'scripts/export_audit_logs',
        type: 'POST',
        data: auditFilterPayload(),
        xhrFields: { responseType: 'blob' },
        success: function(blob, status, xhr) {
            var ct = (xhr.getResponseHeader('Content-Type') || '').toLowerCase();
            if (ct.indexOf('application/json') !== -1) {
                var reader = new FileReader();
                reader.onload = function() {
                    try {
                        var err = JSON.parse(reader.result);
                        alert(err.message || 'Export failed.');
                    } catch (e) {
                        alert('Export failed.');
                    }
                };
                reader.readAsText(blob);
                return;
            }
            var filename = 'ees_audit_logs_' + new Date().toISOString().slice(0, 10) + '.csv';
            var disposition = xhr.getResponseHeader('Content-Disposition') || '';
            var match = disposition.match(/filename=\"?([^\";]+)\"?/i);
            if (match && match[1]) {
                filename = match[1];
            }
            var url = window.URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        },
        error: function(xhr) {
            var msg = 'Export failed.';
            try {
                var err = JSON.parse(xhr.responseText);
                if (err.message) msg = err.message;
            } catch (e) {}
            alert(msg);
        },
        complete: function() {
            $btn.prop('disabled', false).html(origHtml);
        }
    });
}

function prettyDetails(val) {
    if (!val) return '<span class="text-muted">—</span>';
    try {
        var obj = JSON.parse(val);
        val = JSON.stringify(obj, null, 2);
    } catch (e) {}
    return '<pre class="log-detail-pre">' + $('<span>').text(val).html() + '</pre>';
}

function initAuditTable() {
    auditTable = $('#audit-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'scripts/get_audit_logs',
            type: 'POST',
            data: function(d) {
                var payload = auditFilterPayload();
                d.csrf_token       = payload.csrf_token;
                d.filter_category  = payload.filter_category;
                d.filter_action    = payload.filter_action;
                d.filter_severity  = payload.filter_severity;
                d.filter_ip        = payload.filter_ip;
                d.filter_date_from = payload.filter_date_from;
                d.filter_date_to   = payload.filter_date_to;
                if (payload.search) {
                    d.search = d.search || {};
                    d.search.value = payload.search;
                }
            },
            error: function(xhr) {
                console.error('Audit logs AJAX error', xhr.responseText);
            }
        },
        columns: [
            { data: 'created_at', width: '155px' },
            {
                data: 'category', width: '80px',
                render: function(val) {
                    val = (val || 'user').toLowerCase();
                    return '<span class="badge badge-cat-' + val + '">' + val + '</span>';
                }
            },
            { data: 'action' },
            {
                data: 'severity', width: '85px',
                render: function(val) {
                    var sev = (val || 'INFO').toUpperCase();
                    return '<span class="badge badge-severity-' + sev + '">' + sev + '</span>';
                }
            },
            {
                data: 'user_label',
                render: function(val) {
                    return val ? $('<span>').text(val).html() : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'ip_address', width: '120px',
                render: function(val) {
                    return val ? $('<span>').text(val).html() : '<span class="text-muted">—</span>';
                }
            },
            {
                data: null,
                render: function(row) {
                    var parts = [];
                    if (row.resource) parts.push(row.resource);
                    if (row.message)  parts.push(row.message);
                    var text = parts.join(' — ');
                    return text ? $('<span>').text(text).html() : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'details',
                render: prettyDetails
            }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        dom: 'lfrtipB',
        buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-download"></i> Page CSV',
                className: 'btn btn-sm btn-default',
                title: 'ees_audit_logs_page',
                exportOptions: {
                    format: {
                        body: function(data) {
                            return $('<div>').html(data).text().trim();
                        }
                    }
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Page Excel',
                className: 'btn btn-sm btn-default',
                title: 'ees_audit_logs_page',
                exportOptions: {
                    format: {
                        body: function(data) {
                            return $('<div>').html(data).text().trim();
                        }
                    }
                }
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i> Print',
                className: 'btn btn-sm btn-default',
                customize: function(win) {
                    $(win.document.body).find('pre.log-detail-pre').each(function() {
                        $(this).css({ 'max-height': 'none', 'white-space': 'pre-wrap' });
                    });
                }
            }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading…',
            zeroRecords: 'No matching logs found',
            emptyTable:  'No logs loaded yet — apply filters or search to begin'
        }
    });
}

$(document).ready(function() {
    initAuditTable();

    $('#audit-export-csv').on('click', exportAuditCsv);

    $('#audit-filter-apply').on('click', function() {
        auditTable.ajax.reload();
    });
    $('#audit-filter-clear').on('click', function() {
        $('#audit-filter-category, #audit-filter-action, #audit-filter-severity, #audit-filter-ip').val('');
        $('#audit-filter-date-from, #audit-filter-date-to, #audit-filter-search').val('');
        auditTable.search('').ajax.reload();
    });
    $('#audit-filter-search, #audit-filter-action, #audit-filter-ip, #audit-filter-date-from, #audit-filter-date-to').on('keydown', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#audit-filter-apply').trigger('click');
        }
    });
    $('#audit-filter-search').on('keypress', function(e) {
        if (e.which === 13) auditTable.ajax.reload();
    });
});
</script>

</body>
</html>
