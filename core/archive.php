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
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">



<!-- JQuery AJAX -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/themes/base/jquery-ui.min.css" integrity="sha512-8PjjnSP8Bw/WNPxF6wkklW6qlQJdWJc/3w/ZQPvZ/1bjVDkrrSqLe9mfPYrMxtnzsXFPc434+u4FHLnLjXTSsg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" integrity="sha512-Ww1y9OuQ2kehgVWSD/3nhgfrb424O3802QYP/A5gPXoM4+rRjiKrjHdGxQKrMGQykmsJ/86oGdHszfcVgUr4hA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!-- VENDOR CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/morris.min.css" />

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<link rel="stylesheet" href="assets/css/pages/archive.css">

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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left" onclick="fullwidth()"></i></a>Archive</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Archive</li>
                        </ul>
                    </div>            
                </div>
            </div>            

            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="row mb-3">
                            <div class="col-lg-3 col-md-3">
                                <div class="header">
                                    <h6>CHOOSE SITE</h6> 
                                </div>           

                                <select name="sort" id="site" class="alarms-sort select" onchange="validate_date()">
                                    <option hidden>Choose a site</option>
                                </select>    
                            </div>  
                            
                            <div class="col-lg-3 col-xs-12 col-sm-12">
                                <div class="header">
                                    <h6>START DATE:</h6>                            
                                </div>
                                <input type="date" id="startDate" name="startdate" class="alarms-sort" required>
                            </div>
                            
                            <div class="col-lg-3 col-xs-12 col-sm-12">
                                <div class="header">
                                    <h6>END DATE:</h6>                            
                                </div>
                                <input type="date" id="endDate" name="enddate" class="alarms-sort" required>           
                            </div>
                            
                            <div class="col-lg-2 col-12 col-sm-2">    
                                <div class="header">
                                    <h2 style="display:inline-block;color:transparent;"></h2> 
                                </div>           
    
                                <button class="btn btn-primary submit" onclick="query()">Submit</button>         
                            </div>  
                                                
                        </div>
                    </div>
                </div>
            </div>    
            
            <div class="row clearfix g-3 mb-3 custom_hide_card">
                
                <div class="col-lg-12">
                    <div class="card">
                        <div class="body">
                            
                            <div class="row text-center justify-content-center">
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="card text-center mb-3 card-box">
                                        <div class="body">
                                            <h3 id="total_prod"></h3>
                                            <span class="text-muted value-text">Total Production</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="card text-center mb-3 card-box">
                                        <div class="body">
                                            <h3 id="total_insolation"></h3>
                                            <span class="text-muted value-text">Total Insolation</span>
                                        </div>
                                    </div>
                                </div>    
                            </div>
                            
                                                                 
                            <div class="btn-chart">
                                <button class="btn btn-primary clear-btn" id="zoom_reset" onclick="resetZoomBtn()">Reset Zoom</button>
                                <i class="fa fa-download" onclick="downloadCustom()"  title="Download"></i>
                            </div>  
    
                            <div class="chartBox">
                                <canvas id="archive_chart"></canvas> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row clearfix g-3 mb-3 custom_hide_card">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;padding-bottom:20px;">Archived Data Table</h2> 
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="tbl_archive" class="table table-bordered table-striped table-hover dataTable">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Site</th>
                                            <th>Production (kWh)</th>
                                            <th>Insolation (kWh/m<sup>2</sup>)</th>
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
<script src="assets/bundles/morrisscripts.bundle.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script type="text/javascript" src="assets/js/pages/tables/jquery-datatable.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/pages/ui/dialogs.js"></script>

<script src="assets/js/pages/archive.js"></script>

<!-- archive.js loaded above -->

</body>
</html>