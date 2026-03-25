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
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

<!--<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">-->
<!-- VENDOR CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/morris.min.css" />
<link rel="stylesheet" href="assets/vendor/sweetalert/sweetalert.css"/>

<!--PLANT CSS-->

<link rel="stylesheet" href="assets/css/plant.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">

<!-- HTML TO PDF -->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" integrity="sha512-Ww1y9OuQ2kehgVWSD/3nhgfrb424O3802QYP/A5gPXoM4+rRjiKrjHdGxQKrMGQykmsJ/86oGdHszfcVgUr4hA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.js"></script>

<!-- chartJS
============================================ -->  
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> 

<link rel="stylesheet" href="assets/css/pages/plant.css">

</head>

<body data-theme="theme-cyan" class="page-plant">

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
            <div class="block-header plant-legacy-block-header">
                <div class="row g-3">
                    <div class="col-lg-5 col-md-8 col-sm-12">
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth" href="javascript:void(0);" aria-label="Toggle layout width"><i class="fa fa-arrow-left"></i></a>Reports</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>
                            <li class="breadcrumb-item">Reports</li>
                            <li class="breadcrumb-item active">Plant</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mb-3 no-border plant-legacy-filter">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="row">
                            <div class="col-lg-6 col-xs-6 col-sm-6">
                                <div class="header">
                                    <h2 style="font-size: 18px;">CHOOSE A PLANT:</h2>
                                </div>
                                <div class="control">
                                    <select name="sort" id="site_opt" class="alarms-sort" required>
                                        <option value="">Choose a plant…</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-xs-6 col-sm-6">
                                <div class="header">
                                    <h2 style="font-size: 18px;">BUDGETED PRODUCTION:</h2>
                                </div>
                                <input type="text" id="budget_prod_input" name="budgeted" class="alarms-sort" placeholder="Insert the budgeted production">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-xs-6 col-sm-6">
                                <div class="header" style="margin-top: 21px;">
                                    <h2 style="font-size: 18px;">START DATE:</h2>
                                </div>
                                <input type="date" id="startDate" name="startdate" class="alarms-sort" required>
                            </div>
                            <div class="col-lg-6 col-xs-6 col-sm-6">
                                <div class="header" style="margin-top: 21px;">
                                    <h2 style="font-size: 18px;">END DATE:</h2>
                                </div>
                                <input type="date" id="endDate" name="enddate" class="alarms-sort" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="header">
                            <h2 style="font-size: 18px;">ENTER YOUR COMMENTS:</h2>
                        </div>
                        <textarea placeholder="Enter your comments..." id="comments_input" rows="5" cols="70" maxlength="547" required></textarea>
                        <button type="button" class="cmt-btn btn btn-primary mb-3 js-sweetalert" id="preview_btn" onclick="query();">View Changes</button>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-primary mb-3" style="display: inline-block;" id="cmd" onclick="generatePdf();">Generate PDF Report</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 plant-report-scroll-wrap">
                    <div class="pdf-container mb-3" id="plant_report_block">
                        <div class="row clearfix g-3 plant-report-logo-row">
                            <div class="col-lg-12">
                                <img src="" id="eco-logo" alt="">
                            </div>
                        </div>

                        <div class="row clearfix g-3 mb-3">
                            <div class="col-lg-12">
                                <h5 class="text-center reports-title" contenteditable>ECOASIS &nbsp;- <p id="output" class="mb-0 d-inline text-uppercase"></p> - <span id="monthyear" style="text-transform:uppercase;font-family: Arial;" contenteditable></span></h5>
                            </div>
                        </div>

                        <div class="row clearfix g-3 mb-3">
                            <div class="col-lg-3 col-md-5 col-sm-5 col-xs-12">
                               <div class="card text-center mb-3" style="height:101px;">
                                    <div class="body">
                                        <div class="">
                                            <h3 class="mt-0 m-b-5 value" contenteditable="true" style="font-weight: 600;" id="plant_total_prod"></h3>
                                            <span class="text-muted value-text">Total Production, kWh</span>
                                        </div>
                                    </div>
                                </div>  

                               <div class="card text-center mb-3" style="height:101px;">
                                    <div class="body">
                                        <div class="">
                                            <h3 class="mt-0 m-b-5 value" contenteditable="true" style="font-weight: 600;" id = "plant_total_insolation"></h3>
                                            <span class="text-muted value-text">Insolation, kWh/m<sup>2</sup></span>
                                        </div>
                                    </div>
                                </div>

                               <div class="card text-center mb-3" style="height:101px;">
                                    <div class="body">
                                        <div class="">
                                            <h3 class="mt-0 m-b-5 value" contenteditable="true" style="font-weight: 600;" id="plant_pr"></h3>
                                            <span class="text-muted value-text">Performace Ratio</span>
                                        </div>
                                    </div>
                                </div> 

                               <div class="card text-center mb-3" style="height:101px;">
                                    <div class="body">
                                        <div class="">
                                            <h3 class="mt-0 m-b-5 value" contenteditable="true" style="font-weight: 600;" id="plant_co2_avoided"></h3>
                                            <span class="text-muted value-text">Kg C0<sub>2</sub> Avoided</span>
                                        </div>
                                    </div>
                                </div>                                                          
                            </div>

                            <div class="col-lg-9 col-md-7 col-sm-7 col-xs-12">
                                <div class="card text-center" style="height:452px;">
                                    <div class="header">
                                        <h2>Production v/s Irradiance</h2>
                                    </div>
                                    <div class="body chartBox">
                                        <canvas id="barChart"></canvas>
                                    </div>
                                </div>
                            </div>               
                        </div>

                        <div class="row clearfix g-3 mb-3 plant-report-dual-col">
                            <div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">                                                                                                 
                                <div class="card shadow-sm">
                                    <div class="header text-center">
                                        <h2>Daily Values</h2>
                                    </div>

                                    <div class="table-responsive tbl_alerts">
                                        <table id="plantTable" class="table table-bordered table-striped table-hover js-basic-example tbl_daily">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Production (kWh)</th>
                                                    <th>Insolation (kWh/m<sup>2</sup>)</th>
                                                    <th>PR (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody contenteditable>
                                                <!--<tr>-->
                                                <!--    <td>01/05/2024</td>-->
                                                <!--    <td>2218,019</td>-->
                                                <!--    <td>349</td> -->
                                                <!--    <td>87</td>                                           -->
                                                <!--</tr>-->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7 col-md-6 col-sm-6 col-xs-12">
                                <div class="card shadow-sm mb-3">
                                    <div class="header text-center">
                                        <h2>Production Data Analysis</h2>
                                    </div>

                                    <div class="table-responsive tbl_alerts">
                                        <table class="table table-bordered table-hover js-basic-example dataTable table-custom tbl_analysis">
                                            <tbody>
                                                <tr>
                                                    <td>Budgeted Production for <span id="prod-monthyear"></span> (KWh)</td>
                                                    <td contenteditable="true" id="plant_budget_prod"></td>                                          
                                                </tr>
                                                <!--<tr>-->
                                                <!--    <td>Projected <span id="proj-monthyear"></span> value - 0.85 % decrease (kWh)</td>-->
                                                <!--    <td contenteditable="true">88,931.98</td>  -->
                                                <!--</tr>-->
                                                <tr>
                                                    <td>Recorded Value for <span id="rec-monthyear"></span> (kWh)</td>
                                                    <td contenteditable="true" id="plant_total_prod2"></td>                                          
                                                </tr>
                                                <tr>
                                                    <td>% Deviation</td>
                                                    <td contenteditable="true" id="deviation"> <span>%</span></td>  
                                                </tr> 
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card text-center" style="height:516px;">
                                    <div class="header">
                                        <h2>Historical Data</h2>
                                    </div>
                                    <div class="body chartBox2">
                                        <canvas id="barChart2"></canvas>
                                    </div>
                                </div>                    
                            </div>
                        </div>  

                        <div class="row clearfix g-3 mb-3">               
                            <div class="col-lg-12" style="margin-bottom:37px;">
                                <div class="card shadow-sm">
                                    <div class="body" style="padding-top:5px;padding-bottom:0px;">
                                        <label><strong>Comments:</strong></label>
                                        <p id="comments_output" class="comments-para" style="width:100%;margin-bottom:0px;"><!-- The production value of January 2024 is lower than the projected value. The table above shows the previous corresponding insolation values. The insolation value of January 2024  is lower than January 2023, explaining the lower production value. Additionally, due to cyclonic weather conditions throughout this month, the PV was switched off for a total of 5 days as per protocol. This has negatively impacted the production. -->
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div> 


                        <div class="row clearfix g-3 plant-report-footer-row">
                            <div class="col-lg-6 pdf-footer" style="position:relative;">
                                <p style="margin-bottom:0px;">Ecoasis&nbsp; Energy Solutions Ltd<br> <span>Highlands, Mauritius | T: +230 650 6970 | E:info@ecoasis.mu</span></p>
                            </div>

                            <div class="col-lg-6 plant-report-footer-logo" style="position:relative;">
                                <img id="enl-logo" alt="">
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



<!-- Core bundles first so jQuery is available to plant.js -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/morrisscripts.bundle.js"></script>
<script src="assets/vendor/sweetalert/sweetalert.min.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/vendor/editable-table/mindmup-editabletable.js"></script>
<script src="assets/js/pages/tables/editable-table.js"></script>
<script src="assets/js/pages/ui/dialogs.js"></script>

<!-- Page JavaScript -->
<script src="assets/js/pages/plant.js"></script>

</body>
</html>