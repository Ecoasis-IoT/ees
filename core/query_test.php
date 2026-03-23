<?php

include("scripts/auth.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

<!--<link rel="stylesheet" href="assets/css/dataTables.min.css">-->

<!--CUSTOM CSS-->
<link rel="stylesheet" href="assets/css/custom.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

<!-- QUERY CSS-->
<link rel="stylesheet" href="assets/css/query.css">


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

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


<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>


<style>
    
button.dt-button, div.dt-button, a.dt-button, input.dt-button {
    border-radius: 5px;
    color: white;
    background-color: blue;
}

button.dt-button:hover:not(.disabled), div.dt-button:hover:not(.disabled), a.dt-button:hover:not(.disabled), input.dt-button:hover:not(.disabled) {
    border: 1px solid #666;
    background-color: black;
}

.btn-chart {
    display: flex;
    justify-content: flex-end;
    margin-right: 20px;
    margin-bottom: 7px;
    margin-top: 10px;
}    

.clear-btn {
    margin-right: 15px;
}

.fa-download {
    font-size: 18px; 
    float:right;
    cursor:pointer;
    margin-top: 9px;
}

    
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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a>Reports</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Reports</li>
                            <li class="breadcrumb-item active">Energy Meters</li>
                        </ul>
                    </div>            
                </div>
            </div>
     
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12 col-md-12">
                    <div class="card no-bg">
                        <div class="body p-0">
                            <div class="card no-radius-bottom">
                                <div class="body">
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
                                            <div class="row mb-3">
                                                <div class="col-lg-2 col-12 col-sm-3">
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                                    </div>           
                
                                                    <select name="sites_opt_day" id="sites_opt_day" class="alarms-sort" onchange="filter_meters(1)">
                                                        <option hidden>Choose a site</option>
                                                    </select>    
                                                </div>
                                                    
                                                <div class="col-lg-3 col-12 col-sm-5" style="margin-right:-64px;">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE ENERGY METER</h2> 
                                                    </div>           
                
                                                    <select name="meters_opt_day" id="meters_opt_day" class="alarms-sort">
                                                        <option hidden>Choose an Energy Meter</option>
                                                        
                                                    </select>
                                                </div>
                                                
                                                <div class="col-lg-2 col-6 col-sm-3">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE DATE</h2> 
                                                    </div>           
                
                                                    <input type="date" id="date_day" style="padding:6px;">
        
                                                </div>
                                                
                                                
                                                <div class="col-lg-2 col-6 col-sm-2">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;color:transparent;"></h2> 
                                                    </div>           
                
                                                    <button class="btn btn-primary submit" onclick="get_day()">Submit</button>         
        
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
                                            <div class="row mb-3" style="position:relative;">
                                                <div class="col-lg-2 col-md-3">
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="sites_opt_month" class="alarms-sort"  onchange="filter_meters(2)">
                                                        <option hidden>Choose a site</option>

                                                    </select>    
                                                </div>
                                                    
                                                <div class="col-lg-3 col-md-5" style="margin-right:-64px;">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE ENERGY METER</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="meters_opt_month" class="alarms-sort">
                                                        <option hidden>Choose an Energy Meter</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-lg-2 col-md-3">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE MONTH</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="month_opt" class="alarms-sort">
                                                        <option hidden>Choose a month</option>
                                                    </select>
                                                </div>  
                                                
                                                <div class="col-lg-2 col-6 col-sm-2">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;color:transparent;"></h2> 
                                                    </div>           
                
                                                    <button class="btn btn-primary submit" onclick="get_month()">Submit</button>         
        
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
                                            <div class="row mb-3" style="position:relative;">
                                                <div class="col-lg-2 col-md-3">
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="sites_opt_year" class="alarms-sort"  onchange="filter_meters(3)">
                                                        <option hidden>Choose a site</option>

                                                    </select>    
                                                </div>
                                                    
                                                <div class="col-lg-3 col-md-5" style="margin-right:-64px;">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE ENERGY METER</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="meters_opt_year" class="alarms-sort">
                                                        <option hidden>Choose an Energy Meter</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-lg-2 col-md-3">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE YEAR</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="year_opt" class="alarms-sort">
                                                        <option hidden>Choose a Year</option>
                                                    </select>
                                                </div> 
                                                
                                                <div class="col-lg-2 col-6 col-sm-2">    
                                                    <div class="header">
                                                        <h2 style="display:inline-block;color:transparent;"></h2> 
                                                    </div>           
                
                                                    <button class="btn btn-primary submit" onclick="get_year()">Submit</button>         
        
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
                                            <div class="row mb-3">
                                                <div class="col-lg-2 col-md-3">
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                                    </div>           
                
                                                    <select name="sort" id="sites_opt_custom" class="alarms-sort"  onchange="filter_meters(4)">
                                                        <option hidden>Choose a site</option>
                                                    </select>    
                                                </div>
                                                    
                                                <div class="col-lg-3 col-md-5" style="margin-right:-120px;">   
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">START DATE</h2> 
                                                    </div>
                                                    <input type="date" id="start_date" onchange="start_date_validate()" style="padding:6px;">                                        
                                                </div>
                                                
                                                <div class="col-lg-3 col-md-5">   
                                                    <div class="header">
                                                        <h2 style="display:inline-block;">END DATE</h2> 
                                                    </div>
                                                    <input type="date" id="end_date" style="padding:6px;">                                        
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
                                                                <input class="datacheckbox meter_param" name="meter_params" type="checkbox" value="prod" onchange="showtype()">Production
                                                                
                                                                <select id="chart_type">
                                                                    <option value="line" selected="selected">Line</option>
                                                                    <option value="bar">Bar</option>
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
                                                    <div class="card" style="height: 0%;">
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

<!-- ================================================================================================================================= -->

<script>

$("#con_meter_parameters").hide();
$("#chart_type").hide()

$(".meter_param").change(function () {
    $(".meter_param").not(this).prop('checked', false);
});



function show_param(){
    $("#con_meter_parameters").show();
}

function showtype(){
    $("#chart_type").show();
}

$("#zoom_reset").hide();

function show_zoom(){
    $("#zoom_reset").show();
}

$(function hideCards(){
    $(".custom_hide_card").css('display','none');
});

//Initialize DataTables
 
var tbl_hourly = $('#tbl_prod_hourly').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
    
});

