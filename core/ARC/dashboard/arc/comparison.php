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
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js"></script>


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
                    <div class="col-lg-4 col-md-6 col-md-12">
                        <div class="card">
                            <div class="body">
                                <div class="clearfix" style="padding-top:10.5%;padding-bottom:10.5%;">
                                    <div class="float-start">
                                        <h5 class="mb-0">Village ABCD</h5>
                                        <p class="text-muted">Main Meter</p>
                                    </div>
                                    <div class="float-end">
                                        <p style="padding: 0.375rem 0.75rem;border:1px solid #ddd;border-radius: 0.25rem;font-size:25px;">150 m<sup>3</sup></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-md-12">
                        <div class="card">
                            <div class="body">
                                <div class="clearfix" style="padding-top:10.5%;padding-bottom:10.5%;">
                                    <div class="float-start">
                                        <h5 class="mb-0">Village ABCD</h5>
                                        <p class="text-muted">Sub Meters</p>
                                    </div>
                                    <div class="float-end">
                                        <p style="padding: 0.375rem 0.75rem;border:1px solid #ddd;border-radius: 0.25rem;font-size:25px;">145 m<sup>3</sup></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-md-12">
                        <div class="card">
                            <div class="body">
                                <div class="clearfix">
                                    <div class="float-start">
                                        <h5 class="mb-0">Consumption Difference</h5>
                                        <div class="float-start">
                 	                        <div class="chartBox" style="height: 120px;width:100px;">
                    	                        <canvas id="barChart"></canvas> 
                    	                    </div>
                	                    </div>
                	                    <div class="float-end">
                                            <div class="icon" style="color: #70AD47;font-size:40px; margin-left:100px;padding-top:5%;"><i class="fa fa-caret-up"></i> 5 m<sup>3</sup> </div>
                                            <p class="text-muted" style="margin-left: 100px;">(Main - Total Sub)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>

                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2 style="display:inline-block;">Meters Consumption Report<small>Daily Water Meters Consumption Report</small></h2> 
                            </div>
                            <div class="body">                           
                                <div class="table-responsive">
                                    <table id="example3" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export">
                                        <thead>
                                            <tr>
                                                <th>Date/Time</th>
                                                <th>Meter</th>
                                                <th>Start Reading</th>
                                                <th>End Reading</th>
                                                <th>Consumption (m<sup>3</sup>)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>07 Mar 2024 08:00</td>
                                                <td>Meter 1</td>
                                                <td>145</td>
                                                <td>150</td>
                                                <td>5</td>
                                            </tr>  
                                            <tr>
                                                <td>07 Mar 2024 08:01</td>
                                                <td>Meter 2</td>
                                                <td>148</td>
                                                <td>155</td>
                                                <td>7</td>  
                                            </tr>    
                                            <tr>
                                                <td>07 Mar 2024 08:03</td>
                                                <td>Meter 3</td>
                                                <td>118</td>
                                                <td>124</td>
                                                <td>6</td> 
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 08:03</td>
                                                <td>Meter 4</td>
                                                <td>131</td>
                                                <td>134</td>
                                                <td>3</td>   
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 08:05</td>
                                                <td>Meter 5</td>
                                                <td>124</td>
                                                <td>125</td>
                                                <td>1</td> 
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 08:13</td>
                                                <td>Meter 6</td>
                                                <td>114</td>
                                                <td>118</td>
                                                <td>4</td>   
                                            </tr>
                                            <tr>
                                                <td>07 Mar 2024 08:18</td>
                                                <td>Meter 7</td>
                                                <td>115</td>
                                                <td>118</td>
                                                <td>3</td>   
                                            </tr> 
                                            <tr>
                                                <td>07 Mar 2024 08:21</td>
                                                <td>Meter 8</td>
                                                <td>112</td>
                                                <td>113</td>
                                                <td>1</td>   
                                            </tr>
                                            <tr>
                                                <td>07 Mar 2024 08:25</td>
                                                <td>Meter 9</td>
                                                <td>190</td>
                                                <td>198</td>
                                                <td>8</td>   
                                            </tr>  
                                            <tr>
                                                <td>07 Mar 2024 08:30</td>
                                                <td>Meter 10</td>
                                                <td>14</td>
                                                <td>18</td>
                                                <td>4</td>   
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
                        <div class="card shadow-sm">
                    		<div class="body" id="chart-con" style="height: 100%;">
                                <div class="header">
                                    <h2>Water Meters Consumption <small>Water Meters Consumption for today</small> </h2>
                                </div>                     		    
        	                    <div class="chartBox" style="height: 357px;">
        	                        <canvas id="lineChart"></canvas> 
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
// setup 
const dataBar = {
  labels: ['Main', 'Sub'],
  datasets: [
    {
    label: '',
    data: [280, 245],
    backgroundColor: ["#00BDAA","#70AD47"],
    borderColor: "#70AD47"
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
		    display: false
		    },
		datalabels: {
			color: 'white'
		},
    },
    scales: {
        x: {
            grid: {
              display: false
            }            
        },
        
        y: {
            display: false,
            grid: {
              display: true
            }            
        }
    }
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
const dataLine = {
  labels: ['Meter 1', 'Meter 2', 'Meter 3','Meter 4', 'Meter 5', 'Meter 6','Meter 7', 'Meter 8', 'Meter 9','Meter 10', 'Meter 11', 'Meter 12'],
  datasets: [
    {
    label: 'Water Consumption (m³)',
    data: [20, 25, 40, 25, 40, 50, 10, 28, 15, 6, 6, 5],
    backgroundColor: "#00BDAA",
    borderColor: "#00BDAA"
    }]
};

// config 
const configLine = {
  type: 'bar',
  data: dataLine,
  options: {
  	maintainAspectRatio:false,
    plugins: {
		legend: {
		    display: true,
			position: "top"	      
		},
		datalabels: {
			color: 'white'
		},
        annotation: {
          annotations: {
            line1: {
              type: 'line',
              yMin: 110,
              yMax: 110,
              borderColor: 'rgb(255, 99, 132)',
              borderWidth: 2,
              label: {
                backgroundColor: 'red',
                content: 'Main Meter ',
                display: true,
              }              
            }
          },
        },		
    },
    tension: 0.4,
    scales: {
        y: {
            title: {
                display: true,
                text: 'Water Consumption (m³)',
                font: {
                    size: 13,
                    weight: 'bold'
                }
            }            
        },
        x: {
            barThickness: 1,
            title: {
                display: true,
                text: 'Water Meters',
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
