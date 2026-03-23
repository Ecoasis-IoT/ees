<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="../assets/images/logo_icon.png">

<!--Font Awesome-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/font-awesome.min.css">        

<!--DataTables-->
<link rel="stylesheet" href="../assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/main.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<!--ApexCharts-->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<style>

.dt-button {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;        
}      

.fa {
	padding-right: 5px;
}

.table {
    border-color: #9e9e9e;
}

.chartBox {
	height: 200px;
}

#main-content {
    min-height: 100%;
}

img {
    width: 100%; /* or any custom size */
    height: 100%; 
    object-fit: contain;
}

</style>

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

                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="card top_counter shadow-sm">
                            <div class="body">
                                <div class="icon" style="color: #70AD47;"><i class="fa fa-arrow-up"></i> </div>
                                <div class="content">
                                    <div class="text">Total In</div>
                                    <h5 class="number">530 m<sup>3</sup></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card top_counter shadow-sm">
                            <div class="body">
                                <div class="icon" style="color: #B32A2E;"><i class="fa fa-arrow-down"></i> </div>
                                <div class="content">
                                    <div class="text">Total Out</div>
                                    <h5 class="number">430 m<sup>3</sup></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card top_counter shadow-sm">
                            <div class="body">
                                <div class="icon" style="color: #00bdaa;"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" style="fill: #00bdaa;margin-top:6px;"><path d="M12 22c4.636 0 8-3.468 8-8.246C20 7.522 12.882 2.4 12.579 2.185a1 1 0 0 0-1.156-.001C11.12 2.397 4 7.503 4 13.75 4 18.53 7.364 22 12 22zm-.001-17.74C13.604 5.55 18 9.474 18 13.754 18 17.432 15.532 20 12 20s-6-2.57-6-6.25c0-4.29 4.394-8.203 5.999-9.49z"></path></svg></div>
                                <div class="content">
                                    <div class="text">Tank Capacity</div>
                                    <h5 class="number">100 m<sup>3</sup></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-8 col-md-12 col-sm-12">                            																		
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>Meters Map<small>Meters Locations</small></h2>
                            </div>
                            <div class="body">
                                <img src="../assets/images/map.png">
                            </div>
                        </div>
                            
                        <div class="row clearfix g-3 mb-3">
                            <div class="col-lg-12">
                                <div class="card shadow-sm" style="margin-top: 14px;">
                                    <div class="header">
                                        <h2>CWA Water Consumption <small>Water Consumption - 2023 v/s 2024</small></h2>
                                    </div>
                                    <div class="body">
             	                        <div class="chartBox" style="height: 350px;">
                	                        <canvas id="lineChart"></canvas> 
                	                    </div> 
            	                    </div>
        	                    </div>
    	                    </div>
	                    </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>Total Water Consumption<small>Yesterday's total water consumption</small></h2>
                            </div>                            
                    		<div class="body" id="chart-con" style="height: 100%;">
        	                    <div class="chartBox" style="height: 357px;">
        	                        <canvas id="doughChart"></canvas> 
        	                    </div> 
                                <table class="table m-t-25 m-b-0">
                                    <tbody>
                                        <tr>                                   
                                            <td>Village 1</td>
                                            <td>200</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 2</td>
                                            <td>240</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 3</td>
                                            <td>430</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 4</td>
                                            <td>213</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 5</td>
                                            <td>204</td>
                                        </tr>  
                                        <tr>                                   
                                            <td>Village 6</td>
                                            <td>200</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 7</td>
                                            <td>190</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 8</td>
                                            <td>180</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 9</td>
                                            <td>165</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 10</td>
                                            <td>66</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 11</td>
                                            <td>49</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 12</td>
                                            <td>49</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 13</td>
                                            <td>70</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 14</td>
                                            <td>90</td>
                                        </tr>
                                        <tr>                                   
                                            <td>Village 15</td>
                                            <td>110</td>
                                        </tr>
                                    </tbody>
                                </table>        	                    
                            </div>   
                        </div>
                    </div>
                </div>
                
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12" style="margin-top: 0px;">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2>Daily Total Water Consumption <small>Total Water Consumption by Village</small></h2>
                            </div>  
                            <div class="body">
     	                        <div class="chartBox" style="height: 350px;">
        	                        <canvas id="barChart"></canvas> 
        	                    </div>
    	                    </div>
	                    </div>
                    </div>
                </div>  
                
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12">
                        <div class="card shadow-sm" style="margin-top: 14px;">
                            <div class="header">
                                <h2>Energy Usage <small>Usage by locations</small></h2>
                            </div>
                            <div class="body">
                                <div id="bar_chart2">
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
const dataDoughnut = {
  labels: ['Village 1','Village 2', 'Vilage 3', 'Village 4','Village 5','Village 6', 'Vilage 7', 'Village 8','Village 9','Village 10', 'Vilage 11', 'Village 12','Village 13','Village 14', 'Vilage 15'],
  datasets: [{
    label: 'Water Consumption (m3)',
    data: [200, 240, 430, 213, 204, 200, 190, 180, 165, 66, 49, 49,70,90,110],
    backgroundColor: [
      '#00bdaa', '#70AD47', '#E58520', '#B32A2E','#00bdaa', '#70AD47', '#E58520', '#B32A2E','#00bdaa', '#70AD47', '#E58520', '#B32A2E'
    ]
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
		    display: false,
			position: "top"	      
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

<script>
// setup 
const dataBar = {
  labels: ['Village 1','Village 2', 'Vilage 3', 'Village 4','Village 5','Village 6', 'Vilage 7', 'Village 8','Village 9','Village 10', 'Vilage 11', 'Village 12','Village 13','Village 14', 'Vilage 15'],
  datasets: [{
    label: 'Water Consumption (m3)',
    data: [200, 240, 430, 213, 204, 200, 190, 180, 165, 66, 49, 49,70,90,110],
    backgroundColor: [
      '#00bdaa', '#70AD47', '#E58520', '#B32A2E','#00bdaa', '#70AD47', '#E58520', '#B32A2E','#00bdaa', '#70AD47', '#E58520', '#B32A2E'
    ]
  }]
};

// config 
const configBar = {
  type: 'bar',
  data: dataBar,
  options: {
  	maintainAspectRatio:false,
    plugins: {
		legend: {
		    display: false,
			position: "top"	      
		},
		datalabels: {
			color: 'white'
		}	      
    },
    scales: {
        y: {
            title: {
                display: true,
                text: 'Water Consumption (m3)',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            }            
        },
        x: {
            title: {
                display: true,
                text: 'Village',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            }            
        }        
    },    
    },
};

// render init block
const barChart = new Chart(
  document.getElementById('barChart'),
  configBar
);

</script>

<script>
// setup 
const dataLine = {
  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
  datasets: [{
    label: 'Water Consumption (m3) - 2023',
    data: [200, 740, 430, 213, 204, 200, 190, 180, 165, 66, 49, 400],
    backgroundColor: "#6CBBF1",
    borderColor: "#6CBBF1"
  },
    {
        label: 'Water Consumption (m3) - 2024',
        data: [250, 600, 480, 211, 241, 210, 171, 280, 115, 63, 60, 500],
        backgroundColor: "#00BDAA",
        borderColor: "#00BDAA"
    }  ]
};

// config 
const configLine = {
  type: 'line',
  data: dataLine,
  options: {
  	maintainAspectRatio:false,
    plugins: {
		legend: {
			position: "top"	      
		},
		datalabels: {
			color: 'white'
		}	      
    },
    tension: 0.4,
    scales: {
        y: {
            title: {
                display: true,
                text: 'Water Consumption (m3)',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            }            
        },
        x: {
            title: {
                display: true,
                text: 'Month',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            }            
        }        
    }    
  }
};

// render init block
const lineChart = new Chart(
  document.getElementById('lineChart'),
  configLine
);

</script>

<script>
       var options = {
          series: data,
          chart: {
          height: 450,
          type: 'heatmap',
        },
        dataLabels: {
          enabled: false
        },
        colors: colors,
        xaxis: {
          type: 'category',
          categories: ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '01:00', '01:30']
        },
        title: {
          text: 'HeatMap Chart (Different color shades for each series)'
        },
        grid: {
          padding: {
            right: 20
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#bar_chart2"), options);
        chart.render();
      
      
    
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
