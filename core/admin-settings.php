<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/authorization.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

// Admin-only page
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
    <title>Admin Settings | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <link rel="stylesheet" href="assets/css/dataTables.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">
    <?= assetCssTag('assets/css/pages/admin-settings.css') ?>
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
                    <div class="col-lg-5 col-md-8 col-sm-12">
                        <h2>Admin Settings</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>
                            <li class="breadcrumb-item active">Admin Settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Alert Banner -->
            <div id="admin-alert" class="alert" role="alert" style="display:none;"></div>
            <div id="gateway-migration-alert" class="alert alert-warning" role="alert" style="display:none;"></div>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-general-link" data-bs-toggle="tab" href="#tab-general" role="tab">
                        <i class="fa fa-sliders"></i> General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-users-link" data-bs-toggle="tab" href="#tab-users" role="tab">
                        <i class="fa fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-sites-link" data-bs-toggle="tab" href="#tab-sites" role="tab">
                        <i class="fa fa-map-marker"></i> Sites
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-devices-link" data-bs-toggle="tab" href="#tab-devices" role="tab">
                        <i class="icon-energy"></i> Devices
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="adminTabContent">

                <!-- ===================== GENERAL TAB ===================== -->
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <div class="row clearfix g-3 mb-3">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="header"><h2>ChirpStack API</h2></div>
                                <div class="body">
                                    <p class="text-muted">
                                        Global gateway polling settings. Per-site gateway EUIs are configured on the <strong>Sites</strong> tab.
                                        Status on <a href="site">All Sites</a> updates from cron or <strong>Poll All Now</strong>.
                                    </p>
                                    <form id="global-gateway-form" class="row g-3">
                                        <input type="hidden" name="setting_group" value="gateway">
                                        <div class="col-md-5">
                                            <label class="form-label" for="api_url">API URL</label>
                                            <input type="url" class="form-control" id="api_url" name="api_url"
                                                   placeholder="http://195.35.48.27:8090" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="tenant_id">Tenant ID <small class="text-muted">(optional)</small></label>
                                            <input type="text" class="form-control" id="tenant_id" name="tenant_id"
                                                   placeholder="e801c4e7-dd19-4128-a4c3-543bbca0088c">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="offline_threshold_seconds">Offline after (seconds)</label>
                                            <input type="number" class="form-control" id="offline_threshold_seconds"
                                                   name="offline_threshold_seconds" min="60" step="60" value="900">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary" id="btn-save-gateway">
                                                <i class="fa fa-save"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-success ms-2" id="btn-poll-all">
                                                <i class="fa fa-refresh"></i> Poll All Now
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="header"><h2>Other Settings</h2></div>
                        <div class="body">
                            <p class="text-muted mb-0">
                                Additional global settings will be added here. Values are stored in <code>tbl_settings</code>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ===================== USERS TAB ===================== -->
                <div class="tab-pane fade" id="tab-users" role="tabpanel">
                    <div class="row clearfix g-3 mb-3">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="header">
                                    <h2 style="display:inline-block;">Users</h2>
                                    <button class="btn btn-primary mb-2" style="float:right;" onclick="showAddUserModal()">
                                        <i class="fa fa-plus"></i> Add User
                                    </button>
                                </div>
                                <div class="body">
                                    <div class="table-responsive">
                                        <table id="tbl-users" class="table table-bordered table-striped table-hover w-100">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Date Joined</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== SITES TAB ===================== -->
                <div class="tab-pane fade" id="tab-sites" role="tabpanel">
                    <div class="row clearfix g-3 mb-3">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="header">
                                    <h2 style="display:inline-block;">Sites</h2>
                                </div>
                                <div class="body">
                                    <div class="table-responsive">
                                        <table id="tbl-sites" class="table table-bordered table-striped table-hover w-100">
                                            <thead>
                                                <tr>
                                                    <th>Site Name</th>
                                                    <th>Database</th>
                                                    <th>Capacity</th>
                                                    <th>Gateway EUI</th>
                                                    <th>Token</th>
                                                    <th>Poll</th>
                                                    <th>Last Seen</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== DEVICES TAB ===================== -->
                <div class="tab-pane fade" id="tab-devices" role="tabpanel">
                    <div class="row clearfix g-3 mb-3">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="header">
                                    <h2 style="display:inline-block;">Devices &mdash; <span id="devices-site-name">Select a site from the Sites tab</span></h2>
                                </div>
                                <div class="body">
                                    <div class="table-responsive">
                                        <table id="tbl-devices" class="table table-bordered table-striped table-hover w-100">
                                            <thead>
                                                <tr>
                                                    <th>Device Name</th>
                                                    <th>Device Type</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /tab-content -->

            <?php include_once("common/footer.php") ?>
        </div>
    </div>
</div>

<!-- Modal Overlay -->
<div id="modal-overlay">
    <div class="admin-modal" id="modal-body"></div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<?= assetScriptTag('assets/js/pages/admin-settings.js') ?>
</body>
</html>
