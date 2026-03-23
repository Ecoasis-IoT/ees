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

<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!--CUSTOM CSS-->
<link rel="stylesheet" href="assets/css/custom.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

<!--METER-REPORT CSS-->
<link rel="stylesheet" href="assets/css/meter-report.css">


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> 

<style>

.submit {
    margin-bottom:15px;    
}

/*input, select {*/
/*    padding:5px;*/
/*}*/

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
                    <div class="card">
                        <div class="body">
                            <ul class="nav nav-tabs-new2">
                                <li class="nav-item"><a class="nav-link active show" data-bs-toggle="tab" href="#Day">Day</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Month">Month</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Year">Year</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Custom">Custom Query</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane show active" id="Day">
                                    <div class="row mb-3">
                                        <div class="col-lg-2 col-12 col-sm-3">
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                            </div>           
        
                                            <select name="sites_opt" id="sites_opt" class="alarms-sort" onchange="filter_meters()">
                                                <option hidden>Choose a site</option>
                                            </select>    
                                        </div>
                                            
                                        <div class="col-lg-3 col-12 col-sm-5" style="margin-right:-64px;">    
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE ENERGY METER</h2> 
                                            </div>           
        
                                            <select name="meters_opt" id="meters_opt" class="alarms-sort">
                                                <option hidden>Choose an energy meter</option>
                                                
                                            </select>
                                        </div>
                                        
                                        <div class="col-lg-2 col-6 col-sm-3">    
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE DATE</h2> 
                                            </div>           
        
                                            <input type="date" id="date" style="padding:6px;">

                                        </div>
                                        
                                        
                                        <div class="col-lg-2 col-6 col-sm-2">    
                                            <div class="header">
                                                <h2 style="display:inline-block;color:transparent;"></h2> 
                                            </div>           
        
                                            <button class="btn btn-primary submit" onclick="">Submit</button>         

                                        </div>                                        
                                          
                                    </div>

                                    <div class="row text-center mb-3" style="justify-content:center;">
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>5,395.72<span> kWh</span></h3>
                                                        <span class="text-muted value-text">Total Production</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>3.75<span> kWh/m<sup>2</sup></span></h3>
                                                        <span class="text-muted value-text">Insolation</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>    
                                        
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>3.75<span> %</span></h3>
                                                        <span class="text-muted value-text">Performance Ratio</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 

                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>3.75<span> kg</h3>
                                                        <span class="text-muted value-text">CO<sub>2</sub> Avoided</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                         
                                    </div>
                                    
                                    <div class="row clearfix g-3 mb-3">
                                        <div class="col-lg-12">
                                            <div class="header">
                                                <h2 style="display: inline-block;">Plant KPI<small>Production | Irradiance | Power</small> </h2> 
                                                <i class="fa fa-download" style="font-size: 18px; float:right;cursor:pointer;" onclick="downloadKPI()"  title="Download"></i>
                                            </div>
                        
                                            <div class="body chartKPI">                        
                                                <canvas id="kpi"></canvas>                        
                                            </div>                    
                                        </div>                     
                                    </div>
                                    
                                    
                                    <div class="table-responsive">
                                        <table id="hourly" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Device</th>
                                                    <th>Site</th>
                                                    <th>Production (kWh)</th>
                                                </tr>
                                            </thead>
                                            <tbody> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Main Meter</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                </tr>  
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 1</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td> 
                                                </tr>    
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 2</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 3</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td> 
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 4</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 5</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td> 
                                                </tr>
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 6</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td> 
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 7</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td> 
                                                </tr>
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 8</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>  
                                                </tr>  
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 9</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>  
                                                </tr>                                                                                                                          
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="row clearfix g-3 mb-3">
                                        <div class="col-lg-12">
                                            <div class="chartBox">
                                                <canvas id="hourlyChart"></canvas> 
                                            </div>
                                        </div>
                                    </div>                                     
                                </div>

                                <div class="tab-pane" id="Month">
                                    <div class="row mb-3" style="position:relative;">
                                        <div class="col-lg-2 col-md-3">
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose a site</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">Main Meter</option>
                                                <option value="2">Phoenix Mall</option>
                                                <option value="3">Home and Leisure</option>
                                                <option value="4">Riche Terre Mall</option>
                                                <option value="5">BoValon Mall</option>
                                                <option value="6">Plaisance Catering</option>
                                                <option value="7">Helvetia</option>
                                            </select>    
                                        </div>
                                            
                                        <div class="col-lg-3 col-md-5" style="margin-right:-64px;">    
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE ENERGY METER</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose an energy meter</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">Main Meter</option>
                                                <option value="2">Energy Meter 1</option>
                                                <option value="3">Energy Meter 2</option>
                                                <option value="4">Energy Meter 3</option>
                                                <option value="5">Energy Meter 4</option>
                                                <option value="6">Energy Meter 5</option>
                                                <option value="7">Energy Meter 6</option>
                                                <option value="8">Energy Meter 7</option>
                                                <option value="9">Energy Meter 8</option>
                                                <option value="10">Energy Meter 9</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-lg-2 col-md-3">    
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE MONTH</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose a month</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">January 2024</option>
                                                <option value="2">February 2024</option>
                                            </select>
                                        </div>  
                                        
                                        <div class="col-lg-2 col-md-3">  
                                            <button class="btn btn-primary submit" onclick="">Submit</button>         
                                        </div>                                          
                                    </div>


                                    <div class="row text-center" style="justify-content:center;">
                                        <div class="col-lg-3 col-md-4">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>101,395.72<span> kWh</span></h3>
                                                        <span class="text-muted value-text">Total Production</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-md-4">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>141.75<span> kWh/m<sup>2</sup></span></h3>
                                                        <span class="text-muted value-text">Insolation</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <div class="table-responsive">
                                        <table id="daily" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Device</th>
                                                    <th>Site</th>
                                                    <th>Active Power (kW)</th>
                                                    <th>Active Energy (kWh)</th>
                                                    <th>Power Factor</th>
                                                </tr>
                                            </thead>
                                            <tbody> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Main Meter</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>
                                                </tr>  
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 1</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr>    
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 2</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td> 
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 3</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 4</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 5</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr>
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 6</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr> 
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 7</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr>
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 8</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr>  
                                                <tr>
                                                    <td>07 Mar 2024 15:23</td>
                                                    <td>Energy Meter 9</td>
                                                    <td>Mall of Mauritius @ Bagatelle</td>
                                                    <td>10982</td>
                                                    <td>159.232</td>
                                                    <td>0.912321</td>  
                                                </tr>                                                                                                                          
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row clearfix g-3 mb-3">
                                        <div class="col-lg-12">
                                            <div class="chartBox">
                                                <canvas id="dailyChart"></canvas> 
                                            </div>
                                        </div>
                                    </div>                                     
                                </div>
                                
                                <div class="tab-pane" id="Year">
                                    <div class="row mb-3" style="position:relative;">
                                        <div class="col-lg-2 col-md-3">
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose a site</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">Main Meter</option>
                                                <option value="2">Phoenix Mall</option>
                                                <option value="3">Home and Leisure</option>
                                                <option value="4">Riche Terre Mall</option>
                                                <option value="5">BoValon Mall</option>
                                                <option value="6">Plaisance Catering</option>
                                                <option value="7">Helvetia</option>
                                            </select>    
                                        </div>
                                            
                                        <div class="col-lg-3 col-md-5" style="margin-right:-64px;">    
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE ENERGY METER</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose an energy meter</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">Main Meter</option>
                                                <option value="2">Energy Meter 1</option>
                                                <option value="3">Energy Meter 2</option>
                                                <option value="4">Energy Meter 3</option>
                                                <option value="5">Energy Meter 4</option>
                                                <option value="6">Energy Meter 5</option>
                                                <option value="7">Energy Meter 6</option>
                                                <option value="8">Energy Meter 7</option>
                                                <option value="9">Energy Meter 8</option>
                                                <option value="10">Energy Meter 9</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-lg-2 col-md-3">    
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE YEAR</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose a year</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">2024</option>
                                                <option value="2">2025</option>
                                            </select>
                                        </div> 
                                        
                                        <div class="col-lg-2 col-md-12">  
                                            <button class="btn btn-primary submit" onclick="">Submit</button>         
                                        </div>                                          
                                    </div>


                                    <div class="row text-center" style="justify-content:center;">
                                        <div class="col-lg-3 col-md-4">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>175,395.72<span> kWh</span></h3>
                                                        <span class="text-muted value-text">Total Production</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-md-4">
                                            <div class="card text-center mb-3" style="height:101px;width:100%;border:1px solid black;">
                                                <div class="body">
                                                    <div class="">
                                                        <h3>1724.23<span> kWh/m<sup>2</sup></span></h3>
                                                        <span class="text-muted value-text">Insolation</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                        
                                    </div>                                  
                                    <div class="table-responsive">
                                        <table id="monthly" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>Date/Time</th>
                                                <th>Device</th>
                                                <th>Site</th>
                                                <th>Active Power (kW)</th>
                                                <th>Active Energy (kWh)</th>
                                                <th>Power Factor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Main Meter</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>
                                            </tr>  
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 1</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr>    
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 2</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td> 
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 3</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 4</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 5</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr>
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 6</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 7</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr>
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 8</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr>  
                                            <tr>
                                                <td>07 Mar 2024 15:23</td>
                                                <td>Energy Meter 9</td>
                                                <td>Mall of Mauritius @ Bagatelle</td>
                                                <td>10982</td>
                                                <td>159.232</td>
                                                <td>0.912321</td>  
                                            </tr>                                                                                                                          
                                        </tbody>
                                    </table>
                                    </div>   
                                    <div class="row clearfix g-3 mb-3">
                                        <div class="col-lg-12">
                                            <div class="chartBox">
                                                <canvas id="monthlyChart"></canvas> 
                                            </div>
                                        </div>
                                    </div>                                   
                                </div>
                                
                                <div class="tab-pane" id="Custom">
                                    
                                    <div class="row mb-3">
                                        <div class="col-lg-2 col-md-3">
                                            <div class="header">
                                                <h2 style="display:inline-block;">CHOOSE SITE</h2> 
                                            </div>           
        
                                            <select name="sort" id="sort" class="alarms-sort">
                                                <option hidden><span class="form-control"><small>Choose a site</small></span></option>
                                                <option value="0">All</option>
                                                <option value="1">Main Meter</option>
                                                <option value="2">Phoenix Mall</option>
                                                <option value="3">Home and Leisure</option>
                                                <option value="4">Riche Terre Mall</option>
                                                <option value="5">BoValon Mall</option>
                                                <option value="6">Plaisance Catering</option>
                                                <option value="7">Helvetia</option>
                                            </select>    
                                        </div>
                                            
                                        <div class="col-lg-3 col-md-5" style="margin-right:-120px;">   
                                            <div class="header">
                                                <h2 style="display:inline-block;">START DATE</h2> 
                                            </div>
                                            <input type="date" id="date" style="padding:6px;">                                        
                                        </div>
                                        
                                        <div class="col-lg-3 col-md-5">   
                                            <div class="header">
                                                <h2 style="display:inline-block;">END DATE</h2> 
                                            </div>
                                            <input type="date" id="date" style="padding:6px;">                                        
                                        </div>                                        
                                    </div>
                                    
                                    <div class="row clearfix g-3 mb-3">
                                        <div class="col-lg-3 col-md-3" style="margin-top:26px;">
                                            <div class="card text-center" style="height: 256px;">
                                                <div class="header" style="background: #0d6efd;border-radius: 0.25rem 0.25rem 0px 0px;">
                                                    <h2 style="color:white;">Elements</h2>
                                                </div>
                                                <div class="main-con" style="margin: 5px;border: 1px solid #ddd;">
                                                    <ul>
                                                        <li><input id="" type="checkbox"><strong>Main Meter</strong><br></li>
                                                        <i class="fa fa-fw fa-plus-circle" style="float: left;font-size: 18px;color:gray;padding-top:2px;" onclick="myFunction2();iconFunction2(this)"></i><li><input id="" type="checkbox"><strong>Sub Meters</strong><br>
                                                            <ul id="sub-div" style="display: block;">
                                                                <li><input id="" type="checkbox">Sub Meter 1<br></li>
                                                                <li><input id="" type="checkbox">Sub Meter 2<br></li>
                                                                <li><input id="" type="checkbox">Sub Meter 3<br></li>
                                                                <li><input id="" type="checkbox">Sub Meter 4<br></li>
                                                                <li><input id="" type="checkbox">Sub Meter 5<br></li>                                     
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>                                               
                                            </div>
                                            
                                            <div class="card text-center mb-3" style="height: 136px;margin-top:1rem;">
                                                <div class="header" style="background: #0d6efd;border-radius: 0.25rem 0.25rem 0px 0px;">
                                                    <h2 style="color:white;">Parameters</h2>
                                                </div>
                                                <div class="main-con" style="margin: 5px;border: 1px solid #ddd;">
                                                    <div class="checkbox-container">
                                                    <div class="input-container">
                                                        <input id="selectallcheckbox" type="checkbox" onclick="updateAll(this)" checked =""><strong>SELECT ALL</strong><br>
                                                        <input class="datacheckbox" type="checkbox" onclick="updateChart(this)" checked ="" value="0">PRODUCTION<br>
                                                        <input class="datacheckbox" type="checkbox" onclick="updateChart(this)" checked ="" value="1">ACTIVE POWER<br>
                                                    </div>
                                                </div> 
                                                </div>
                                            </div> 
                                            
                                            <button class="btn btn-outline-secondary" style="width:100%;"> Submit</button>
                                        </div>                                        
                                        <div class="col-lg-9 col-md-9">  
                                            <div class="chartBox">
                                                <canvas id="myChart"></canvas> 
                                            </div>                                        
                                        </div>
                                    </div>
                                    
                                    <div class="row clearfix g-3 mb-3">
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table id="custom-query" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export" style="width:100%;">
                                                <thead>
                                                    <tr>
                                                        <th>Date/Time</th>
                                                        <th>Device</th>
                                                        <th>Site</th>
                                                        <th>Active Power (kW)</th>
                                                        <th>Active Energy (kWh)</th>
                                                        <th>Power Factor</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Main Meter</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>
                                                    </tr>  
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 1</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr>    
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 2</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td> 
                                                    </tr> 
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 3</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr> 
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 4</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>
                                                    </tr> 
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 5</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr>
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 6</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr> 
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 7</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr>
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 8</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr>  
                                                    <tr>
                                                        <td>07 Mar 2024 15:23</td>
                                                        <td>Energy Meter 9</td>
                                                        <td>Mall of Mauritius @ Bagatelle</td>
                                                        <td>10982</td>
                                                        <td>159.232</td>
                                                        <td>0.912321</td>  
                                                    </tr>                                                                                                                          
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
            <!-- Footer -->
            <?php include_once("common/footer.php") ?>            
        </div>
    </div>
    