var tbl_ir_day = $('#tbl_irradiance_day').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});


var tbl_power_day = $('#tbl_apower_day').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_daily = $('#tbl_prod_daily').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_ir_month = $('#tbl_irradiance_month').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});


var tbl_power_month = $('#tbl_apower_month').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_monthly = $('#tbl_prod_monthly').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_ir_year = $('#tbl_irradiance_year').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});


var tbl_custom_prod = $('#tbl_custom_prod').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_custom_active = $('#tbl_custom_active').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_custom_irradiance = $('#tbl_custom_irradiance').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});

$(function sites_name(){

        $.ajax({
            type: "POST",
            url: "scripts/get_all_sites.php",
            data: {
                
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                // console.log(data.data);
                
                let sites = data.data;
                
                for(var i = 0; i < sites.length; i++){
                    
                    let option = "<option value = '"+ sites[i][0] +"'>"+ sites[i][1] +"</option>";
                    
                    $('#sites_opt_day').append(option);
                    $('#sites_opt_month').append(option);
                    $('#sites_opt_year').append(option);
                    $('#sites_opt_custom').append(option);
                }
            }
        });
});

function intializeOptions(){
    
    let default_opt = "<option hidden>Choose an Energy Meter</option>";
    
    $("#meters_opt_day").empty();
    $("#meters_opt_day").append(default_opt);
    
    $("#meters_opt_month").empty();
    $("#meters_opt_month").append(default_opt);
    
    $("#meters_opt_year").empty();
    $("#meters_opt_year").append(default_opt);
    
    $("#meters_opt_custom").empty();
}

function filter_meters(tab){
    intializeOptions();
    
    let all_months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    let site;
    let meter_opt;
    
    if(tab == 1){
        site = document.getElementById("sites_opt_day").value; 
        meter_opt = "#meters_opt_day";
        // console.log(site);
    }
    else if(tab == 2){
        site = document.getElementById("sites_opt_month").value; 
        meter_opt = "#meters_opt_month";
        
    }
    else if(tab == 3){
        site = document.getElementById("sites_opt_year").value; 
        meter_opt = "#meters_opt_year";
        
    }
    else if(tab == 4){
        site = document.getElementById("sites_opt_custom").value; 
        meter_opt_custom = "#meters_opt_custom";
        
    }

    // let meters = document.getElementById("meters_opt");
    $.ajax({
            type: "POST",
            url: "scripts/get_query_meters.php",
            data: {
                "site": site
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                
                if(data.status != undefined){
                    alert("Failed to retrieve meters for this site!");
                }
                else{
                    // $("#meters_opt").empty();
                    // let default_opt = "<option hidden>Choose Meter</option>";
                    // $("#meters_opt").append(default_opt);
                    
                    // meters available
                    
                    let arr_meters = data.meters;
                    let dates = data.dates;
                    
                    if(tab < 4){
                    
                        for (const key in arr_meters) {
                            let rec = arr_meters[key];
                            
                            $(meter_opt).append($('<option>', {value: rec.meter_id, text: rec.name}));
                            
                        }
                    }
                    else{
                        
                          
                        
                        let ul = document.getElementById("meters_opt_custom");
                        
                        for (const key in arr_meters) {
                            let rec = arr_meters[key];
                            
                            // $(meter_opt_custom).append($('<option>', {value: rec.meter_id, text: rec.name}));
                            
                            let chkbox = document.createElement('INPUT');
                            chkbox.setAttribute('type', 'checkbox');
                            chkbox.setAttribute('onchange', 'show_param()');
                            chkbox.setAttribute('name', 'custom_meters');
                            chkbox.setAttribute('value', rec.meter_id);
                            
                            let li = document.createElement("li");
                            let description = document.createTextNode(rec.name);
                            li.appendChild(chkbox);
                            li.appendChild(description);
                            ul.appendChild(li);
                            
                            
                        }
                        
                        
                        
                    }
                    
                    
                    if(tab == 2){
                        let default_opt = "<option hidden>Choose a Month</option>";
                        $("#month_opt").empty();
                        $("#month_opt").append(default_opt);
                        
                        for(let i = dates.startmth; i <= dates.endmth; i++){
                         
                            $('#month_opt').append($('<option>', {value: i, text: all_months[i-1]}));
                            
                        }
                    }
                    else if(tab == 3){
                        let default_year = "<option hidden>Choose a Year</option>";
                        $("#year_opt").empty();
                        $("#year_opt").append(default_year);
                        
                        for(let i = dates.startyr; i <= dates.endyr; i++){
                         
                            $('#year_opt').append($('<option>', {value: i, text: i}));
                            
                        }
                        
                        
                    }
                    
                }
            
                
                
            },
            error: function(req, err){ console.log('error' + err)}
        });
}


