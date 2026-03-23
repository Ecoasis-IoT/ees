<?php

include("scripts/auth.php");

$site_id = $_GET['site'];

//get site name
require("config/admin.php");

$get_site_name = "SELECT site_name, db_name from tbl_site where id = $site_id";
$results = mysqli_query($admin_link, $get_site_name);
$site_details = mysqli_fetch_assoc($results);

$site_name = $site_details["site_name"];
$site_db = $site_details["db_name"];

if(mysqli_num_rows($results) < 1){
    
    header('Location: dashboard.php');
}

mysqli_close($admin_link);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/themes/base/jquery-ui.min.css" integrity="sha512-8PjjnSP8Bw/WNPxF6wkklW6qlQJdWJc/3w/ZQPvZ/1bjVDkrrSqLe9mfPYrMxtnzsXFPc434+u4FHLnLjXTSsg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" integrity="sha512-Ww1y9OuQ2kehgVWSD/3nhgfrb424O3802QYP/A5gPXoM4+rRjiKrjHdGxQKrMGQykmsJ/86oGdHszfcVgUr4hA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>




<!--Font Awesome-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">        

<!--DataTables-->
<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="assets/css/main.css">

<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>-->

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> 

<style>
.top_counter .icon i {
    font-size: 35px;
    color: #00bdaa;
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
    
    <div id="content">
        <div id="main-content">
            <div class="container-fluid">
                <div class="block-header">
                    <div class="row g-3">
                        <div class="col-lg-5 col-md-8 col-sm-12">                        
                            <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Dashboard<?php echo " - $site_name" ?></h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ul>
                        </div>            
                    </div>
                </div>           
                
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card top_counter">
                            <div class="body">
                                <div class="icon text-info"><i class="icon-energy"></i> </div>
                                <div class="content">
                                    <div class="text">Current Active Power</div>
                                    <h5 class="number" id = "active_power"></h5>
                                </div>
                            </div>                        
                        </div>                        
                    </div>     
                    
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card top_counter">
                            <div class="body">
                                <div class="icon text-info" style="line-height:45px;"><img src="assets/images/solar.png" style="width:40px;height:auto;"> </div>
                                <div class="content">
                                    <div class="text">Today's Production</div>
                                    <h5 class="number" id="daily_prod"></h5>
                                </div>
                            </div>                        
                        </div>
                    </div>        	
    
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card top_counter">
                            <div class="body">
                                <div class="icon text-info" style="line-height:45px;"><img src="assets/images/solar-panel.png" style="width:40px;height:auto;"> </div>
                                <div class="content">
                                    <div class="text">Monthly Production</div>
                                    <h5 class="number" id="monthly_prod"></h5>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card top_counter">
                            <div class="body">
                                <div class="icon text-info" style="line-height:45px;"><img src="assets/images/power.png" style="width:40px;height:auto;"> </div>
                                <div class="content">
                                    <div class="text">Yearly Production</div>
                                    <h5 class="number" id="yearly_prod"></h5>
                                </div>
                            </div>                        
                        </div>
                    </div>                    
                </div>
                
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12">
                        <div class="datepicker">
                            <label>Choose date:</label>
                            <button class="dateBtn" id="prevDate" type="button"><i class="fa fa-angle-left"></i></button>
                            <input type="date" id="calendar" style="padding:6px;" onchange="render();" max="<?php echo date("Y-m-d"); ?>">                            
                            <button class="dateBtn" id="nextDate" type="button"><i class="fa fa-angle-right"></i></button>
                        </div>
                    </div>
                </div>
                            
                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2 style="display: inline-block;">Plant KPI<small>Production | Insolation | Power</small> </h2> 
                                <i class="fa fa-download" style="font-size: 18px; float:right;cursor:pointer;" onclick="downloadbar()"  title="Download"></i>
                            </div>
        
                            <div class="body chartKPI">                        
                                <canvas id="kpi"></canvas>                        
                            </div>                    
                        </div>
                    </div>                     
                </div>
                
                <div class="row clearfix g-3 mb-3">
                    
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2 style="display: inline-block;">Production <small>Hourly Production</small> </h2> 
                                <i class="fa fa-download" style="font-size: 18px; float:right;cursor:pointer;" onclick="downloadbar()"  title="Download"></i>
                            </div>	
                    		<div class="body" id="chart-con" style="height: 100%;">
        	                    <div class="chartBox" style="height: 499px;">
        	                        <canvas id="barChart"></canvas> 
        	                    </div> 
                            </div>
                        </div>
                    </div>
                        
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="header">
                                <h2 style="display: inline-block;">Weather<small>Insolation | Ambient Temperature | Panel Temperature</small> </h2> 
                                <i class="fa fa-download" style="font-size: 18px; float:right;cursor:pointer;" onclick="download()"  title="Download"></i>
                            </div>	
                    		<div class="body" id="chart-con" style="height: 100%;">
        	                    <div class="chartBox" style="height: 499px;">
        	                        <canvas id="lineChart"></canvas> 
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



<!-- GET Site Data -->
<script>

var barchart_labels = [];
var barchart_data = [];
var line_labels = [];
var line_irradiance = [];
var line_ambtemp = [];
var line_pantemp = [];
var active_power = [];

//Get Card Data
    $(function get_card_data(){
    
        $.ajax({
            type: "POST",
            url: "scripts/get_site_card_data.php",
            async: false,
            data: {
                "site_db": '<?php echo $site_db;?>'
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                
                // console.log(data);
                
                document.getElementById('active_power').innerHTML = data.active_power.toFixed(2) + " kW";
                
                if(data.daily_prod < 1000){
                    document.getElementById('daily_prod').innerHTML = data.daily_prod.toFixed(2) + " kWh";    
                }
                else{
                    document.getElementById('daily_prod').innerHTML = (data.daily_prod/1000).toFixed(2) + " MWh";
                }
                
                if(data.monthly_prod < 1000){
                    document.getElementById('monthly_prod').innerHTML = data.monthly_prod.toFixed(2) + " kWh";    
                }
                else{
                    document.getElementById('monthly_prod').innerHTML = (data.monthly_prod/1000).toFixed(2) + " MWh";
                }
                
                if(data.yearly_prod < 1000){
                    document.getElementById('yearly_prod').innerHTML = data.yearly_prod.toFixed(2) + " kWh";    
                }
                else{
                    document.getElementById('yearly_prod').innerHTML = (data.yearly_prod/1000).toFixed(2) + " MWh";
                }
                
                
            }
        });
    });




//Get Barchart Data
function get_barchart_data(date){
    
    $.ajax({
        type: "POST",
        url: "scripts/get_site_barchart.php",
        async: false,
        data: {
            "site_db": '<?php echo $site_db;?>',
            "date": date
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            // console.log(data);
            for(var i = 0; i < data.length; i++){
                
                barchart_labels.push(data[i].time);
                barchart_data.push(data[i].production)
                
            }
            
        }
    });
    
    
}

//Get Line Chart Data
function get_linechart_data(date){
    
    $.ajax({
        type: "POST",
        url: "scripts/get_site_irradiance.php",
        async: false,
        data: {
            "site_db": '<?php echo $site_db;?>',
            "date": date
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            
            // console.log(dataResult);
            
            for(var i = 0; i < data.length; i++){
                
                line_labels.push(data[i].time);
                line_irradiance.push(data[i].irradiance/1000)
                line_ambtemp.push(data[i].ambient_temp);
                line_pantemp.push(data[i].panel_temp)
                
            }
        }
    });
    
}

function getActivePower(date){
    
    $.ajax({
        type: "POST",
        url: "scripts/get_site_active_power.php",
        async: false,
        data: {
            "site_db": '<?php echo $site_db;?>',
            "date": date
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            
            for(var i = 0; i < data.length; i++){
                
                active_power.push(data[i].active_power);
            }
        }
    });
}

function renderKpiChart(){
    
    console.log(barchart_data);
    
    const dataKPI = {
      labels: barchart_labels,
      datasets: [
      {
        type: 'line',
        label: 'Active Power (kW)',
        yAxisID: 'A',
        data: active_power,
        fill: false,
        backgroundColor: '#36648B',
        borderColor: '#36648B',
        tension: 0.4,
        pointRadius: 0
      },
        {
        type: 'line',
        label: 'Insolation (kWh/m2)',
        yAxisID: 'B',
        data: line_irradiance,
        fill: false,
        backgroundColor: '#F2BB46',
        borderColor: '#F2BB46',
        title: 'Insolation (kWh/2)',
        tension: 0.4,
        pointRadius: 0       
      },      
      {
        type: 'bar',
        label: 'Production (kWh)',
        yAxisID: 'C',
        data: barchart_data,
        backgroundColor: '#CA5952'
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
                    text: 'Insolation (kWh/m2)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }                
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
              }        
            }
        },
        plugins: {
            legend: {
                position: 'bottom',
                display: true,
            }
        },
        maintainAspectRatio:false,
        responsive: true,
        interaction: {
            mode: 'index'
        },
      },
    //   plugins: [plugin],
    };
    
    let chartStatus = Chart.getChart("kpi"); // <canvas> id
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    
    // render init block
    const kpi = new Chart(
      document.getElementById('kpi'),
      configKPI
    );
    
}


