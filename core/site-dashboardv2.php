<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

$site_id = intval($_GET['site'] ?? 0);
if (!$site_id) { header('Location: ' . ees_url_path('dashboard.php')); exit; }

try {
    $stmt = getDB('admin')->prepare("SELECT site_name, db_name FROM tbl_site WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $site_id]);
    $site_details = $stmt->fetch();
} catch (PDOException $e) {
    error_log("site-dashboardv2 PDO error: " . $e->getMessage());
    header('Location: ' . ees_url_path('dashboard.php')); exit;
}
if (!$site_details) { header('Location: ' . ees_url_path('dashboard.php')); exit; }

$site_name = $site_details['site_name'];
$site_db   = $site_details['db_name'];
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

<style>
.top_counter .icon i {
    font-size: 35px;
    color: #00bdaa;
}    

#grid_availability {
    font-family:'Nunito Sans', sans-serif;
    font-weight: bold;
}

.space:nth-child(3) {
    margin-left:auto;
}

.space:nth-child(1) {
    margin-right:auto;
}


.space:nth-child(3) {
    margin-right: 50px;
}

.space:nth-child(1) {
    margin-left: 50px;
}

#zoom_reset {
  margin-right: 32px;
  float: right;  
}

.fa-download {
    font-size: 18px; 
    float: right;
    cursor:pointer;
    margin-right: -134px;
    margin-top: 9px;    
}

@media (max-width: 820px) {
    .space:nth-child(3) {
        margin-right: 0px;
        margin-left: 0px;
        
    }
    .space:nth-child(1) {
        margin-left: 0px;
        margin-right: 0px;
    }    

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
                        <div class="col-lg-6 col-md-8 col-sm-12">
                            <h2><?php echo htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8'); ?></h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active"><?php echo htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8'); ?></li>
                            </ul>
                        </div>            
                    </div>
                </div>           
                
                <!-- KPI Stat Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon green"><i class="fa fa-bolt"></i></div>
                            <div>
                                <div class="ees-stat-label">Today's Production</div>
                                <div class="ees-stat-value" id="daily_prod">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon blue"><i class="fa fa-calendar"></i></div>
                            <div>
                                <div class="ees-stat-label">Monthly Production</div>
                                <div class="ees-stat-value" id="monthly_prod">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon orange"><i class="fa fa-line-chart"></i></div>
                            <div>
                                <div class="ees-stat-label">Yearly Production</div>
                                <div class="ees-stat-value" id="yearly_prod">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon teal"><i class="fa fa-tachometer"></i></div>
                            <div>
                                <div class="ees-stat-label">Active Power</div>
                                <div style="font-size:13px;margin-top:4px;">PVDB 1: <strong id="active_power1">—</strong></div>
                                <div style="font-size:13px;">PVDB 2: <strong id="active_power2">—</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon orange"><i class="fa fa-sun-o"></i></div>
                            <div>
                                <div class="ees-stat-label">Avg Irradiance</div>
                                <div class="ees-stat-value" id="avg_irradiance">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ees-stat-card">
                            <div class="ees-stat-icon blue"><i class="fa fa-clock-o"></i></div>
                            <div>
                                <div class="ees-stat-label">Sun Hours</div>
                                <div class="ees-stat-value" id="sun_hours">—</div>
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
                                <h2 style="display: inline-block;">Plant KPI<small>Production | Irradiance | Power</small> </h2> 
                                <button class="btn btn-primary" id="zoom_reset" onclick="resetZoomBtn(kpi)">Reset Zoom</button>
                                <i class="fa fa-download" onclick="downloadKPI()"  title="Download"></i>
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
                                <h2 style="display: inline-block;">Weather<small>Irradiance | Ambient Temperature | Panel Temperature</small> </h2> 
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


var kpi_irradiance = [];
var kpi_prod = [];
var kpi_active_power1 = [];
var kpi_active_power2 = [];

//Get Card Data
    $(function get_card_data(){
    
        $.ajax({
            type: "POST",
            url: "scripts/get_site_card_datav2",
            async: false,
            data: {
                "site_db": '<?php echo $site_db;?>'
            },
            success: function(dataResult) {
                var data = dataResult;
                
                // console.log(data);
                
                document.getElementById('active_power1').innerHTML = data.active_power1.toFixed(2) + " kW";
                document.getElementById('active_power2').innerHTML = data.active_power2.toFixed(2) + " kW";
                
                
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
                
                document.getElementById('avg_irradiance').innerHTML = data.avg_irr + " W/m<sup>2<sup>";
                
                
                let sun_hours = Math.floor(parseInt(data.sun_hours) / 60);          
                let sun_minutes = parseInt(data.sun_hours) % 60;
                
                document.getElementById('sun_hours').innerHTML = sun_hours + " hours " + sun_minutes + " minutes ";
                
            }
        });
    });




//Get Barchart Data
function get_barchart_data(date){
    
    $.ajax({
        type: "POST",
        url: "scripts/get_site_barchart",
        async: false,
        data: {
            "site_db": '<?php echo $site_db;?>',
            "date": date
        },
        success: function(dataResult) {
            var data = dataResult;
            // console.log(data);
            for(var i = 0; i < data.length; i++){
                
                kpi_prod.push({"x": data[i].time, "y": data[i].production});
                
                barchart_labels.push(data[i].time);
                barchart_data.push(data[i].production)
                
            }
            // console.log(kpi_prod);
        }
    });
    
    
}