//============================================================Day Script=============================================================

function get_day(){
    
    let chart_labels = [];
    let kpi_irradiance = [];
    let kpi_prod = [];
    let kpi_active_power = [];
    
    $.ajax({
        type: "POST",
        url: "scripts/query_day.php",
        async : false,
        data: {
            "site": document.getElementById("sites_opt_day").value,
            "meter": document.getElementById("meters_opt_day").value,
            "date": document.getElementById("date_day").value
        },
        success: function(dataResult) {
            
            // console.log(dataResult);
            var data = JSON.parse(dataResult);
            
            
            //production
            let prod = data.prod;
            
            let total_prod_day = 0;
            
            tbl_hourly.rows().remove().draw();
            
            for(var i = 0; i < prod.length; i++){
                
                total_prod_day += parseFloat(prod[i].production);
                
                chart_labels.push(prod[i].datetime);
                
                kpi_prod.push({"x": prod[i].datetime, "y": prod[i].production});
                
                tbl_hourly.row.add([prod[i].datetime, data.site_name, prod[i].meter_name, prod[i].production]).draw();
            }
            
            tbl_hourly.columns.adjust().draw();
            
            if(total_prod_day < 1000){
                document.getElementById('day_total_prod').innerHTML = total_prod_day.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('day_total_prod').innerHTML = (total_prod_day/1000).toFixed(2) + " MWh";
            }
            
            //Irradiance
            let irradiance = data.irradiance;
            let total_day_insolation = 0;
            
            tbl_ir_day.rows().remove().draw();
            
            for(var i = 0; i < irradiance.length; i++){
                
                total_day_insolation += parseFloat(irradiance[i].insolation);
                
                kpi_irradiance.push({"x": irradiance[i].date, "y": irradiance[i].irradiance});
                
                tbl_ir_day.row.add([irradiance[i].date, data.site_name, irradiance[i].irradiance, irradiance[i].insolation, irradiance[i].ambient_temp, irradiance[i].panel_temp]).draw();
                                
                // var row = "<tr><td>"+ irradiance[i].date +"</td><td>" + data.site_name + "</td><td>" + irradiance[i].irradiance + "</td><td>" + irradiance[i].insolation + "</td><td>" + irradiance[i].ambient_temp + "</td><td>" + irradiance[i].panel_temp + "</td></tr>";
                    
                // $('#tbl_irradiance_day tbody').append(row);
                
            }
            
            tbl_ir_day.columns.adjust().draw();
            
            document.getElementById('day_total_insolation').innerHTML = total_day_insolation.toFixed(2) + " kWh/m<sup>2</sup>";
            
            //PR Ratio
            let day_pr = (total_prod_day/(total_day_insolation * data.site_capacity)) * 100;
            document.getElementById('day_pr').innerHTML = day_pr.toFixed(0) + "%";
            
            //CO2 Avoided
            
            let day_co2_avoided = (total_prod_day * 0.966);
            document.getElementById('day_co2').innerHTML = day_co2_avoided.toFixed(0) + "Kg";
            
            //Active Power
            
            let active_power = data.active_power;
            
            tbl_power_day.rows().remove().draw();
            
            for(var i = 0; i < active_power.length; i++){
                
                if(active_power[i].active_power < 0){
                    kpi_active_power.push({"x": active_power[i].date, "y": 0});
                }
                else{
                    kpi_active_power.push({"x": active_power[i].date, "y": active_power[i].active_power});
                }
                
                tbl_power_day.row.add([active_power[i].date, data.site_name, active_power[i].meter_name, active_power[i].active_power]).draw();
                
                // var row = "<tr><td>"+ active_power[i].date +"</td><td>" + data.site_name + "</td><td>" + active_power[i].meter_name + "</td><td>"  + active_power[i].active_power + "</td></tr>";
                    
                // $('#tbl_apower_day tbody').append(row);
                
            }
            
            tbl_power_day.columns.adjust().draw();
            
            // console.log(chart_labels);
            // console.log(kpi_active_power);
            // console.log(kpi_irradiance);
            // console.log(kpi_prod);


            // render KPI Chart
            const dataKPIday = {
              labels: chart_labels,
              datasets: [
              {
                type: 'line',
                label: 'Active Power (kW)',
                yAxisID: 'A',
                // data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
                data: kpi_active_power,
                fill: false,
                backgroundColor: '#36648B',
                borderColor: '#36648B',
                tension: 0.4,
                pointRadius: 1,
              },
                {
                type: 'line',
                label: 'Irradiance (W/m2)',
                yAxisID: 'B',
                data: kpi_irradiance,
                // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
                fill: false,
                backgroundColor: '#F2BB46',
                borderColor: '#F2BB46',
                title: 'Irradiance (W/2)',
                tension: 0.4,
                pointRadius: 1,
              },      
              {
                type: 'bar',
                label: 'Production (kWh)',
                yAxisID: 'C',
                data: kpi_prod,
                // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                backgroundColor: '#CA5952',
                borderColor: '#CA5952',
                barThickness:30,
              }
              ]
            };
            
                        
            const configKPIday = {
              data: dataKPIday,
              options: {
                enabled: true,
                scales: {       
                    A: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Active Power (kW)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
                        }
                    },
                    B: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Irradiance (W/m2)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
                        }
                    },
                    C: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Production (kWh)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }
                        }            
                    },        
                    x: {
                      title: {
                        display: true,
                        text: 'Time',
                        font: {
                            size: 13,
                            weight:'bold'
                        }            
                      },
                        // type: 'time',
                        // time: {
                        //   unit: 'second'
                        // }              
                      type: 'time',
                      time: {
                        parser: 'HH:mm:ss',
                        unit: 'hour',
                        tooltipFormat: 'HH:mm',
                        displayFormats: {
                          hour: 'HH:mm'
                        }                
                    },              
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                    },
                },
                maintainAspectRatio:false,
                responsive: true,
              },
            //   plugins: [white_back],
            };
            
            let chartStatus = Chart.getChart("day_kpi"); // <canvas> id
            if (chartStatus != undefined) {
              chartStatus.destroy();
            }
                                
            
            const day_kpi = new Chart(
              document.getElementById('day_kpi'),
              configKPIday
            );       
            
            $("#Day .custom_hide_card").css('display','block');
            
        }
    });
    
}