function renderBarchart(){
    
    const dataBar = {
      labels: barchart_labels,
      datasets: [{
        label: 'Production (KWh)',
        data: barchart_data,
        backgroundColor: [
          '#CA5952'
        ],
        borderColor: [
          '#CA5952'
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
    	    },
            title: {
                display: true,
                text: 'Time',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            },
            ticks:{
                autoSkip: false
            }
    	  },
    	  y: {
    	    grid: {
    	      display: false
    	    },
            title: {
                display: true,
                text: 'Production(kWh)',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            }                                	    
    	  }
    	},
        maintainAspectRatio:false,
        responsive: true
      }
    };
    
    let chartStatus = Chart.getChart("barChart"); // <canvas> id
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    
    // render init block
    const barChart = new Chart(
      document.getElementById('barChart'),
      configBar
    );
    
}

function renderLineChart(){
    
    // setup Line Chart
    const dataLine = {
      labels: line_labels,
      datasets: [{
        type: 'line',
        label: 'Insolation (kWh/m2)',
        yAxisID: 'A',
        fill: false,
        data: line_irradiance,
        backgroundColor: "#F2BB46",
        borderColor: "#F2BB46",
        title: 'Insolation (kWh/2)',
        tension: 0.4
      },
    {
        type: 'line',
        label: 'Ambient Temperature (°C)',
        yAxisID: 'B',
        fill: false,
        data: line_ambtemp,
        backgroundColor: "RGB(154,181,255)",
        borderColor: "RGB(154,181,255)",
        title: 'Ambient Temperature (°C)',
        tension: 0.4
    },
    {
        type: 'line',
        label: 'Panel Temperature (°C)',
        yAxisID: 'C',
        fill: false,
        data: line_pantemp,
        backgroundColor: "#ff7a00",
        borderColor: "#ff7a00",
        title: 'Panel Temperature (°C)',
        tension: 0.4
    }
    ]
    };
    
    // config 
    const configLine = {
      type: 'line',
      data: dataLine,
      options: {
        enabled: true,
        scales: {       
            A: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Insolation (kWh/m2)',
                    font: {
                        size: 13,
                        weight: 'bold'
                    }
                },
        	    grid: {
        	      display: false
        	    }            
            },
            B: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Temperature (°C)',
                    font: {
                        size: 13,
                        weight: 'bold'
                    }                
                },
        	    grid: {
        	      display: false
        	    }            
            },
            // C: {
            //     type: 'linear',
            //     position: 'right',
            //     title: {
            //         display: true,
            //         text: 'Test',
            //         font: {
            //             size: 13,
            //             weight: 'bold'
            //         }
            //     },
        	   // grid: {
        	   //   display: false
        	   // },            
            // },
            x: {
              title: {
                display: true,
                text: 'Time',
                font: {
                    size: 13,
                    weight: 'bold'
                }           
              },
              
            }
        },
        maintainAspectRatio:false,
        responsive: true,
        interaction: {
            mode: 'index'
        },
      },
      
    };
    
    let chartStatus = Chart.getChart("lineChart"); // <canvas> id
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    // render init block
    const lineChart = new Chart(
      document.getElementById('lineChart'),
      configLine
    );
}