//Get Line Chart Data
function get_linechart_data(date){
    
    $.ajax({
        type: "POST",
        url: "scripts/get_site_irradiance",
        async: false,
        data: {
            "site_db": '<?php echo $site_db;?>',
            "date": date
        },
        success: function(dataResult) {
            var data = dataResult;
            
            // console.log(dataResult);
            
            for(var i = 0; i < data.length; i++){
                
                kpi_irradiance.push({"x": data[i].time, "y": data[i].irradiance});
                
                line_labels.push(data[i].time);
                line_irradiance.push(data[i].irradiance)
                line_ambtemp.push(data[i].ambient_temp);
                line_pantemp.push(data[i].panel_temp)
                
            }
            // console.log(kpi_irradiance);
        }
    });
    
}

function getActivePower(date){
    
    $.ajax({
        type: "POST",
        url: "scripts/get_site_active_powerv2",
        async: false,
        data: {
            "site_db": '<?php echo $site_db;?>',
            "date": date
        },
        success: function(dataResult) {
            var data = dataResult;
            console.log(data);
            for(var i = 0; i < data.length; i++){
                
                if(data[i].meter_id == "100"){
                    kpi_active_power1.push({"x": data[i].time, "y": data[i].active_power});    
                }
                else{
                    kpi_active_power2.push({"x": data[i].time, "y": data[i].active_power});    
                }
                
            }
            console.log(kpi_active_power1);
            console.log(kpi_active_power2);
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


var kpi;

function renderKpiChart(){
    
    // console.log(barchart_data);
    
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
    
    const dataKPI = {
      labels: line_labels,
      datasets: [
      {
        type: 'line',
        label: 'Main Meter 1 - Active Power (kW)',
        yAxisID: 'A',
        data: kpi_active_power1,
        fill: false,
        // backgroundColor: '#36648B',
        // borderColor: '#36648B',
        backgroundColor: '#5cb7f2',
        borderColor: '#5cb7f2',        
        tension: 0.4,
        pointRadius: 1,
        // pointHoverBackgroundColor: '#36648B',
        // pointBorderColor: 'transparent',
        // pointBackgroundColor: 'transparent',
      },
      {
        type: 'line',
        label: 'Main Meter 2 - Active Power (kW)',
        yAxisID: 'A',
        data: kpi_active_power2,
        fill: false,
        // backgroundColor: '#36648B',
        // borderColor: '#36648B',
        backgroundColor: '#075282',
        borderColor: '#075282',        
        tension: 0.4,
        pointRadius: 1,
        // pointHoverBackgroundColor: '#36648B',
        // pointBorderColor: 'transparent',
        // pointBackgroundColor: 'transparent',
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
        // pointHoverBackgroundColor: '#F2BB46',
        // pointBorderColor: 'transparent',
        // pointBackgroundColor: 'transparent'
      },      
      {
        type: 'bar',
        label: 'Production (kWh)',
        yAxisID: 'C',
        data: kpi_prod,
        // data: [{x: "09:00",y: 3},{x: "10:00",y: 5},{x: "11:00",y: 3}],
        backgroundColor: '#CA5952',
        borderColor: '#CA5952',
        maxBarThickness: 35
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
            zoom: zoomOptions,
            legend: {
                position: 'bottom',
                display: true,
            },
            tooltip: {
                enabled: true,
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
    
    let chartStatus = Chart.getChart("kpi"); // <canvas> id
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    
    // render init block
    kpi = new Chart(
      document.getElementById('kpi'),
      configKPI
    );
    
}

function resetZoomBtn(){
    kpi.resetZoom();
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
      maxBarThickness: 30,
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
      },
      plugins: [white_back],
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
        label: 'Irradiance (W/m2)',
        yAxisID: 'A',
        fill: false,
        data: line_irradiance,
        backgroundColor: "#F2BB46",
        borderColor: "#F2BB46",
        title: 'Irradiance (W/2)',
        tension: 0.4,
        pointRadius: 1,
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
        tension: 0.4,
        pointRadius: 1,
      },
      {
        type: 'line',
        label: 'Panel Temperature (°C)',
        yAxisID: 'B',
        fill: false,
        data: line_pantemp,
        backgroundColor: "#ff7a00",
        borderColor: "#ff7a00",
        title: 'Panel Temperature (°C)',
        tension: 0.4,
        pointRadius: 1,
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
                    text: 'Irradiance (kWh/m2)',
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
                type: 'time',
                  time: {
                    parser: 'HH:mm:ss',
                    unit: 'hour',
                    tooltipFormat: 'HH:mm',
                    displayFormats: {
                      hour: 'HH:mm'
                    }                
                }
              },
            },
        maintainAspectRatio:false,
        responsive: true,
        interaction: {
            mode: 'index'
        },
      },
      plugins: [white_back],
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
// console.log(currentDate);
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
    if(calendar_date <= currentDate){
        document.getElementById('calendar').value = calendar_date;
        render();
    }
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
    
    kpi_irradiance = [];
    kpi_prod = [];
    kpi_active_power1 = [];
    kpi_active_power2 = [];
    
    get_barchart_data(db_date);
    get_linechart_data(db_date);
    getActivePower(db_date);
    
    // console.log(kpi_irradiance);
    
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
//     label: 'Irradiance (kWh/m2)',
//     yAxisID: 'A',
//     data: ["1","2","3","4","5","6","7"],
//     fill: false,
//     backgroundColor: '#F2BB46',
//     borderColor: '#F2BB46',
//     title: 'Irradiance (kWh/2)',
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
//                 text: 'Irradiance (kWh/m2)',
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

<!--Download charts-->
<script>
function downloadKPI() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('kpi');
    imageLink.download = 'kpi.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}   

function downloadBar() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('barChart');
    imageLink.download = 'total-production.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}

function downloadWeather() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('lineChart');
    imageLink.download = 'weather.png';
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