//===========================================MONTH SCRIPT=========================================================

function get_month(){
    
    let chart_labels = [];
    let kpi_insolation = [];
    let kpi_prod = [];
    let kpi_pr = [];
    
    $.ajax({
        type: "POST",
        url: "scripts/query_month.php",
        async : true,
        data: {
            "site": document.getElementById("sites_opt_month").value,
            "meter": document.getElementById("meters_opt_month").value,
            "month": document.getElementById("month_opt").value
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            
            //production
            let prod = data.prod;
            
            let total_prod_month = 0;
            
            tbl_daily.rows().remove().draw();
            
            for(var i = 0; i < prod.length; i++){
                
                total_prod_month += parseFloat(prod[i].production);
                
                chart_labels.push(prod[i].datetime);
                
                kpi_prod.push({"x": prod[i].datetime, "y": prod[i].production});
                
                tbl_daily.row.add([prod[i].datetime, data.site_name, prod[i].meter_name, prod[i].production]).draw();
            }
            
            tbl_daily.columns.adjust().draw();
            
            if(total_prod_month < 1000){
                document.getElementById('month_total_prod').innerHTML = total_prod_month.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('month_total_prod').innerHTML = (total_prod_month/1000).toFixed(2) + " MWh";
            }
            
            // //Irradiance
            let irradiance = data.irradiance;
            let total_month_insolation = 0;
            let pr;
            
            tbl_ir_month.rows().remove().draw();
            
            for(var i = 0; i < irradiance.length; i++){
                
                total_month_insolation += parseFloat(irradiance[i].insolation);
                
                kpi_insolation.push({"x": irradiance[i].date, "y": irradiance[i].insolation});
                
                pr = (prod[i].production/(irradiance[i].insolation * data.site_capacity)) * 100;
                
                kpi_pr.push({"x": irradiance[i].date, "y": pr});
                
                tbl_ir_month.row.add([irradiance[i].date, data.site_name, irradiance[i].insolation]).draw();
                
            }
            
            tbl_ir_month.columns.adjust().draw();
            
            document.getElementById('month_total_insolation').innerHTML = total_month_insolation.toFixed(2) + " kWh/m<sup>2</sup>";
            
            // //PR Ratio
            let month_pr = (total_prod_month/(total_month_insolation * data.site_capacity)) * 100;
            document.getElementById('month_pr').innerHTML = month_pr.toFixed(0) + "%";
            
            //CO2 Avoided
            
            let month_co2_avoided = (total_prod_month * 0.966);
            document.getElementById('month_co2').innerHTML = month_co2_avoided.toFixed(0) + "Kg";
            
            //Active Power
            
            let active_power = data.active_power;
            
            tbl_power_day.rows().remove().draw();
            
            for(var i = 0; i < active_power.length; i++){
                
                
                // kpi_active_power.push({"x": active_power[i].date, "y": active_power[i].active_power});
                
                tbl_power_month.row.add([active_power[i].date, data.site_name, active_power[i].meter_name, active_power[i].active_power]).draw();
                
            }
            
            tbl_power_day.columns.adjust().draw();
            
            // console.log(chart_labels);
            // console.log(kpi_active_power);
            // console.log(kpi_insolation);
            // console.log(kpi_prod);


            // render KPI Chart
            const dataKPIday = {
              labels: chart_labels,
              datasets: [
              {
                type: 'line',
                label: 'PR (%)',
                yAxisID: 'A',
                // data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
                data: kpi_pr,
                fill: false,
                backgroundColor: '#7CAF57',
                borderColor: '#7CAF57',
                tension: 0.4,
                pointRadius: 1,
              },
                {
                type: 'line',
                label: 'Insolation (kWh/m2)',
                yAxisID: 'B',
                data: kpi_insolation,
                // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
                fill: false,
                backgroundColor: '#F2BB46',
                borderColor: '#F2BB46',
                title: 'Insolation (kWh/m2)',
                tension: 0.4,
                pointRadius: 1,
              },      
              {
                type: 'bar',
                label: 'Production (kWh)',
                yAxisID: 'C',
                data: kpi_prod,
                // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                backgroundColor: '#CA5952',
                borderColor: '#CA5952',
                barThickness:30,
              }
              ]
            };
            
                        
            const configKPIday = {
              data: dataKPIday,
              options: {
                enabled: true,
                scales: {       
                    A: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'PR (%)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
                        }            
                    },
                    B: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Insolation (kWh/m2)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
                        }
                    },
                    C: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Production (kWh)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }
                        }            
                    },        
                    x: {
                      title: {
                        display: true,
                        text: 'Date',
                        font: {
                            size: 13,
                            weight:'bold'
                        }            
                      },
                        // type: 'time',
                        // time: {
                        //   unit: 'second'
                        // }              
                      type: 'time',
                      time: {
                        parser: 'yyyy-MM-dd',
                        unit: 'day',
                        tooltipFormat: 'dd-MM',
                        displayFormats: {
                          day: 'dd-MM'
                        }                
                    }              
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                    },
                },
                maintainAspectRatio:false,
                responsive: true,
              },
            //   plugins: [white_back],
            };
            
            let chartStatus = Chart.getChart("month_kpi"); // <canvas> id
            if (chartStatus != undefined) {
              chartStatus.destroy();
            }
                                
            
            const month_kpi = new Chart(
              document.getElementById('month_kpi'),
              configKPIday
            );       
            
            $("#Month .custom_hide_card").css('display','block');
            
        }
    });
    
}

