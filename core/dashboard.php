<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ecoasis - Positive Energies | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-providers@latest/leaflet-providers.js"></script>
    <script src="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js"></script>
    <link href="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="assets/css/dataTables.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">

    <!-- Page-specific CSS -->
    <?= assetCssTag('assets/css/pages/dashboard.css') ?>
</head>
<body data-theme="theme-cyan">

<?php include_once("common/page-loader.php") ?>

<div id="wrapper">
    <?php include_once("common/header.php") ?>
    <?php include_once("common/sidebar.php") ?>

    <div id="content">
        <div id="main-content">
            <div class="container-fluid">
                <div class="block-header">
                    <div class="block-header-bar">
                        <h2>Dashboard</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                    </div>
                </div>

                <!-- KPI Stat Cards -->
                <div class="row g-2 mb-2">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon green">
                                <i class="fa fa-sitemap"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Total Sites</div>
                                <div class="ees-stat-value" id="kpi-total-sites">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon blue">
                                <i class="fa fa-bolt"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Today's Production</div>
                                <div class="ees-stat-value" id="kpi-total-prod">—</div>
                                <div class="ees-stat-sub">kWh total</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon orange">
                                <i class="fa fa-tachometer"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Active Power</div>
                                <div class="ees-stat-value" id="kpi-total-power">—</div>
                                <div class="ees-stat-sub">kW combined</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sites table + chart (left) | Map (right) -->
                <div class="row clearfix g-2 mb-2">
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>All Sites</h2>
                            </div>
                            <div class="body" id="tbl_production">
                                <div class="table-responsive">
                                    <table id="tbl_site_prod" class="table table-bordered table-hover js-basic-example">
                                        <thead>
                                            <tr>
                                                <th>Site Name</th>
                                                <th>Production (kWh)</th>
                                                <th>Active Power (kW)</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="header" style="border-top:1px solid var(--ees-border);">
                                <h2>Today's Production <small>All sites combined</small></h2>
                            </div>
                            <div class="body" id="chart-con">
                                <div class="chartBox" style="height:340px;">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="card shadow-sm" style="min-height:560px;">
                            <div class="header">
                                <h2>Site Map</h2>
                            </div>
                            <div id="map_container" style="padding:0;">
                                <div id="sites_map" style="height:500px;border-radius:0 0 12px 12px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include_once("common/footer.php") ?>
            </div>
        </div>
    </div>
</div>

<!-- Bundles -->
<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/knob.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script src="assets/js/index.js"></script>
<script src="assets/js/widgets/infobox/infobox-1.js"></script>

<!-- Page-specific JS -->
<?= assetScriptTag('assets/js/pages/dashboard.js') ?>
</body>
</html>
