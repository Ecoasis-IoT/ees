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
<link rel="stylesheet" href="../assets/css/custom.css">
<link rel="stylesheet" href="../assets/css/main.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js"></script>

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
                            <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Comparison</h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                                <li class="breadcrumb-item active">Reports / Comparison</li>
                            </ul>
                        </div>            
                    </div>
                </div>           

                <div class="row clearfix g-3 mb-3">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2 style="display:inline-block;">Daily Meters Comparison Report<small>Main Meters vs Total Sub-Meters</small></h2> 
                            </div>
                            <div class="body">                           
                                <div class="table-responsive">
                                    <table id="example2" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export">
                                        <thead>
                                            <tr>
                                                <th colspan="5" style="text-align: center;">Consumption (m<sup>3</sup>)</th>
                                            </tr>
                                            <tr>
                                                <th>Date</th>
                                                <th>Location</th>
                                                <th>Main Meter</th>
                                                <th>Total Sub Meters</th>
                                                <th>Difference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 1</td>
                                                <td>145</td>
                                                <td>142</td>
                                                <td>3</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>                                                
                                                <td>Village 2</td>
                                                <td>147</td>
                                                <td>145</td>
                                                <td>2</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 3</td>
                                                <td>125</td>
                                                <td>121</td>
                                                <td>4</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 4</td>
                                                <td>232</td>
                                                <td>232.1</td>
                                                <td>0.1</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 5</td>
                                                <td>322</td>
                                                <td>321</td>
                                                <td>1</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 6</td>
                                                <td>544</td>
                                                <td>544.6</td>
                                                <td>0.6</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 7</td>
                                                <td>445</td>
                                                <td>442</td>
                                                <td>3</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 8</td>
                                                <td>109.5</td>
                                                <td>109.8</td>
                                                <td>0.3</td>
                                            </tr>
                                            <tr>
                                                <td>08 March 2024</td>
                                                <td>Village 9</td>
                                                <td>55</td>
                                                <td>56.5</td>
                                                <td>1.5</td>
                                            </tr>                                            
                                        </tbody>
                                    </table>
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