//===========================================YEAR SCRIPT=========================================================

function get_year(){
    
    let chart_labels = [];
    let kpi_insolation = [];
    let kpi_prod = [];
    let kpi_pr = [];
    
    let all_months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    $.ajax({
        type: "POST",
        url: "scripts/query_year.php",
        async : true,
        data: {
            "site": document.getElementById("sites_opt_year").value,
            "meter": document.getElementById("meters_opt_year").value,
            "year": document.getElementById("year_opt").value
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            
            //production
            let prod = data.prod;
            
            let total_prod_year = 0;
            
            tbl_monthly.rows().remove().draw();
            
            for(var i = 0; i < prod.length; i++){
                
                total_prod_year += parseFloat(prod[i].production);
                
                chart_labels.push(all_months[prod[i].datetime-1]);
                
                kpi_prod.push({"x": all_months[prod[i].datetime - 1], "y": prod[i].production});
                
                tbl_monthly.row.add([all_months[prod[i].datetime-1], data.site_name, prod[i].meter_name, prod[i].production]).draw();
            }
            
            tbl_monthly.columns.adjust().draw();
            
            if(total_prod_year < 1000){
                document.getElementById('year_total_prod').innerHTML = total_prod_year.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('year_total_prod').innerHTML = (total_prod_year/1000).toFixed(2) + " MWh";
            }
            
            // //Irradiance
            let irradiance = data.irradiance;
            let total_year_insolation = 0;
            let pr;
            
            tbl_ir_year.rows().remove().draw();
            
            for(var i = 0; i < irradiance.length; i++){
                
                total_year_insolation += parseFloat(irradiance[i].insolation);
                
                kpi_insolation.push({"x": all_months[irradiance[i].date - 1], "y": irradiance[i].insolation});
                
                pr = (prod[i].production/(irradiance[i].insolation * data.site_capacity)) * 100;
                
                kpi_pr.push({"x": all_months[irradiance[i].date - 1], "y": pr});
                
                tbl_ir_year.row.add([all_months[irradiance[i].date - 1], data.site_name, irradiance[i].insolation]).draw();
                
            }
            
            tbl_ir_year.columns.adjust().draw();
            
            document.getElementById('year_total_insolation').innerHTML = total_year_insolation.toFixed(2) + " kWh/m<sup>2</sup>";
            
            // //PR Ratio
            let year_pr = (total_prod_year/(total_year_insolation * data.site_capacity)) * 100;
            document.getElementById('year_pr').innerHTML = year_pr.toFixed(0) + "%";
            
            //CO2 Avoided
            
            let year_co2_avoided = (total_prod_year * 0.966);
            document.getElementById('year_co2').innerHTML = year_co2_avoided.toFixed(0) + "Kg";
            
            //Active Power
            
            // let active_power = data.active_power;
            
            // tbl_power_day.rows().remove().draw();
            
            // for(var i = 0; i < active_power.length; i++){
                
                
            //     // kpi_active_power.push({"x": active_power[i].date, "y": active_power[i].active_power});
                
            //     tbl_power_year.row.add([active_power[i].date, data.site_name, active_power[i].meter_name, active_power[i].active_power]).draw();
                
            // }
            
            // tbl_power_day.columns.adjust().draw();
            
            // console.log(chart_labels);
            // console.log(kpi_active_power);
            // console.log(kpi_insolation);
            // console.log(kpi_prod);


            // render KPI Chart
            const dataKPIday = {
              labels: chart_labels,
              datasets: [
              {
                type: 'line',
                label: 'PR (%)',
                yAxisID: 'A',
                // data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
                data: kpi_pr,
                fill: false,
                backgroundColor: '#7CAF57',
                borderColor: '#7CAF57',
                tension: 0.4,
                pointRadius: 1,
              },
                {
                type: 'line',
                label: 'Insolation (kWh/m2)',
                yAxisID: 'B',
                data: kpi_insolation,
                // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
                fill: false,
                backgroundColor: '#F2BB46',
                borderColor: '#F2BB46',
                title: 'Insolation (kWh/m2)',
                tension: 0.4,
                pointRadius: 1,
              },      
              {
                type: 'bar',
                label: 'Production (kWh)',
                yAxisID: 'C',
                data: kpi_prod,
                // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                backgroundColor: '#CA5952',
                borderColor: '#CA5952',
                barThickness:30,
              }
              ]
            };
            
                        
            const configKPIday = {
              data: dataKPIday,
              options: {
                enabled: true,
                scales: {       
                    A: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'PR (%)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
                        }            
                    },
                    B: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Insolation (kWh/m2)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
                        }
                    },
                    C: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Production (kWh)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }
                        }            
                    },        
                    x: {
                      title: {
                        display: true,
                        text: 'Date',
                        font: {
                            size: 13,
                            weight:'bold'
                        }            
                      }
                        // type: 'time',
                        // time: {
                        //   unit: 'second'
                        // }              
                    //   type: 'time',
                    //   time: {
                    //     parser: 'yyyy-MM-dd',
                    //     unit: 'day',
                    //     tooltipFormat: 'dd-MM',
                    //     displayFormats: {
                    //       day: 'dd-MM'
                    //     }                
                    // }              
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                    },
                },
                maintainAspectRatio:false,
                responsive: true,
              },
            //   plugins: [white_back],
            };
            
            let chartStatus = Chart.getChart("year_kpi"); // <canvas> id
            if (chartStatus != undefined) {
              chartStatus.destroy();
            }
                                
            
            const year_kpi = new Chart(
              document.getElementById('year_kpi'),
              configKPIday
            );       
            
            $("#Year .custom_hide_card").css('display','block');
            
        }
    });
    
}

