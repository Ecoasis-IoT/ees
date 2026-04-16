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

<!--<link rel="stylesheet" href="assets/css/dataTables.min.css">-->

<!--CUSTOM CSS-->
<link rel="stylesheet" href="assets/css/custom.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">

<!-- QUERY CSS-->
<link rel="stylesheet" href="assets/css/query.css">


<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<!--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/1.2.1/chartjs-plugin-zoom.js" integrity="sha512-7X7B4dUsqfSxUe5m8NELendyUKx+xwZg4wSFECgBIPGaMSLS6e6oDGkxfJsFOlPADqIwkrP/pI9PihypuWFbEw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>-->




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> 


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css"/>


<!-- DataTables JS moved to footer (after libscripts.bundle.js re-defines jQuery) -->




</head>
<body data-theme="theme-cyan" class="page-query">

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
                <div class="block-header-bar">
                    <h2>Reports</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>
                        <li class="breadcrumb-item">Reports</li>
                        <li class="breadcrumb-item active">Energy Meters</li>
                    </ul>
                </div>
            </div>
     
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12 col-md-12">
                    <div class="card no-bg">
                        <div class="body p-0">
                            <div class="card no-radius-bottom query-tabs-wrap">
                                <div class="body query-tabs-body">
                                    <ul class="nav nav-tabs-new2">
                                        <li class="nav-item"><a class="nav-link active show" data-bs-toggle="tab" href="#Day">Day</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Month">Month</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Year">Year</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Custom">Custom Query</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content no-pt">
                                <div class="tab-pane show active" id="Day">
                                    <div class="card no-radius">
                                        <div class="body no-ptb">
                                            <div class="row mb-3 g-3 query-toolbar align-items-end">
                                                <div class="col-lg-2 col-md-6 col-sm-6">
                                                    <div class="header">
                                                        <h2>Choose site</h2>
                                                    </div>
                                                    <select name="sites_opt_day" id="sites_opt_day" class="form-select alarms-sort" onchange="filter_meters(1)">
                                                        <option hidden>Choose a site</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="header">
                                                        <h2>Energy meter</h2>
                                                    </div>
                                                    <select name="meters_opt_day" id="meters_opt_day" class="form-select alarms-sort">
                                                        <option hidden>Choose an Energy Meter</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 col-md-4 col-sm-6">
                                                    <div class="header">
                                                        <h2>Date</h2>
                                                    </div>
                                                    <input type="date" id="date_day" class="form-control query-date-input">
                                                </div>
                                                <div class="col-lg-auto col-md-4 col-sm-6 d-flex align-items-end query-toolbar-action">
                                                    <button type="button" class="btn btn-primary submit" onclick="get_day()">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card g-3 mb-3 no-radius custom_hide_card">
                                        <div class="body no-pb">
                                            <div class="row text-center justify-content-center">
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="day_total_prod"></h3>
                                                            <span class="text-muted value-text">Total Production</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="day_total_insolation"></h3>
                                                            <span class="text-muted value-text">Insolation</span>
                                                        </div>
                                                    </div>
                                                </div>    
                                                
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="day_pr"></h3>
                                                            <span class="text-muted value-text">Performance Ratio</span>
                                                        </div>
                                                    </div>
                                                </div> 
        
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="day_co2"></h3>
                                                            <span class="text-muted value-text">CO<sub>2</sub> Avoided</span>
                                                        </div>
                                                    </div>
                                                </div>                                         
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row clearfix g-3 mb-3 custom_hide_card">
                                        <div class="col-lg-12">
                                            <div class="card no-radius-top">
                                                <div class="body">
                                                    <div class="header">
                                                        <h2 style="display: inline-block;">Meter KPI<small>Production | Irradiance | Power</small> </h2> 
                                                        <i class="fa fa-download download" onclick="downloadKPI()"  title="Download"></i>
                                                    </div>
                                
                                                    <div class="chartKPI">                        
                                                        <canvas id="day_kpi"></canvas>                        
                                                    </div>  
                                                </div>
                                            </div>
                                        </div>                     
                                    </div>
                                    
                                    <div class="card g-3 mb-3 custom_hide_card">
                                        <div class="body">
                                            <div class="header">
                                                <h2>Hourly Production</h2>
                                            </div>                                    
                                            <div class="table-responsive">
                                                <table id="tbl_prod_hourly" class="table table-bordered table-striped table-hover" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>Device</th>
                                                            <th>Production (kWh)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> 
                                                    </tbody>
                                                </table>
                                            </div> 
                                        </div>
                                    </div>
                                    
                                    <div class="card g-3 mb-3 custom_hide_card">
                                        <div class="body">                                    
                                            <div class="header">
                                                <h2>Active Power</h2>
                                            </div>                                             
                                            <div class="table-responsive">
                                                <table id="tbl_apower_day" class="table table-bordered table-striped table-hover" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>Device</th>
                                                            <th>Active Power (kW)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> 

                                                    </tbody>
                                                </table>
                                            </div>  
                                        </div>
                                    </div>  
                                    
                                    <div class="card g-3 custom_hide_card">
                                        <div class="body">                                    
                                            <div class="header">
                                                <h2>Irradiance</h2>
                                            </div>                                             
                                            <div class="table-responsive">
                                                <table id="tbl_irradiance_day" class="table table-bordered table-striped table-hover" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Site</th>
                                                    <th>Irradiance (W/m<sup>2</sup>)</th>
                                                    <th>Insolation (kWh/m<sup>2</sup>)</th>
                                                    <th>Ambient Temperature</th>
                                                    <th>Panel Temperature</th>
                                                </tr>
                                            </thead>
                                            <tbody> 
                                            
                                            </tbody>
                                            </table>
                                            </div>                                    
                                        </div>
                                    </div>                                    
                                </div>

                                <div class="tab-pane" id="Month">
                                    <div class="card no-radius">
                                        <div class="body no-ptb">                                    
                                            <div class="row mb-3 g-3 query-toolbar align-items-end" style="position:relative;">
                                                <div class="col-lg-2 col-md-6">
                                                    <div class="header">
                                                        <h2>Choose site</h2>
                                                    </div>
                                                    <select name="sort" id="sites_opt_month" class="form-select alarms-sort" onchange="filter_meters(2)">
                                                        <option hidden>Choose a site</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="header">
                                                        <h2>Energy meter</h2>
                                                    </div>
                                                    <select name="sort" id="meters_opt_month" class="form-select alarms-sort">
                                                        <option hidden>Choose an Energy Meter</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 col-md-4">
                                                    <div class="header">
                                                        <h2>Month</h2>
                                                    </div>
                                                    <select name="sort" id="month_opt" class="form-select alarms-sort">
                                                        <option hidden>Choose a month</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 col-md-4">
                                                    <div class="header">
                                                        <h2>Year</h2>
                                                    </div>
                                                    <select name="sort" id="m_year_opt" class="form-select alarms-sort">
                                                        <option hidden>Choose a year</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-auto col-md-4 d-flex align-items-end query-toolbar-action">
                                                    <button type="button" class="btn btn-primary submit" onclick="get_month()">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card g-3 mb-3 no-radius custom_hide_card">
                                        <div class="body no-pb">                                            
                                            <div class="row text-center justify-content-center">
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="month_total_prod"></h3>
                                                            <span class="text-muted value-text">Total Production</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="month_total_insolation"></h3>
                                                            <span class="text-muted value-text">Insolation</span>
                                                        </div>
                                                    </div>
                                                </div> 
                                                
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="month_pr"></h3>
                                                            <span class="text-muted value-text">Performance Ratio</span>
                                                        </div>
                                                    </div>
                                                </div> 
        
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="month_co2"></h3>
                                                            <span class="text-muted value-text">CO<sub>2</sub> Avoided</span>
                                                        </div>
                                                    </div>
                                                </div>                                         
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row clearfix g-3 mb-3 custom_hide_card">
                                        <div class="col-lg-12">
                                            <div class="card no-radius-top">
                                                <div class="body">
                                                    <div class="header">
                                                        <h2 style="display: inline-block;">Meter KPI<small>Production | Irradiance | Power</small> </h2> 
                                                        <i class="fa fa-download download" onclick="downloadKPI()"  title="Download"></i>
                                                    </div>
                                
                                                    <div class="chartKPI">                        
                                                        <canvas id="month_kpi"></canvas>                        
                                                    </div>  
                                                </div>
                                            </div>
                                        </div>                     
                                    </div>
                                    
                                    <div class="card g-3 mb-3 custom_hide_card">
                                        <div class="body">
                                            <div class="header">
                                                <h2>Daily Production</h2>
                                            </div>                                    
                                            <div class="table-responsive">
                                                <table id="tbl_prod_daily" class="table table-bordered table-striped table-hover" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>Meter</th>
                                                            <th>Production (kWh)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> 
                                                    </tbody>
                                                </table>
                                            </div> 
                                        </div>
                                    </div>
                                    
                                    <div class="card g-3 mb-3 custom_hide_card">
                                        <div class="body">                                    
                                            <div class="header">
                                                <h2>Active Power</h2>
                                            </div>                                             
                                            <div class="table-responsive">
                                                <table id="tbl_apower_month" class="table table-bordered table-striped table-hover" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>Meter</th>
                                                            <th>Active Power (kW)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> 

                                                    </tbody>
                                                </table>
                                            </div>  
                                        </div>
                                    </div>  
                                    
                                    <div class="card g-3 custom_hide_card">
                                        <div class="body">                                    
                                            <div class="header">
                                                <h2>Insolation</h2>
                                            </div>                                             
                                            <div class="table-responsive">
                                                <table id="tbl_irradiance_month" class="table table-bordered table-striped table-hover" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Site</th>
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
                                
                                <div class="tab-pane" id="Year">
                                    <div class="card no-radius">
                                        <div class="body no-ptb">                                    
                                            <div class="row mb-3 g-3 query-toolbar align-items-end" style="position:relative;">
                                                <div class="col-lg-2 col-md-6">
                                                    <div class="header">
                                                        <h2>Choose site</h2>
                                                    </div>
                                                    <select name="sort" id="sites_opt_year" class="form-select alarms-sort" onchange="filter_meters(3)">
                                                        <option hidden>Choose a site</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="header">
                                                        <h2>Energy meter</h2>
                                                    </div>
                                                    <select name="sort" id="meters_opt_year" class="form-select alarms-sort">
                                                        <option hidden>Choose an Energy Meter</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 col-md-4">
                                                    <div class="header">
                                                        <h2>Year</h2>
                                                    </div>
                                                    <select name="sort" id="year_opt" class="form-select alarms-sort">
                                                        <option hidden>Choose a Year</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-auto col-md-4 d-flex align-items-end query-toolbar-action">
                                                    <button type="button" class="btn btn-primary submit" onclick="get_year()">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card g-3 mb-3 no-radius custom_hide_card">
                                        <div class="body no-pb">                                            
                                            <div class="row text-center justify-content-center">
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="year_total_prod"></h3>
                                                            <span class="text-muted value-text">Total Production</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="year_total_insolation"></h3>
                                                            <span class="text-muted value-text">Insolation</span>
                                                        </div>
                                                    </div>
                                                </div> 
                                                
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="year_pr"></h3>
                                                            <span class="text-muted value-text">Performance Ratio</span>
                                                        </div>
                                                    </div>
                                                </div> 
        
                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                    <div class="card text-center mb-3 card-box">
                                                        <div class="body">
                                                            <h3 id="year_co2"></h3>
                                                            <span class="text-muted value-text">CO<sub>2</sub> Avoided</span>
                                                        </div>
                                                    </div>
                                                </div>                                         
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row clearfix g-3 mb-3 custom_hide_card">
                                        <div class="col-lg-12">
                                            <div class="card no-radius-top">
                                                <div class="body">
                                                    <div class="header">
                                                        <h2 style="display: inline-block;">Meter KPI<small>Production | Irradiance | Power</small> </h2> 
                                                        <i class="fa fa-download download" onclick="downloadKPI()"  title="Download"></i>
                                                    </div>
                                
                                                    <div class="chartKPI">                        
                                                        <canvas id="year_kpi"></canvas>                        
                                                    </div>  
                                                </div>
                                            </div>
                                        </div>                     
                                    </div>
                                    
                                    <div class="card g-3 mb-3 custom_hide_card">
                                        <div class="body">
                                            <div class="header">
                                                <h2>Daily Production</h2>
                                            </div>                                    
                                            <div class="table-responsive">
                                                <table id="tbl_prod_monthly" class="table table-bordered table-striped table-hover" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>Meter</th>
                                                            <th>Production (kWh)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> 
                                                    </tbody>
                                                </table>
                                            </div> 
                                        </div>
                                    </div>
                                    
                                    <!--<div class="card custom_hide_card">-->
                                    <!--    <div class="body">                                    -->
                                    <!--        <div class="header">-->
                                    <!--            <h2>Active Power</h2>-->
                                    <!--        </div>                                             -->
                                    <!--        <div class="table-responsive">-->
                                    <!--            <table id="tbl_apower_year" class="table table-bordered table-striped table-hover" style="width:100%;">-->
                                    <!--                <thead>-->
                                    <!--                    <tr>-->
                                    <!--                        <th>Date/Time</th>-->
                                    <!--                        <th>Site</th>-->
                                    <!--                        <th>Meter</th>-->
                                    <!--                        <th>Active Power (kW)</th>-->
                                    <!--                    </tr>-->
                                    <!--                </thead>-->
                                    <!--                <tbody> -->

                                    <!--                </tbody>-->
                                    <!--            </table>-->
                                    <!--        </div>  -->
                                    <!--    </div>-->
                                    <!--</div>  -->
                                    
                                    <div class="card g-3 custom_hide_card">
                                        <div class="body">                                    
                                            <div class="header">
                                                <h2>Insolation</h2>
                                            </div>                                             
                                            <div class="table-responsive">
                                                <table id="tbl_irradiance_year" class="table table-bordered table-striped table-hover" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Site</th>
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
                                
                                <div class="tab-pane" id="Custom">
                                    <div class="card no-radius-top g-3 mb-3">
                                        <div class="body no-ptb">                                     
                                            <div class="row mb-3 g-3 query-toolbar align-items-end">
                                                <div class="col-lg-2 col-md-4">
                                                    <div class="header">
                                                        <h2>Choose site</h2>
                                                    </div>
                                                    <select name="sort" id="sites_opt_custom" class="form-select alarms-sort" onchange="filter_meters(4)">
                                                        <option hidden>Choose a site</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3 col-md-4">
                                                    <div class="header">
                                                        <h2>Start date</h2>
                                                    </div>
                                                    <input type="date" id="start_date" class="form-control query-date-input" onchange="start_date_validate()">
                                                </div>
                                                <div class="col-lg-3 col-md-4">
                                                    <div class="header">
                                                        <h2>End date</h2>
                                                    </div>
                                                    <input type="date" id="end_date" class="form-control query-date-input">
                                                </div>
                                            </div>
                                            
                                            <div class="row clearfix g-3 mb-3">
                                                <div class="col-lg-3 col-md-3" style="margin-top:26px;">
                                                    
                                                    <div class="header custom-header">
                                                            <h2 style="color:white; text-align:center">Meters</h2>
                                                    </div>
                                                    <div class="card text-center" style="height: 256px; overflow-y:auto">
                                                        
                                                        <div class="main-con" style="margin: 5px;border: none;">
                                                            <ul id="meters_opt_custom">
                                                                <li>Choose Site First</li>
                                                            </ul>
                                                        </div>                                               
                                                    </div>
                                                    
                                                    <div class="header custom-header">
                                                        <h2 style="color:white;">Meter Parameters</h2>
                                                    </div>
                                                    <div class="card text-center" style="height: 136px; overflow-y:auto">

                                                        <div class="main-con" style="margin: 5px;border: none;">
                                                            <div class="checkbox-container" id="con_meter_parameters">
                                                            <!--<div class="input-container">-->
                                                                <input class="datacheckbox meter_param" name="meter_params" type="checkbox" value="prod" onchange="showtype()">Production  &ensp;
                                                                
                                                                <select id="chart_type">
                                                                    <option value="line" selected="selected">Line Chart</option>
                                                                    <option value="bar">Bar Chart</option>
                                                                </select>
                                                                
                                                                <br>
                                                                <input class="datacheckbox meter_param" name="meter_params" type="checkbox" value="a_power">Active Power<br>
                                                            <!--</div>-->
                                                            
                                                            </div> 
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="header custom-header">
                                                        <h2 style="color:white;">Weather Station Parameters</h2>
                                                    </div>
                                                    
                                                    <div class="card text-center" style="height: 136px; overflow-y:auto">

                                                        <div class="main-con" style="margin: 5px;border: none;">
                                                            <div class="checkbox-container">
                                                            <!--<div class="input-container">-->
                                                                <input class="datacheckbox" id="irradiance_param" type="checkbox" value="irradiance">Irradiance<br>
                                                                <input class="datacheckbox" id="ambientTemp_param" type="checkbox" value="ambient_temp">Ambient Temperature<br>
                                                                <input class="datacheckbox" id="panelTemp_param" type="checkbox" value="panel_temp">Panel Temperature<br>
                                                            <!--</div>-->
                                                            
                                                            </div> 
                                                        </div>
                                                    </div>
                                                    
                                                    

                                                    
                                                    <button class="btn btn-outline-secondary" style="width:100%;" onclick="get_custom()"> Add to Chart</button>
                                                </div>    
                                                
                                                <div class="col-lg-9 col-md-9">
                                                    <div class="btn-chart">
                                                        <button class="btn btn-primary clear-btn" id="zoom_reset" onclick="resetZoomBtn()">Reset Zoom</button>
                                                        <button class="btn btn-primary clear-btn" onclick="removeData()">Remove Chart</button>
                                                        <i class="fa fa-download" onclick="downloadCustom()"  title="Download"></i>
                                                    </div>  
                                                    <div class="card">
                                                        <div class="body">
                                                            <div class="chartBox">
                                                                <canvas id="custom_chart"></canvas> 
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                            
                                    <!--Production Table-->
                                    
                                    <div class="card g-3 mb-3">
                                        <div class="body">
                                            <div class="col-lg-12">
                                                <div class="header">
                                                    <h2>Production</h2>
                                                </div>  
                                                <div class="table-responsive">
                                                    <table id="tbl_custom_prod" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>meter</th>
                                                            <th>Production</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                                                            
                                                    </tbody>
                                                </table>
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!--Active Power Table-->
                                    
                                    <div class="card g-3 mb-3">
                                        <div class="body">
                                            <div class="col-lg-12">
                                                <div class="header">
                                                    <h2>Active Power</h2>
                                                </div>                                                      
                                                <div class="table-responsive">
                                                    <table id="tbl_custom_active" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>meter</th>
                                                            <th>Active Power</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                                                            
                                                    </tbody>
                                                </table>
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!--Irradiance Table-->
                                    
                                    <div class="card g-3">
                                        <div class="body">
                                            <div class="col-lg-12">
                                                <div class="header">
                                                    <h2>Irradiance</h2>
                                                </div>                                                      
                                                <div class="table-responsive">
                                                    <table id="tbl_custom_irradiance" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th>Date/Time</th>
                                                            <th>Site</th>
                                                            <th>Irradiance</th>
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


<!-- Core bundles (re-define jQuery, so DataTables must come after) -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>

<!-- DataTables — must register against the final jQuery from libscripts -->
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>

<!-- Page JavaScript -->
<script src="assets/js/pages/query.js"></script>
<!--<script src="assets/js/pages/tables/jquery-datatable.js"></script>-->
<!--<script src="assets/bundles/datatablescripts.bundle.js"></script>-->

<!--<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>-->
<!--<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>-->
<!--<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>-->
</body>
</html>
