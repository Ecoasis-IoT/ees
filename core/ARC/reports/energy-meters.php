<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="../assets/images/logo_icon.png">

<link rel="stylesheet" href="../assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/custom.css">
<link rel="stylesheet" href="../assets/css/main.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<style>

.chartBox {
    height: 600px;
}

input {
    padding: 5px;
    width: auto;    
}


.card .header {
    padding: 7px;
}

@media only screen and (max-width: 600px) {
    strong {
        display: block;
    }
  }
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
    

    <div id="main-content">
        <div class="container-fluid">
            <div class="block-header">
                <div class="row g-3">
                    <div class="col-lg-5 col-md-8 col-sm-12">                        
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Energy Meters</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard/dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Device Management</li>
                            <li class="breadcrumb-item active">Energy Meters</li>
                        </ul>
                    </div>            
                </div>
            </div>
     
            <div class="card mb-3 no-border">
                <div class="row">
                    <div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
                        <div class="header">
                            <h2>CHOOSE AN ENERGY METER:</h2>                            
                        </div>
                        <div class="control">
                            <select name="sort" id="sort" class="alarms-sort">
                                <option>All</option>
                                <option>Main Meter</option>
                                <option>Energy Meter 1</option>
                                <option>Energy Meter 2</option>
                                <option>Energy Meter 3</option>
                                <option>Energy Meter 4</option>
                                <option>Energy Meter 5</option>
                                <option>Energy Meter 6</option>
                                <option>Energy Meter 7</option>
                                <option>Energy Meter 8</option>
                                <option>Energy Meter 9</option>
                                <option>Energy Meter 10</option>
                            </select>
                        </div> 
                    </div>
                    
                    <div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
                        <div class="header">
                            <h2>SELECT A SITE:</h2>                            
                        </div>
                        <div class="control">
                            <select name="sort" id="sort" class="alarms-sort">
                                <option>Mall of Mauritius @ Bagatelle</option>
                                <option>Phoenix Mall</option>
                                <option>Trou aux Biches Hotel</option>
                                <option>Paradis Golf Resort & Spa</option>
                                <option>Le Mauric999ia Hotel</option>
                                <option>Deco City Bagatelle</option>
                                <option>Kendra Commercial Centre</option>
                                <option>Victoria Hotel</option>
                                <option>Shandrani Resort & Spa</option>
                                <option>ENL HOUSE</option>
                            </select>
                        </div> 
                    </div>                    

                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <div class="header">
                            <h2>FILTER BY DATE:</h2>  
                        </div>  
                        <div class="date-container">
                            <strong>Start Date: </strong><input onchange="filterData()" type="date" id="startdate">
                             &nbsp <strong>End Date: </strong><input onchange="filterData()" type="date" id="enddate">
                        </div>                        
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <button class="btn btn-primary" style="display: inline-block;" id="cmd" onclick="">Generate Report</button>         
                    </div>
                </div>                   
            </div>
        
        
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;">Energy Meter Report</h2> 
                        </div>
                        <div class="body">                           
                            <div class="table-responsive">
                                <table id="example3" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export">
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
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="body">   
    	                    <div class="chartBox">
    	                        <canvas id="myChart"></canvas> 
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
const dates = ['07 Mar 2024 09:00','07 Mar 2024 09:30','07 Mar 2024 10:00','07 Mar 2024 10:30','07 Mar 2024 11:00','07 Mar 2024 11:30','07 Mar 2024 12:00']
const dataset1 = [18, 12, 6, 9, 12, 3, 9]
const dataset2 = [9, 15, 12, 6, 15, 9, 6]
const dataset3 = [11, 5, 9, 2, 12, 4, 1]

const plugin = {
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

const data = {
  labels: dates,
  datasets: [{
    label: 'ACTIVE POWER',
    data: dataset1,
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
    data: dataset2,
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
    data: dataset3,
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
const config = {
  type: 'line',
  data,
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
  plugins: [plugin],
};

// render init block
const myChart = new Chart(
  document.getElementById('myChart'),
  config
);

</script>

<!-- Javascript -->
<script src="../assets/bundles/libscripts.bundle.js"></script>    
<script src="../assets/bundles/vendorscripts.bundle.js"></script>

<script src="../assets/bundles/mainscripts.bundle.js"></script>
<script src="../assets/js/pages/tables/jquery-datatable.js"></script>
<script src="../assets/bundles/datatablescripts.bundle.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
</body>
</html>