// White background plugin
const white_back = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart, args, options) => {
    const {ctx} = chart;
    ctx.save();
    ctx.globalCompositeOperation = 'destination-over';
    ctx.fillStyle = options.color || 'white';
    ctx.fillRect(0, 0, chart.width, chart.height);
    ctx.restore();
  }
};  


//===========================================CUSTOM QUERY SCRIPT=========================================================

//Create Custom chart

    const zoomOptions = {
      pan: {
        enabled: true,
        modifierKey: 'ctrl',
      },
      zoom: {
        drag: {
          enabled: true
        },
        mode: 'x',
      },
    };
    
    
    const configKPI = {
      options: {
        enabled: true,
        scales: {       
            A: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Active Power (kW)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }                
                },
                grid:{
                    display:false
                }            
            },
            B: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Irradiance (W/m2)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }                
                },
                grid:{
                    display:false
                }
            },
            C: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Production (kWh)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }
                }            
            },
            D: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Temperature (°C)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }
                },
                grid:{
                    display:false
                }
            },
            x: {
              title: {
                display: true,
                text: 'Date/Time',
                font: {
                    size: 13,
                    weight:'bold'
                }            
              },
              type: 'time',
              parsing: true,
              time: {
                    parser: 'yyyy-MM-dd HH:mm:ss',
                    unit: 'hour',
                    tooltipFormat: 'HH:mm',
                    displayFormats: {
                      hour: 'dd-MM-yyyy HH:mm'
                    }                
                }
              
              
            },              
            },
            plugins: {
                zoom: zoomOptions,
                legend: {
                    position: 'bottom',
                    display: true,
                },
                tooltip: {
                    enabled: true,
                },
            }
        },
        plugins: [white_back],
        maintainAspectRatio:true,
        responsive: true,
        // interaction: {
        //     mode: 'index'
        // },
      
    };
    
    // render init block
    custom_kpi = new Chart(
      document.getElementById('custom_chart'),
      configKPI
    );


function start_date_validate(){
    
    document.getElementById("end_date").value = "";
    
    document.getElementById("end_date").setAttribute("min", document.getElementById("start_date").value); 
    
}

var custom_kpi;
var color_count = 0;