var date = new Date();
var currentDate = date.toISOString().substring(0,10);
document.getElementById('calendar').value = currentDate;

const prevDate = document.getElementById("prevDate");
const nextDate = document.getElementById("nextDate");

prevDate.addEventListener('click', () => { // add a click event listener
    
    let val = new Date(document.getElementById('calendar').value);
    val.setDate(val.getDate() - 1);
    let calendar_date = val.toISOString().substring(0,10);
    document.getElementById('calendar').value = calendar_date;
    
    render();
    
});

nextDate.addEventListener('click', () => { // add a click event listener
    
    let val = new Date(document.getElementById('calendar').value);
    val.setDate(val.getDate() + 1);
    let calendar_date = val.toISOString().substring(0,10);
    document.getElementById('calendar').value = calendar_date;
    
    render();
    
});

function render(){
    
    let val = new Date(document.getElementById('calendar').value);
    let db_date = val.toISOString().split('T')[0];
    
    barchart_labels = [];
    barchart_data = [];
    line_labels = [];
    line_irradiance = [];
    line_ambtemp = [];
    line_pantemp = [];
    active_power = [];
    
    get_barchart_data(db_date);
    get_linechart_data(db_date);
    getActivePower(db_date);
    
    renderKpiChart();
    renderBarchart();
    renderLineChart();
    
}


