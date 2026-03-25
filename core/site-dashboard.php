<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

$site_id = intval($_GET['site'] ?? 0);

if (!$site_id) {
    header('Location: dashboard.php');
    exit;
}

$admin_pdo = getDB('admin');

try {
    $stmt = $admin_pdo->prepare("SELECT site_name, db_name FROM tbl_site WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $site_id]);
    $site_details = $stmt->fetch();
} catch (PDOException $e) {
    error_log("site-dashboard PDO error: " . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}

if (!$site_details) {
    header('Location: dashboard.php');
    exit;
}

$site_name  = $site_details['site_name'];
$site_db    = $site_details['db_name'];
$csrf_token = generateCSRFToken();

require_once __DIR__ . '/common/db_key_helper.php';
$db_key    = ees_db_key($site_db);
$site_pdo  = tryGetDB($db_key);
$has_db    = ($site_pdo !== null);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/themes/base/jquery-ui.min.css" integrity="sha512-8PjjnSP8Bw/WNPxF6wkklW6qlQJdWJc/3w/ZQPvZ/1bjVDkrrSqLe9mfPYrMxtnzsXFPc434+u4FHLnLjXTSsg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" integrity="sha512-Ww1y9OuQ2kehgVWSD/3nhgfrb424O3802QYP/A5gPXoM4+rRjiKrjHdGxQKrMGQykmsJ/86oGdHszfcVgUr4hA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!--Font Awesome-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">        

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!--DataTables-->
<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">

<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>-->

<!--chartJS -->
<!--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>-->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> 

<link rel="stylesheet" href="assets/css/pages/site-dashboard.css">

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
    
    <div id="content">
        <div id="main-content">
            <div class="container-fluid">
                <div class="block-header">
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-8 col-sm-12">
                            <h2><?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></li>
                            </ul>
                        </div>
                    </div>
                </div>           
                
                <?php if (!$has_db): ?>
                <div class="row clearfix g-3 mb-3">
                    <div class="col-12">
                        <div class="card shadow-sm text-center py-5">
                            <div class="body">
                                <i class="fa fa-database fa-4x text-muted mb-3" style="display:block;"></i>
                                <h3 class="text-muted">No Data Available</h3>
                                <p class="text-muted mb-0">The database for <strong><?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></strong> has not been configured yet.</p>
                                <p class="text-muted">This site's dashboard will be available once data collection begins.</p>
                                <a href="dashboard.php" class="btn btn-primary mt-2"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- KPI Stat Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon green">
                                <i class="fa fa-bolt"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Today's Production</div>
                                <div class="ees-stat-value" id="daily_prod">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon blue">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Monthly Production</div>
                                <div class="ees-stat-value" id="monthly_prod">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon orange">
                                <i class="fa fa-line-chart"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Yearly Production</div>
                                <div class="ees-stat-value" id="yearly_prod">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon teal">
                                <i class="fa fa-tachometer"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Active Power</div>
                                <div class="ees-stat-value" id="active_power">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon orange">
                                <i class="fa fa-sun-o"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Avg Irradiance</div>
                                <div class="ees-stat-value" id="avg_irradiance">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon blue">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <div>
                                <div class="ees-stat-label">Sun Hours</div>
                                <div class="ees-stat-value" id="sun_hours">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Date Picker -->
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="body" style="padding:14px 20px;">
                                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                    <span style="font-size:13px;font-weight:700;color:var(--ees-text-secondary);">Choose date:</span>
                                    <button class="btn btn-secondary btn-sm" id="prevDate" type="button">
                                        <i class="fa fa-angle-left"></i>
                                    </button>
                                    <input type="date" id="calendar" class="form-control" style="width:auto;"
                                           onchange="render();" max="<?php echo date('Y-m-d'); ?>">
                                    <button class="btn btn-secondary btn-sm" id="nextDate" type="button">
                                        <i class="fa fa-angle-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Chart -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-12">
                        <div class="card shadow-sm">
                            <div class="header" style="display:flex;align-items:center;gap:12px;">
                                <h2 style="flex:1;">Plant KPI <small>Production | Irradiance | Power</small></h2>
                                <button class="btn btn-secondary btn-sm" id="zoom_reset" onclick="resetZoomBtn(kpi)">
                                    <i class="fa fa-search-minus"></i> Reset Zoom
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="downloadKPI()" title="Download">
                                    <i class="fa fa-download"></i>
                                </button>
                            </div>
                            <div class="body chartKPI">
                                <canvas id="kpi"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Production + Weather Charts -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>Production <small>Hourly</small></h2>
                            </div>
                            <div class="body">
                                <div style="height:400px;">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>Weather <small>Irradiance | Temperature</small></h2>
                            </div>
                            <div class="body">
                                <div style="height:400px;">
                                    <canvas id="lineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <!-- Footer -->
            <?php include_once("common/footer.php") ?>
            
        </div>
    </div> 
</div>



<!-- PHP data bridge for JS -->
<script>
var SITE_DB    = '<?php echo htmlspecialchars($site_db, ENT_QUOTES, 'UTF-8'); ?>';
var SITE_HAS_DB = <?php echo $has_db ? 'true' : 'false'; ?>;
</script>

<!-- Page JavaScript -->
<?php if ($has_db): ?>
<script src="assets/js/pages/site-dashboard.js"></script>
<?php endif; ?>



<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>

<script src="assets/bundles/knob.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/index.js"></script>

<script src="assets/js/pages/tables/jquery-datatable.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script src="assets/js/widgets/infobox/infobox-1.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

</body>
</html>