function get_custom(){
    
    let chart_colors = ['#3366cc','#dc3912','#ff9900','#109618','#990099','#0099c6','#dd4477','#66aa00','#b82e2e','#316395','#994499','#22aa99','#aaaa11','#6633cc','#e67300','#8b0707','#651067','#329262','#5574a6','#3b3eac','#b77322','#16d620','#b91383','#f4359e','#9c5935','#a9c413','#2a778d','#668d1c','#bea413','#0c5922','#743411'];
    
    let chart_labels = [];
    let kpi_irradiance = [];
    let kpi_prod = [];
    let kpi_active = [];
    let kpi_ambientTemp = [];
    let kpi_panelTemp = [];
    
    site = document.getElementById("sites_opt_custom").value; 
    
    let inputs = document.getElementsByName('custom_meters');
    
    // let meters = [];
    let meters = "";
    let arr_meters = [];
    
    for(let i = 0; i < inputs.length; i++){
        
        if(inputs[i].checked){
            // meters.push(inputs[i].value);
            meters += inputs[i].value + ",";
            arr_meters.push(inputs[i].value);
        }
        
    }
    
    meters = meters.slice(0,-1);
    
    let params = document.getElementsByName('meter_params');
    let parameter = "";
    
    for(let i = 0; i < params.length; i++){
        
        if(params[i].checked){
            parameter = params[i].value;
        }
        
    }
    
    let input_start = document.getElementById("start_date").value;
    let input_end = document.getElementById("end_date").value;

    //Irradiance Parameter
    
    let irr_param = 0; 
    
    if(document.getElementById('irradiance_param').checked == true){
        irr_param = 1;
    }
    
    //Ambient Temperature Parameter
    
    let amb_param = 0;
    
    if(document.getElementById('ambientTemp_param').checked == true){
        amb_param = 1;
    }
    
    //Panel Temperature Parameter
    
    let panel_param = 0;
    
    if(document.getElementById('panelTemp_param').checked == true){
        panel_param = 1;
    }
    
    
    if(meters.length < 1 && irr_param == 0){
        alert("No meter Selected!");
    }
    else if(parameter == "" && irr_param == 0){
        alert("No parameter Selected!");
    }
    else if(input_start == ""){
        alert("No Start Date Selected!");
    }
    else if(input_end == ""){
        alert("No End Date Selected!");
    }
    else{
        
        
        let start_date = new Date(input_start).toISOString().split('T')[0];
        let end_date = new Date(input_end).toISOString().split('T')[0];
        
        // console.log(site);
        // console.log(meters);
        // console.log(parameter);
        // console.log(start_date);
        // console.log(end_date);
        
        let meter_name;
        
        $.ajax({
            type: "POST",
            url: "scripts/query_custom_test.php",
            data: {
                
                "site": site,
                "meters": meters,
                "arr_meters": arr_meters,
                "param": parameter,
                "start_date": start_date,
                "end_date": end_date,
                "irradiance": irr_param,
                "ambientTemp": amb_param,
                "panelTemp": panel_param
            },
            success: function(dataResult) {
                var json = JSON.parse(dataResult);
                
                let data = json.data;
                
                console.log(data);
                
                if(parameter == "prod"){
                    let chartType = document.getElementById("chart_type").value;
                    
                    for(let count = 0; count < arr_meters.length; count++){
                        
                        let prod_dataset = {
                            type: chartType,
                            yAxisID: 'C',
                            tension: 0.4,
                            pointRadius: 1
                        };
                        
                        meter_name = "";
                        chart_labels = [];
                        kpi_prod = [];
                        
                        for(let i = 0; i < data.length; i++){
                            
                            if(parseInt(data[i].meter_id) == parseInt(arr_meters[count])){
                                meter_name = data[i].meter_name;
                                chart_labels.push(data[i].datetime);
                                kpi_prod.push({"x": data[i].datetime, "y": data[i].production});
                                tbl_custom_prod.row.add([data[i].datetime, json.site_name, data[i].meter_name, data[i].production]).draw();
                            }
                            
                        }
                        
                        // console.log(kpi_prod);
                        
                        let color_id = color_count % 31;
                        if(chartType == "bar"){
                            // fill: true,
                            prod_dataset['fill'] = true;
                            
                        }
                        prod_dataset['backgroundColor'] = chart_colors[color_id];
                        prod_dataset['borderColor'] = chart_colors[color_id];
                        prod_dataset['label'] = meter_name + " - Production";
                        prod_dataset['order'] = 3;
                        prod_dataset['data'] = kpi_prod;
                        
                        
                        addData(chart_labels, prod_dataset);
                        color_count += 1;
                        
                    }
        
                }
                else if(parameter == "a_power"){
                    
                    for(let count = 0; count < arr_meters.length; count++){
                        
                        let active_dataset = {
                            type: 'line',
                            yAxisID: 'A',
                            fill: false,
                            tension: 0.4,
                            pointRadius: 1
                        };
                        
                        meter_name = "";
                        chart_labels = [];
                        kpi_active = [];
                        
                        for(let i = 0; i < data.length; i++){
                            
                            if(parseInt(data[i].meter_id) == parseInt(arr_meters[count])){
                                meter_name = data[i].meter_name;
                                chart_labels.push(data[i].datetime);
                                kpi_active.push({"x": data[i].datetime, "y": data[i].active_power});
                                tbl_custom_active.row.add([data[i].datetime, json.site_name, data[i].meter_name, data[i].active_power]).draw();
                            }
                            
                        }
                        
                        // console.log(kpi_prod);
                        
                        let color_id = color_count % 31;
                        
                        active_dataset['backgroundColor'] = chart_colors[color_id];
                        active_dataset['borderColor'] = chart_colors[color_id];
                        active_dataset['label'] = meter_name + " - Active Power";
                        active_dataset['order'] = 2;
                        active_dataset['data'] = kpi_active;
                        
                        addData(chart_labels, active_dataset);
                        color_count += 1;
                        
                    }
                    
                }
                
                //get irradiance data if checked
                if(irr_param == 1){
                    
                    let irr_dataset = {
                        type: 'line',
                        yAxisID: 'B',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 1
                    };
                    
                    kpi_irradiance = [];
                    
                    for(let i = 0; i < data.length; i++){
                        
                        if(parseInt(data[i].meter_id) == 99 && ("irradiance" in data[i])){
                            
                            chart_labels.push(data[i].datetime);
                            kpi_irradiance.push({"x": data[i].datetime, "y": data[i].irradiance});
                            tbl_custom_irradiance.row.add([data[i].datetime, json.site_name, data[i].irradiance]).draw();
                        }
                        
                    }
                    
                    // console.log(kpi_prod);
                    
                    let color_id = color_count % 31;
                    
                    irr_dataset['backgroundColor'] = chart_colors[color_id];
                    irr_dataset['borderColor'] = chart_colors[color_id];
                    irr_dataset['label'] = "Irradiance";
                    irr_dataset['order'] = 1;
                    irr_dataset['data'] = kpi_irradiance;
                    
                    addData(chart_labels, irr_dataset);
                    color_count += 1;
                    
                }
                
                //get Ambient Temperature data if checked
                if(amb_param == 1){
                    
                    let ambient_dataset = {
                        type: 'line',
                        yAxisID: 'D',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 1
                    };
                
                kpi_ambientTemp = [];
                    
                    for(let i = 0; i < data.length; i++){
                        
                        if(parseInt(data[i].meter_id) == 99 && ("ambient_temp" in data[i])){
                            
                            chart_labels.push(data[i].datetime);
                            kpi_ambientTemp.push({"x": data[i].datetime, "y": data[i].ambient_temp});
                            // tbl_custom_irradiance.row.add([data[i].datetime, json.site_name, data[i].ambient_temp]).draw();
                        }
                        
                    }
                    
                    // console.log(kpi_prod);
                    
                    let color_id = color_count % 31;
                    
                    ambient_dataset['backgroundColor'] = chart_colors[color_id];
                    ambient_dataset['borderColor'] = chart_colors[color_id];
                    ambient_dataset['label'] = "Ambient Temperature";
                    ambient_dataset['order'] = 1;
                    ambient_dataset['data'] = kpi_ambientTemp;
                    
                    addData(chart_labels, ambient_dataset);
                    color_count += 1;
                    
                }
                
                
                //get Panel Temperature data if checked
                if(panel_param == 1){
                    
                    let panel_dataset = {
                        type: 'line',
                        yAxisID: 'D',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 1
                    };
                
                kpi_panelTemp = [];
                    
                    for(let i = 0; i < data.length; i++){
                        
                        if(parseInt(data[i].meter_id) == 99 && ("panel_temp" in data[i])){
                            
                            chart_labels.push(data[i].datetime);
                            kpi_panelTemp.push({"x": data[i].datetime, "y": data[i].panel_temp});
                            // tbl_custom_irradiance.row.add([data[i].datetime, json.site_name, data[i].ambient_temp]).draw();
                        }
                        
                    }
                    
                    // console.log(kpi_prod);
                    
                    let color_id = color_count % 31;
                    
                    panel_dataset['backgroundColor'] = chart_colors[color_id];
                    panel_dataset['borderColor'] = chart_colors[color_id];
                    panel_dataset['label'] = "Panel Temperature";
                    panel_dataset['order'] = 1;
                    panel_dataset['data'] = kpi_panelTemp;
                    
                    addData(chart_labels, panel_dataset);
                    color_count += 1;
                    
                }
                
                
                
                
                
               show_zoom(); 
               
                for(let i = 0; i < inputs.length; i++){
                    inputs[i].checked = false;
                }
                
                for(let i = 0; i < params.length; i++){
                    params[i].checked = false;
                }
               document.getElementById('irradiance_param').checked = false;
               document.getElementById('ambientTemp_param').checked = false;
               document.getElementById('panelTemp_param').checked = false;
            }
        });
    
    }
}