</div>

<!-- ================================================================================================================================= -->

<script>

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
                
                var sites = data.data;
                
                var select = document.getElementById("sites_opt");
                
                for(var i = 0; i < sites.length; i++){
                    
                    var option = document.createElement("option");
                    option.text = sites[i][1];
                    option.value = sites[i][0];
                    
                    select.appendChild(option);
                
                }
            }
        });
});


function filter_meters(){
    
    $("#meters_opt").empty();
    let default_opt = "<option hidden>Choose Meter</option>";
    $("#meters_opt").append(default_opt);
    
    
    // let meters = document.getElementById("meters_opt");
    
    $.ajax({
            type: "POST",
            url: "scripts/get_query_meters.php",
            data: {
                "site": document.getElementById("sites_opt").value
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
                    
                    
                    for (const key in data) {
                        let rec = data[key];
                        
                        $("#meters_opt").append($('<option>', {value: rec.meter_id, text: rec.name}));
                        
                    }
                    
                    
                    
                    // meters available
                }
            
                
                
            },
            error: function(req, err){ console.log('error' + err)}
        });
}


</script>

<!-- ================================================================================================================================= -->


<!--Start Date and End Date validation-->
<script>
$("#startdate").on("change", function(){
  $("#enddate").attr("min", $(this).val());
}); 

