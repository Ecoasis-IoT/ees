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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://unpkg.com/leaflet-providers@latest/leaflet-providers.js"></script>
  
<!--Font Awesome-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">        

<!--DataTables-->
<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="assets/css/main.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<style>
 
 #map_container { height: 75vh; width: 100%; overflow:hidden;}
 #sites_map {height:100%; width: 110%; z-index:0;border-radius: 0.35rem;}
 
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
    
    <div id="content">
        <div id="main-content">
            <div class="container-fluid">
                <div class="block-header">
                    <div class="row g-3">
                        <div class="col-lg-5 col-md-8 col-sm-12">                        
                            <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Dashboard</h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ul>
                        </div>            
                    </div>
                </div>           
                
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="card shadow-sm">
                            <div class="header" style="padding-bottom:0px;">
                                <h2 style="display: inline-block;">All Sites</h2> 
                            </div>	
    
                            <div class="body" id="tbl_production">
                                <div class="table-responsive">
        							<table id="tbl_site_prod" class="table table-bordered table-hover js-basic-example">
        							    <thead>
        							        <tr>
        							            <th>Site Name</th>
        							            <th>Production (KWh)</th>
        							            <th>Active Power (kW)</th>
        							        </tr>
        							    </thead>
        							    <tbody>
        							        
        							    </tbody>
        							</table>
                                </div>
                            </div>
                            
                            <div class="header" style="padding-bottom:0px;padding-top:0px;">
                                <h2 style="display: inline-block;">Today's Production <small>All Sites production for today</small> </h2> 
                            </div>	
    
                    		<div class="body" id="chart-con">
        	                    <div class="chartBox" style="height: 400px;">
        	                        <canvas id="myChart"></canvas> 
        	                    </div> 
                            </div>
                            

                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="card shadow-sm" style="height:75vh;">
                            
                            <div id="map_container">
                                <div id="sites_map"></div>
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

<script src="assets/bundles/knob.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/index.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script src="assets/js/widgets/infobox/infobox-1.js"></script>

<script>



//map initialisation

var map = L.map('sites_map').setView([-20.2337508,57.5510122], 10);

L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
    maxZoom: 19,
    zoomSnap: 0.1
}).addTo(map);



    //Sites and barchart

    var chart_labels = [];
    var chart_prod = [];
    var all_sites = [];

    var phoenix = {};
    var home_leisure = {};
    var bovalon = {};
    var helvetia = {};
    var p_catering = {};
    var r_terre = {};
    
    //get_all_site_data
    $(function sites_name(){
    
        $.ajax({
            type: "POST",
            url: "scripts/get_all_sites.php",
            data: {
                
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                console.log(data.data);
                
                var sites = data.data;
                
                for(var i = 0; i < sites.length; i++){
                    
                    if(sites[i][2] == "phoenix_mall.php"){
                        
                        phoenix.id = sites[i][0];
                        phoenix.name = sites[i][1];
                        phoenix.prod = 0;
                        phoenix.power = 0;
                        phoenix.location = sites[i][4];
                        
                    }
                    else if(sites[i][2] == "home_leisure.php"){
                        
                        home_leisure.id = sites[i][0];
                        home_leisure.name = sites[i][1];
                        home_leisure.prod = 0;
                        home_leisure.power = 0;
                        home_leisure.location = sites[i][4];
                        
                    }
                    else if(sites[i][2] == "r_terre_mall.php"){
                        
                        r_terre.id = sites[i][0];
                        r_terre.name = sites[i][1];
                        r_terre.prod = 0;
                        r_terre.power = 0;
                        r_terre.location = sites[i][4];
                    }
                    else if(sites[i][2] == "bovalon_mall.php"){
                        
                        bovalon.id = sites[i][0];
                        bovalon.name = sites[i][1];
                        bovalon.prod = 0;
                        bovalon.power = 0;
                        bovalon.location = sites[i][4];
                    }
                    else if(sites[i][2] == "p_catering.php"){
                        
                        p_catering.id = sites[i][0];
                        p_catering.name = sites[i][1];
                        p_catering.prod = 0;
                        p_catering.power = 0;
                        p_catering.location = sites[i][4];
                        
                    }
                    else if(sites[i][2] == "helvetia.php"){
                        
                        helvetia.id = sites[i][0];
                        helvetia.name = sites[i][1];
                        helvetia.prod = 0;
                        helvetia.power = 0;
                        helvetia.location = sites[i][4];
                    }
                    
                }
                // console.log(JSON.stringify(phoenix));
                    
                site_power();
                
            }
            });
    });
    
    function site_power(){
    
        $.ajax({
            type: "POST",
            url: "scripts/get_site_power.php",
            data: {
                
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                // console.log(data);
                    
                    if(data.ref == "phoenix_mall"){
                        
                        phoenix.prod = data.prod;
                        phoenix.power = data.active_power;
                    
                    }
                    else if(data.ref == "home_leisure"){

                        home_leisure.prod = data.prod;
                        home_leisure.power = data.active_power;
                        
                    }
                    else if(data.ref == "r_terre_mall"){

                        r_terre.prod = data.prod;
                        r_terre.power = data.active_power;
                        
                    }
                    else if(data.ref == "bovalon_mall"){
                        
                        bovalon.prod = data.prod;
                        bovalon.power = data.active_power;
                        
                    }
                    else if(data.ref == "p_catering"){

                        p_catering.prod = data.prod;
                        p_catering.power = data.active_power;
                        
                    }
                    else if(data.ref == "helvetia"){

                        helvetia.prod = data.prod;
                        helvetia.power = data.active_power;
                    }
                    
                // console.log(JSON.stringify(phoenix));
                    
                all_sites.push(phoenix, home_leisure, r_terre, bovalon, p_catering, helvetia);
                // console.log(all_sites);
                
                for (var i = 0; i < all_sites.length; i++){
        
                    var row = "<tr><td><a href='site-dashboard?site="+ all_sites[i].id +"'>"+ all_sites[i].name +"</a></td><td>"+ all_sites[i].prod +"</td><td>"+ all_sites[i].power +"</td></tr>";
                    
                    $('#tbl_site_prod tbody').append(row);
                    
                    chart_labels.push(all_sites[i].name);
                    chart_prod.push(all_sites[i].prod);
                    
                    let coordinates = all_sites[i].location.split(",");
                    
                    let marker = L.marker([coordinates[0],coordinates[1]]).bindTooltip(all_sites[i].name,{permanent: false, direction: 'right',offset:L.point(-14, -5)}).addTo(map);
                    marker.bindPopup("<b>" + all_sites[i].name +"</b><br>Production (kWh): " + all_sites[i].prod);
                }
                
                
                const chart_data = {
                  labels: chart_labels,
                  datasets: [{
                    label: 'Production (KWh)',
                    data: chart_prod,
                    backgroundColor: [
                      '#00bdaa'
                    ]
                  }]
                };
                
                // config 
                const config = {
                  type: 'bar',
                  data: chart_data,
                  options: {
                    scales: {
                      y: {
                        beginAtZero: true
                      }
                    },
                    maintainAspectRatio:false,
                    responsive: true
                  }
                };
                
                // render init block
                const myChart = new Chart(
                  document.getElementById('myChart'),
                  config
                );
                

   
        }
    });
    }
    
    
</script>



</body>
</html>