function resetZoomBtn(){
    custom_kpi.resetZoom();
}

function addData(label, newData) {
    
    // custom_kpi.data.labels.push(label);
    // custom_kpi.data.datasets.forEach((dataset) => {
    //     dataset.data.push(newData);
    // });
    
    custom_kpi.data.labels = label;
    custom_kpi.data.datasets.push(newData);
    custom_kpi.update();
}

function removeData() {
    custom_kpi.data.labels.pop();
    // custom_kpi.data.datasets.forEach((dataset) => {
    //     dataset.data.pop();
    // });
    custom_kpi.data.datasets.pop();
    custom_kpi.update();
}
 

</script>


<script>

  
</script>   

<script>

function download() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('myChart');
    imageLink.download = 'report.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}

function updateAll(selectall) {
  let selectallcheckbox = document.getElementById('selectallcheckbox');
  let checkboxes = document.querySelectorAll('.datacheckbox');
  if (selectall.checked === false) {
    for (let i = 0; i < checkboxes.length; i++) {
      checkboxes[i].checked = false;
      myChart.hide(i);
    }
  };
  if (selectall.checked === true) {
    for (let i = 0; i < checkboxes.length; i++) {
      checkboxes[i].checked = true;
      myChart.show(i);
    }
  };      
};

function checkboxSelectAllChecker() {
  let selectallcheckbox = document.getElementById('selectallcheckbox');
  let checkboxes = document.querySelectorAll('.datacheckbox');

  let x = 0;
  for (let i = 0;i <= checkboxes.length -1; i++) {
    if (checkboxes[i].checked === true) {
      x++;
    }
  };

  if (x == checkboxes.length) {
    selectallcheckbox.checked = true;
  } else {
    selectallcheckbox.checked = false;
  }
};

function updateChart(dataset) {
  console.log(dataset.value);
  const isDataShown = myChart.isDatasetVisible(dataset.value);
  if (isDataShown === false) {
    myChart.show(dataset.value);        
  };
  if (isDataShown === true) {
    myChart.hide(dataset.value);        
  };   
  checkboxSelectAllChecker();   
};

function filterData() {
    const dates2 = [...dates];
    const startdate = document.getElementById('startdate');
    const enddate = document.getElementById('enddate');

    const indexstartdate = dates2.indexOf(startdate.value);
    const indexenddate = dates2.indexOf(enddate.value);

    const filterDate = dates2.slice(indexstartdate, indexenddate);

    myChart.config.data.labels = filterDate;
    myChart.update();

}

</script>

<script>
function downloadCustom() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('custom_chart');
    imageLink.download = 'custom_chart.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}    
</script>

<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>
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