$(document).ready(function() {
    
    var val = new Date(document.getElementById('calendar').value);
    let date = val.toISOString().split('T')[0];
    
    get_barchart_data(date);
    get_linechart_data(date);
    getActivePower(date);
    renderKpiChart();
    renderBarchart();
    renderLineChart();
    
});





//New

// const kpi = {
//   labels: ["01:00","02:00","03:00","04:00","05:00","06:00","07:00"],
//   datasets: [{
//     type: 'line',
//     label: 'Insolation (kWh/m2)',
//     yAxisID: 'A',
//     data: ["1","2","3","4","5","6","7"],
//     fill: false,
//     backgroundColor: '#F2BB46',
//     borderColor: '#F2BB46',
//     title: 'Insolation (kWh/2)',
//     tension: 0.4,
//     pointRadius: 0       
//   },
//   {
//     type: 'line',
//     label: 'PR (%)',
//     yAxisID: 'B',
//     data: ["1","2","3","4","5","6","7"],
//     fill: false,
//     backgroundColor: '#7CAF57',
//     borderColor: '#7CAF57',
//     tension: 0.4,
//     pointRadius: 0
//   },
//     {
//     type: 'bar',
//     label: 'Production (kWh)',
//     yAxisID: 'C',
//     data: ["1","2","3","4","5","6","7"],
//     backgroundColor: '#CA5952'
//   }  ]
// };

// const plugin = {
//   id: 'customCanvasBackgroundColor',
//   beforeDraw: (chart, args, options) => {
//     const {ctx} = chart;
//     ctx.save();
//     ctx.globalCompositeOperation = 'destination-over';
//     ctx.fillStyle = options.color || 'white';
//     ctx.fillRect(0, 0, chart.width, chart.height);
//     ctx.restore();
//   }
// };
// // config 

// const configKPI = {
//   data: kpi,
//   options: {
//     enabled: true,
//     scales: {       
//         A: {
//             type: 'linear',
//             position: 'right',
//             title: {
//                 display: true,
//                 text: 'Insolation (kWh/m2)',
//                 font: {
//                     size: 17,
//                     weight: 'bold'
//                 }
//             }            
//         },
//         B: {
//             type: 'linear',
//             position: 'right',
//             title: {
//                 display: true,
//                 text: 'PR (%)',
//                 font: {
//                     size: 17,
//                     weight: 'bold'
//                 }                
//             }            
//         },
//         C: {
//             type: 'linear',
//             position: 'left',
//             title: {
//                 display: true,
//                 text: 'Production (kWh)',
//                 font: {
//                     size: 17,
//                     weight: 'bold'
//                 }
//             }            
//         },
//         x: {
//           title: {
//             display: true,
//             text: 'Day',
//             font: {
//                 size: 17,
//             }            
//           }        
//         }
//     },
//     plugins: {
//         legend: {
//             position: 'bottom',
//             display: true,
//         }
//     },
//     maintainAspectRatio:false,
//     responsive: true,
//     interaction: {
//         mode: 'index'
//     },
//   },
//   plugins: [plugin],
// };


// // render init block
// const kpi = new Chart(
//   document.getElementById('kpi'),
//   configKPI
// );
    
</script>

<script>
function download() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('doughChart');
    imageLink.download = 'report.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}   

function downloadbar() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('barChart');
    imageLink.download = 'report.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}
</script>


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
