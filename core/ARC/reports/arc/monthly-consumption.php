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
<link rel="stylesheet" href="../assets/css/main.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<style>

.dt-button {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;        
} 

.date-container {
    margin-left: 7px;
}

input {
    padding: 5px;
    width: auto;    
}

#cmd {
    right: 0;
    bottom: 0;
    position: absolute;
    margin-right: 7px;
    margin-bottom: 9px;
}

.card .header {
    padding: 7px;
}

.date-container input {
    padding-bottom: 5px;
    margin-bottom: 9px;
    font-size: 14px;
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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Monthly Consumption</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard/dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Reports</li>
                            <li class="breadcrumb-item active">Monthly Consumption</li>
                        </ul>
                    </div>            
                </div>
            </div>
     
            <div class="card mb-3 no-border">
                <div class="row">
                    <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12">
                        <div class="header">
                            <h2>SELECT A ZONE:</h2>                            
                        </div>
                        <div class="control">
                            <select name="sort" id="sort" class="alarms-sort">
                                <option>Zone 1</option>
                                <option>Zone 2</option>
                                <option>Zone 3</option>
                                <option>Zone 4</option>
                                <option>Zone 5</option>
                                <option>Zone 6</option>
                                <option>Zone 7</option>
                                <option>Zone 8</option>
                                <option>Zone 9</option>
                                <option>Zone 10</option>                                
                            </select>
                        </div> 
                    </div>
                    
                    <div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
                        <div class="header">
                            <h2>CHOOSE A WATER METER:</h2>                            
                        </div>
                        <div class="control">
                            <select name="sort" id="sort" class="alarms-sort">
                                <option>Main Meter</option>
                                <option>Water Meter 1</option>
                                <option>Water Meter 2</option>
                                <option>Water Meter 3</option>
                                <option>Water Meter 4</option>
                                <option>Water Meter 5</option>
                                <option>Water Meter 6</option>
                                <option>Water Meter 7</option>
                                <option>Water Meter 8</option>
                                <option>Water Meter 9</option>
                                <option>Water Meter 10</option>
                            </select>
                        </div> 
                    </div>

                    <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12">
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
                            <h2 style="display:inline-block;">Water Meter Report</h2> 
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="example3" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export">
                                    <thead>
                                        <tr>
                                            <th>Meter</th>
                                            <th>Month</th>
                                            <th>Start Date</th>
                                            <th>Start Reading</th>
                                            <th>End Date</th>
                                            <th>End Reading</th>
                                            <th>Consumption (m<sup>3</sup>)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>January</td>
                                            <td>01/01/2024</td>
                                            <td>1000</td>
                                            <td>31/01/2024</td>
                                            <td>1500</td>
                                            <td>500</td>
                                        </tr>  
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>February</td>
                                            <td>01/02/2024</td>
                                            <td>1500</td>
                                            <td>29/02/2024</td>
                                            <td>1750</td>
                                            <td>250</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>March</td>
                                            <td>01/03/2024</td>
                                            <td>1750</td>
                                            <td>31/03/2024</td>
                                            <td>1900</td>
                                            <td>150</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>April</td>
                                            <td>01/04/2024</td>
                                            <td>1900</td>
                                            <td>30/04/2024</td>
                                            <td>2300</td>
                                            <td>400</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>May</td>
                                            <td>01/05/2024</td>
                                            <td>2300</td>
                                            <td>31/05/2024</td>
                                            <td>2800</td>
                                            <td>500</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>June</td>
                                            <td>01/06/2024</td>
                                            <td>2800</td>
                                            <td>30/06/2024</td>
                                            <td>3000</td>
                                            <td>200</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>July</td>
                                            <td>01/07/2024</td>
                                            <td>3000</td>
                                            <td>31/07/2024</td>
                                            <td>3320</td>
                                            <td>320</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>August</td>
                                            <td>01/08/2024</td>
                                            <td>3320</td>
                                            <td>31/08/2024</td>
                                            <td>3500</td>
                                            <td>180</td>
                                        </tr> 
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>September</td>
                                            <td>01/09/2024</td>
                                            <td>3500</td>
                                            <td>30/09/2024</td>
                                            <td>3800</td>
                                            <td>300</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>October</td>
                                            <td>01/10/2024</td>
                                            <td>3800</td>
                                            <td>31/10/2024</td>
                                            <td>4150</td>
                                            <td>350</td>
                                        </tr>
                                        <tr>
                                            <td>Main Meter</td>
                                            <td>November</td>
                                            <td>01/11/2024</td>
                                            <td>4150</td>
                                            <td>30/11/2024</td>
                                            <td>4400</td>
                                            <td>250</td>
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
const dates = ['January','February','March','April','May','June','July','August','September','October','November']
const dataset1 = [500, 250, 150, 400, 500, 200, 320, 180, 300, 350, 250]

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
    label: 'Water Consumption (m3)',
    data: dataset1,
    backgroundColor: [
      'rgba(0,189,170,0.2)'
    ],
    borderColor: [
      'rgba(0,189,170,1)'
    ],
    fill: 'origin',
    tension: 0.5,
    pointStyle: 'rect'
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
