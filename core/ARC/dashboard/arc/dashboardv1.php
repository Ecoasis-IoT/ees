<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="../assets/images/logo_icon.png">

<!--Font Awesome-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">        

<!--DataTables-->
<link rel="stylesheet" href="../assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/custom.css">
<link rel="stylesheet" href="../assets/css/main.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

</head>
<body data-theme="theme-cyan">

<!-- Page Loader -->
<?php include_once("../common/page-loader.php") ?>


<!-- Overlay For Sidebars -->

<div id="wrapper">

    <!-- Header -->
    <?php include_once("../common/header.php") ?>

    <!-- Left sidebar-->
    <?php include_once("../common/sidebar.php") ?>
    
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
    
                <div class="row">
                    <div class="col-lg-12">
                        <i class="fa fa-chevron-circle-right" style="font-size: 20px;" id="flip2" onclick="myFunction2();iconFunction2(this);toggleClass();"></i>
                    </div>
                </div>
                
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 panel2" id="alarms-div">
                        <div class="card info-box-2">
                            <div class="body">
                            	<div class="row">
                            		<div class="col-lg-6 col-md-6 col-sm-6 col-6 chart-col">
    				                    <div class="doughBox">
    				                        <canvas id="doughChart"></canvas> 
    				                    </div>
                            		</div>
    
                            		<div class="col-lg-6 col-md-6 col-sm-6 col-6 alerts-col">
                            			<div class="alerts-contents">
    		                                <h3>Alerts</h3>
    		                                <div class="number">17</div>
                                    	</div>
                            		</div>
                            	</div>
                            </div>
                        </div>
                    </div>               
                    <div class="col-lg-6" id="id1">
                        <div class="card info-box-2">
                            <div class="body">
                            	<div class="row">
                            		<div class="col-lg-6 col-md-6 col-sm-6 col-6 chart-col">
    				                    <div class="chartBox">
    				                        <canvas id="barChart"></canvas> 
    				                    </div>
                            		</div>
                            		<div class="col-lg-6 col-md-6 col-sm-6 col-6 alerts-col">
                            			<div class="alerts-contents" style="width: 100%;">
    		                                <h3>Production Last 7 Days</h3>
    		                                <div class="number">(KWh)</div>
                                    	</div>
                            		</div>
                            	</div>
                            </div>
                        </div>
                    </div>        	
    
                </div>
    
    
                <div class="row clearfix g-3 mb-3">
                	<div class="col-lg-12">
    					</span><i style="font-size:20px;float: right;" class="fa fa-fw fa-minus-circle" id="flip" onclick="myFunction();iconFunction(this);"></i>
                	</div>
    
                </div>
    
                <div class="row clearfix g-3 mb-3" id="panel">
                    <div class="col-lg-12 col-md-12 col-sm-12">                            																		
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>Active Alarms <small>Plant alarms for today</small> </h2>
                            </div>
    
                            <div class="row">
                                <div class="col-lg-4 col-sm-12 col-xs-12" style="padding-right: 0px;">
                                    <div class="alert-con">
                                        <div class="body">
                							<p class="control">Sort By Alarm Impact:</p> 
    
                                            <button class="dt-button btn-primary active" onclick="filterSelection('all')">All</button>
                                            <button class="dt-button btn-primary" onclick="filterSelection('alert-critical')">Critical</button>
                                            <button class="dt-button btn-primary" onclick="filterSelection('alert-medium')">Medium</button>
                                            <button class="dt-button btn-primary" onclick="filterSelection('alert-low')">Low</button>
                                            <button class="dt-button btn-primary" onclick="filterSelection('alert-notcritical')">Not Critical</button> 
    
                                            <div class="body alert-body" style="padding-left: 0px;">                       
                                                <div class="alert alert-critical">
                                                    <i class="fa fa-info-circle"></i> Energy production issue detected
                                                </div>
                                                <div class="alert alert-medium">
                                                    <i class="fa fa-times-circle"></i> Sites not communicating
                                                </div>
                                                <div class="alert alert-low">
                                                    <i class="fa fa-warning"></i> Grid Voltage Disruption
                                                </div>
                                                <div class="alert alert-notcritical">
                                                    <i class="fa fa-times-circle"></i> No communication with Power Optimizer
                                                </div>
                                                <div class="alert alert-medium">
                                                    <i class="fa fa-times-circle"></i> Sites not communicating
                                                </div> 
                                                <div class="alert alert-low">
                                                    <i class="fa fa-warning"></i> Grid Voltage Disruption
                                                </div>     
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="col-lg-8 col-sm-12 col-xs-12">
            						<div class="table-responsive tbl_alerts">
                                        <table id="alerts" class="table table-bordered table-hover dataTable">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Site Name</th>
                                                    <th>Alarm Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Mall of Mauritius @ Bagatelle</a></td>
                                                    <td>No communication with Power Optimizer</td>                                           
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Phoenix Mall</a></td>
                                                    <td>Grid Voltage Disruption</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Trou aux Biches Hotel</a></td>
                                                    <td>Sites not communicating</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Paradis Golf Resort & Spa</a></td>
                                                    <td>No communication with Power Optimizer</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Le Mauricia Hotel</a></td>
                                                    <td>Energy production issue detected</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Deco City Bagatelle</a></td>
                                                    <td>Grid Voltage Disruption</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Kendra Commercial Centre</a></td>
                                                    <td>Sites not communicating</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Victoria Hotel</a></td>
                                                    <td>No communication with Power Optimizer</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Shandrani Resort & Spa</a></td>
                                                    <td>Energy production issue detected</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - ENL HOUSE</a></td>
                                                    <td>Sites not communicating</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - LES ALLES D'HELVETIA</a></td>
                                                    <td>Grid Voltage Disruption</td>
                                                </tr>
                                                <tr>
                                                    <td>02/02/2024</td>
                                                    <td><a href="#">Ecoasis - Old Factory (1827)</a></td>
                                                    <td>No communication with Power Optimizer</td>
                                                </tr>                                                                                                                 
                                            </tbody>
                                        </table>
                                	</div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                </div>
    
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2 style="display: inline-block;">Today's Production <small>Plant production for today</small> </h2> 
    							<div class="tab" style="float:right;">							  
    							  <button class="tablinks" onclick="openTab(event, 'chart-con');">Chart</button>
    							  <button class="tablinks" onclick="openTab(event, 'tbl_production')">Table</button>
    							</div>                                                       
                            </div>	
    
                		<div class="body tabcontent" id="chart-con" style="height: 100%;">
    	                    <div class="chartBox" style="height: 499px;">
    	                        <canvas id="myChart"></canvas> 
    	                    </div> 
                        </div>
    
                            <div class="body tabcontent" id="tbl_production" style="display: none;">
                                <div class="table-responsive">
        							<table id="production" class="table table-bordered table-hover js-basic-example">
        							    <thead>
        							        <tr>
        							            <th>Date/Time</th>
        							            <th>Site Name</th>
        							            <th>Production (KWh)</th>
        							        </tr>
        							    </thead>
        							    <tbody>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Mall of Mauritius @ Bagatelle</a></td>
        							            <td>2000</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="plant.html">Ecoasis - Phoenix Mall</a></td>
        							            <td>730</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Trou aux Biches Hotel</a></td>
        							            <td>430</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Paradis Golf Resort & Spa</a></td>
        							            <td>213</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Le Mauricia Hotel</a></td>
        							            <td>204</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Deco City Bagatelle</a></td>
        							            <td>200</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Kendra Commercial Centre</a></td>
        							            <td>190</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Victoria Hotel</a></td>
        							            <td>180</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Shandrani Resort & Spa</a></td>
        							            <td>165</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - ENL HOUSE</a></td>
        							            <td>66</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - LES ALLES D'HELVETIA</a></td>
        							            <td>49</td>
        							        </tr>
        							        <tr>
        							            <td>02/02/2024</td>
        							            <td><a href="#">Ecoasis - Old Factory (1827)</a></td>
        							            <td>49</td>
        							        </tr>                                                                                                                 
        							    </tbody>
        							</table>
                                </div>
                            </div>                    
                        </div>
                    </div> 
                </div>
            </div>
            
            <!-- Footer -->
            <?php include_once("../common/footer.php") ?>
            
        </div>
    </div> 