$("#enddate").on("change", function(){
  $("#startdate").attr("max", $(this).val());
});
</script>

<!--Hourly Report-->
<script>
const hourlyDates = ['01/04/2024 09:00','02/04/2024 09:00','03/04/2024 09:00','03/04/2024 09:00','05/04/2024 09:00','06/04/2024 09:00','07/04/2024 09:00']
const hourlypowerDataset = [230123, 242122, 226421, 231912, 311232, 123123, 229131]
const hourlyactiveDataset = [9, 15, 12, 6, 15, 9, 6]
const hourlyfactorDataset = [11, 5, 9, 2, 12, 4, 1]

const hourlyPlugin = {
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

const dataHourly = {
  labels: hourlyDates,
  datasets: [{
    label: 'ACTIVE POWER',
    data: hourlypowerDataset,
    backgroundColor: [
      'rgba(255, 26, 104, 0.2)'
    ],
    borderColor: [
      'rgba(255, 26, 104, 1)'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: 'ACTIVE ENERGY',
    data: hourlyactiveDataset,
    backgroundColor: [
      'rgba(54, 162, 235, 0.2)'
    ],
    borderColor: [
      'rgba(54, 162, 235, 1)'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: 'POWER FACTOR',
    data: hourlyfactorDataset,
    backgroundColor: [
      'rgba(48, 26, 104, 0.2)'
    ],
    borderColor: [
      'rgba(48, 26, 104, 1)'
    ],
    tension: 0.5,
    pointStyle: 'line'
  }
]
};

// config 
const hourlyConfig = {
  type: 'line',
  data: dataHourly,
  options: {
    plugins: {
      legend: {
        position: 'top',
        labels: {
            usePointStyle: true
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        title: {
            display: true,
            text: 'Consumption',
            font: {
                size: 17,
                weight: 'bold'
            }
        },        
      },
      x: {
        title: {
            display: true,
            text: 'Date/Time',
            font: {
                size: 17,
                weight: 'bold'
            }
        }          
          
      }
    },
    responsive: true,    
    interaction: {
        mode: 'index'
    },  
  },
  plugins: [hourlyPlugin],
};

// render init block
const hourlyChart = new Chart(
  document.getElementById('hourlyChart'),
  hourlyConfig
);

</script>

<!--Daily Report-->
<script>
const dailyDates = ['01/04/2024 09:00','02/04/2024 09:00','03/04/2024 09:00','03/04/2024 09:00','05/04/2024 09:00','06/04/2024 09:00','07/04/2024 09:00']
const powerDataset = [18, 12, 6, 9, 12, 3, 9]
const activeDataset = [9, 15, 12, 6, 15, 9, 6]
const factorDataset = [11, 5, 9, 2, 12, 4, 1]

const dailyPlugin = {
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

const dataDaily = {
  labels: dailyDates,
  datasets: [{
    label: 'ACTIVE POWER',
    data: powerDataset,
    backgroundColor: [
      '#F2BB46'
    ],
    borderColor: [
      '#F2BB46'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: 'ACTIVE ENERGY',
    data: activeDataset,
    backgroundColor: [
      '#CA5952'
    ],
    borderColor: [
      '#CA5952'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: 'POWER FACTOR',
    data: factorDataset,
    backgroundColor: [
      '#7CAF57'
    ],
    borderColor: [
      '#CA5952'
    ],    
    tension: 0.5,
    pointStyle: 'line'
  }
]
};

// config 
const dailyConfig = {
  type: 'line',
  data: dataDaily,
  options: {
    plugins: {
      legend: {
        position: 'top',
        labels: {
            usePointStyle: true
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        title: {
            display: true,
            text: 'Consumption',
            font: {
                size: 17,
                weight: 'bold'
            }
        },        
      },
      x: {
        title: {
            display: true,
            text: 'Date/Time',
            font: {
                size: 17,
                weight: 'bold'
            }
        }          
          
      }
    },
    responsive: true,    
    interaction: {
        mode: 'index'
    },  
  },
  plugins: [dailyPlugin],
};

// render init block
const dailyChart = new Chart(
  document.getElementById('dailyChart'),
  dailyConfig
);

</script>

<!--Monthly Report-->
<script>
const monthlyDates = ['01/04/2024 09:00','02/04/2024 09:00','03/04/2024 09:00','03/04/2024 09:00','05/04/2024 09:00','06/04/2024 09:00','07/04/2024 09:00']
const monthlypowerDataset = [18, 12, 6, 9, 12, 3, 9]
const monthlyactiveDataset = [9, 15, 12, 6, 15, 9, 6]
const monthlyfactorDataset = [11, 5, 9, 2, 12, 4, 1]

const monthlyPlugin = {
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

const dataMonthly = {
  labels: monthlyDates,
  datasets: [{
    label: 'ACTIVE POWER',
    data: monthlypowerDataset,
    backgroundColor: [
      'rgba(255, 26, 104, 0.2)'
    ],
    borderColor: [
      'rgba(255, 26, 104, 1)'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: 'ACTIVE ENERGY',
    data: monthlyactiveDataset,
    backgroundColor: [
      'rgba(54, 162, 235, 0.2)'
    ],
    borderColor: [
      'rgba(54, 162, 235, 1)'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: 'POWER FACTOR',
    data: monthlyfactorDataset,
    backgroundColor: [
      'rgba(48, 26, 104, 0.2)'
    ],
    borderColor: [
      'rgba(48, 26, 104, 1)'
    ],
    tension: 0.5,
    pointStyle: 'line'
  }
]
};

// config 
const monthlyConfig = {
  type: 'line',
  data: dataMonthly,
  options: {
    plugins: {
      legend: {
        position: 'top',
        labels: {
            usePointStyle: true
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        title: {
            display: true,
            text: 'Consumption',
            font: {
                size: 17,
                weight: 'bold'
            }
        },        
      },
      x: {
        title: {
            display: true,
            text: 'Date/Time',
            font: {
                size: 17,
                weight: 'bold'
            }
        }          
          
      }
    },
    responsive: true,    
    interaction: {
        mode: 'index'
    },  
  },
  plugins: [monthlyPlugin],
};

// render init block
const monthlyChart = new Chart(
  document.getElementById('monthlyChart'),
  monthlyConfig
);


</script>

<script>

function iconFunction(x) {
  x.classList.toggle("fa-minus-circle");
}

function iconFunction2(x) {
  x.classList.toggle("fa-minus-circle");
}

</script>

<script>

function myFunction() {

  var x = document.getElementById("main-div");

  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

function myFunction2() {  
  var y = document.getElementById("sub-div");
  if (y.style.display === "none") {
    y.style.display = "block";
  } else {
    y.style.display = "none";
  }

}

</script>

<script>
// setup 

const dates = ['2024-05-01','2024-05-02','2024-05-03','2024-05-04','2024-05-05','2024-05-06','2024-05-07','2024-05-08','2024-05-09','2024-05-10']
const activeEnergy = [381212, 221212, 423136, 412319, 321312, 323001, 320231, 309123,201232,523013]
const activePower = [312131, 315131, 311312, 231236, 411245, 492912, 463241, 434234, 513123, 312312]
const powerFactor = [93, 89, 78, 72, 97, 74, 81, 77, 92, 73]
const energylabel = 'METER ' + '1' + ' -' + ' ACTIVE ENERGY (kWh)' 
const powerlabel = 'METER ' + '1' + ' -' + ' ACTIVE POWER (kW)'
const factorlabel = 'METER ' + '1' + ' -' + ' POWER FACTOR (%)'

const data = {
  labels: dates,
  datasets: [{
    label: energylabel,
    data: activeEnergy,
    backgroundColor: [
      '#F2BB46'
    ],
    borderColor: [
      '#F2BB46'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: powerlabel,
    data: activePower,
    backgroundColor: [
      '#CA5952'
    ],
    borderColor: [
      '#CA5952'
    ],
    tension: 0.5,
    pointStyle: 'line'
  },
  {
    label: factorlabel,
    data: powerFactor,
    backgroundColor: [
      '#7CAF57'
    ],
    borderColor: [
      '#7CAF57'
    ],
    tension: 0.5,
    pointStyle: 'line'
  }]
};

// config 
const config = {
  type: 'line',
  data,
  options: {
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
            usePointStyle: true
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
};

// render init block
const myChart = new Chart(
  document.getElementById('myChart'),
  config
);

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
</script>

<script>

// console.log(barchart_data);

const dataKPI = {
//   labels: barchart_labels,
  datasets: [
  {
    type: 'line',
    label: 'Active Power (kW)',
    yAxisID: 'A',
    data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
    // data: kpi_active_power,
    fill: false,
    backgroundColor: '#36648B',
    borderColor: '#36648B',
    tension: 0.4,
    pointRadius: 4,
    pointBorderColor: 'transparent',
    pointBackgroundColor: 'transparent'
  },
    {
    type: 'line',
    label: 'Irradiance (W/m2)',
    yAxisID: 'B',
    // data: kpi_irradiance,
    data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
    fill: false,
    backgroundColor: '#F2BB46',
    borderColor: '#F2BB46',
    title: 'Irradiance (W/2)',
    tension: 0.4,
    pointRadius: 4,
    pointBorderColor: 'transparent',
    pointBackgroundColor: 'transparent'
  },      
  {
    type: 'bar',
    label: 'Production (kWh)',
    yAxisID: 'C',
    // data: kpi_prod,
    data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
    backgroundColor: '#CA5952',
    borderColor: '#CA5952',
    barThickness:30,
  }
  ]
};

const configKPI = {
  data: dataKPI,
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
        tooltip: {
            enabled: true,
            multiKeyBackground: ["#F2BB46"]
        }
    },
    maintainAspectRatio:false,
    responsive: true,
    // interaction: {
    //     mode: 'index'
    // },
  },
  plugins: [white_back],
};

// render init block
const kpi = new Chart(
  document.getElementById('kpi'),
  configKPI
);

</script>

<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/pages/tables/jquery-datatable.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
</body>
</html>