</div>

<script>


function iconFunction(x) {
  x.classList.toggle("fa-plus-circle");
  x.classList.toggle("fa-minus-circle");
}

function iconFunction2(x) {
  x.classList.toggle("fa-chevron-circle-right");
  x.classList.toggle("fa-chevron-circle-left");
}

</script>

<script>



function myFunction2() {

  var x = document.getElementById("alarms-div");

  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

</script>

<script>

function toggleClass() {
    let element = document.getElementById('id1');    

    // toggle class
    element.classList.toggle('col-lg-12');        

}
</script>

<script> 
$(document).ready(function(){
  $("#flip").click(function(){
    $("#panel").slideToggle("slow");
  });
});

// $(document).ready(function(){
//   $("#flip2").click(function(){
//     $("#panel2").slideToggle("slow");
//   });
// });
</script>


<script>
// setup 

const dataBar = {
  labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
  datasets: [{
    label: 'Production (KWh)',
    data: [2000, 2740, 2430, 2213, 2204, 2200, 2190, 2180, 2165, 2266, 2249, 2249],
    backgroundColor: [
      '#00bdaa'
    ]
  }]
};

// config 
const configBar = {
  type: 'bar',
  data: dataBar,
  options: {
	scales: {
	  x: {
	    grid: {
	      display: false
	    }
	  },
	  y: {
	    grid: {
	      display: false
	    }
	  }
	},
    maintainAspectRatio:false,
    responsive: true
  }
};

// render init block
const barChart = new Chart(
  document.getElementById('barChart'),
  configBar
);

</script>

<script>
// setup 

const data = {
  labels: ['Mall of Mauritius @ Bagatelle', 'Phoenix Mall', 'Trou aux Biches Hotel', 'Paradis Golf Resort & Spa', 'Le Mauricia Hotel', 'Deco City Bagatelle', 'Kendra Commercial Centre', 'Victoria Hotel', 'Shandrani Resort & Spa', 'ENL HOUSE', "LES ALLES D'HELVETIA", 'Old Factory (1827)'],
  datasets: [{
    label: 'Production (KWh)',
    data: [2000, 740, 430, 213, 204, 200, 190, 180, 165, 66, 49, 49],
    backgroundColor: [
      '#00bdaa'
    ]
  }]
};

// config 
const config = {
  type: 'bar',
  data,
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

</script>

<script>
// setup 
const dataDoughnut = {
  labels: ['Critical', 'Medium', 'Low', 'Not Critical'],
  datasets: [{
    label: 'Alerts',
    data: [4, 6, 5, 2],
    backgroundColor: ['#7b3131', '#CC0000', '#FF6600', '#FFCC00']
  }]
};

// config 
const configDough = {
  type: 'doughnut',
  data: dataDoughnut,
  options: {
  	maintainAspectRatio:false,
    plugins: {
		legend: {
			position: "right"	      
		},
		datalabels: {
			color: 'white'
		}	      
    },
  },
  plugins: [ChartDataLabels],
};

// render init block
const doughChart = new Chart(
  document.getElementById('doughChart'),
  configDough
);

</script>

<script >
// To switch tab between main page and splash screen
function openTab(evt, tabName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active"
}	
</script>

<script>
filterSelection("all")
function filterSelection(c) {
  var x, i;
  x = document.getElementsByClassName("alert");
  if (c == "all") c = "";
  for (i = 0; i < x.length; i++) {
    w3RemoveClass(x[i], "show");
    if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], "show");
  }
}

function w3AddClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    if (arr1.indexOf(arr2[i]) == -1) {element.className += " " + arr2[i];}
  }
}

function w3RemoveClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    while (arr1.indexOf(arr2[i]) > -1) {
      arr1.splice(arr1.indexOf(arr2[i]), 1);     
    }
  }
  element.className = arr1.join(" ");
}

</script>

<!-- Javascript -->
<script src="../assets/bundles/libscripts.bundle.js"></script>    
<script src="../assets/bundles/vendorscripts.bundle.js"></script>

<script src="../assets/bundles/knob.bundle.js"></script>

<script src="../assets/bundles/mainscripts.bundle.js"></script>
<script src="../assets/js/index.js"></script>

<script src="../assets/js/pages/tables/jquery-datatable.js"></script>
<script src="../assets/bundles/datatablescripts.bundle.js"></script>
<script src="../assets/js/widgets/infobox/infobox-1.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

</body>
</html>
