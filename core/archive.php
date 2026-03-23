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



<!-- JQuery AJAX -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/themes/base/jquery-ui.min.css" integrity="sha512-8PjjnSP8Bw/WNPxF6wkklW6qlQJdWJc/3w/ZQPvZ/1bjVDkrrSqLe9mfPYrMxtnzsXFPc434+u4FHLnLjXTSsg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" integrity="sha512-Ww1y9OuQ2kehgVWSD/3nhgfrb424O3802QYP/A5gPXoM4+rRjiKrjHdGxQKrMGQykmsJ/86oGdHszfcVgUr4hA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!-- VENDOR CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/morris.min.css" />

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

<!--chartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<style>

.header {
    padding-bottom: 0px !important;
}

.card .body {
    /*padding-top: 0px !important;*/
}

.submit {
    margin-top: 6px;
}

.btn {
    font-size: 16px;
}

.dt-button {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
}

table.table-bordered.dataTable thead tr:first-child th {
    color: white;
}

input {
    padding: 5px;
    font-size: 14px;
    border-radius: 1px;
    width: auto;
    border: 1px solid #767676;
}

.btn-chart {
    display: flex;
    justify-content: flex-end;
    margin-right: 20px;
    margin-bottom: 20px;
    margin-top: 10px;
    align-items:center;
}    

.clear-btn {
    margin-right: 15px;
}

.select {
    margin-top:1px;
    padding-bottom: 10px !important;
}

.alarms-sort {
    padding: 8px;
    width: 218px;
    font-size: 14px;
    border-radius: 1px;
    margin-left: 20px;
    margin-bottom: 20px;
}

.submit {
    margin-bottom: 15px;
    margin-left: 20px;
}

.card-box {
    height: 101px !important;
    width: 100% !important;
    border: 1px solid black !important;
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

    <div id="main-content">
        <div class="container-fluid">
            <div class="block-header">
                <div class="row g-3">
                    <div class="col-lg-5 col-md-8 col-sm-12">                        
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left" onclick="fullwidth()"></i></a>Archive</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Archive</li>
                        </ul>
                    </div>            
                </div>
            </div>            

            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="row mb-3">
                            <div class="col-lg-3 col-md-3">
                                <div class="header">
                                    <h6>CHOOSE SITE</h6> 
                                </div>           

                                <select name="sort" id="site" class="alarms-sort select" onchange="validate_date()">
                                    <option hidden>Choose a site</option>
                                </select>    
                            </div>  
                            
                            <div class="col-lg-3 col-xs-12 col-sm-12">
                                <div class="header">
                                    <h6>START DATE:</h6>                            
                                </div>
                                <input type="date" id="startDate" name="startdate" class="alarms-sort" required>
                            </div>
                            
                            <div class="col-lg-3 col-xs-12 col-sm-12">
                                <div class="header">
                                    <h6>END DATE:</h6>                            
                                </div>
                                <input type="date" id="endDate" name="enddate" class="alarms-sort" required>           
                            </div>
                            
                            <div class="col-lg-2 col-12 col-sm-2">    
                                <div class="header">
                                    <h2 style="display:inline-block;color:transparent;"></h2> 
                                </div>           
    
                                <button class="btn btn-primary submit" onclick="query()">Submit</button>         
                            </div>  
                                                
                        </div>
                    </div>
                </div>
            </div>    
            
            <div class="row clearfix g-3 mb-3 custom_hide_card">
                
                <div class="col-lg-12">
                    <div class="card">
                        <div class="body">
                            
                            <div class="row text-center justify-content-center">
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="card text-center mb-3 card-box">
                                        <div class="body">
                                            <h3 id="total_prod"></h3>
                                            <span class="text-muted value-text">Total Production</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="card text-center mb-3 card-box">
                                        <div class="body">
                                            <h3 id="total_insolation"></h3>
                                            <span class="text-muted value-text">Total Insolation</span>
                                        </div>
                                    </div>
                                </div>    
                            </div>
                            
                                                                 
                            <div class="btn-chart">
                                <button class="btn btn-primary clear-btn" id="zoom_reset" onclick="resetZoomBtn()">Reset Zoom</button>
                                <i class="fa fa-download" onclick="downloadCustom()"  title="Download"></i>
                            </div>  
    
                            <div class="chartBox">
                                <canvas id="archive_chart"></canvas> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row clearfix g-3 mb-3 custom_hide_card">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;padding-bottom:20px;">Archived Data Table</h2> 
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="tbl_archive" class="table table-bordered table-striped table-hover dataTable">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Site</th>
                                            <th>Production (kWh)</th>
                                            <th>Insolation (kWh/m<sup>2</sup>)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
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

<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/morrisscripts.bundle.js"></script>
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<script type="text/javascript" src="assets/js/pages/tables/jquery-datatable.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/pages/ui/dialogs.js"></script>

<script>

$(function hideCards(){
    $(".custom_hide_card").css('display','none');
});


// Setup - add a text input to each footer cell
$('#tbl_archive thead tr')
    .clone(true)
    .addClass('filters')
    .appendTo('#tbl_archive thead');

var archive_table = $('#tbl_archive').DataTable({
    ordering: false,
    autoWidth: false,
    columnDefs: [{ width: '10%', targets: 0 },{ width: '13%', targets: 1 },{ width: '11%', targets: 2 },{ width: '12%', targets: 3 }],   
    orderCellsTop: true,
    fixedHeader: true,
    dom: 'Bfrtip',
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
    ],  
    initComplete: function () {
        this.api()
            .columns()
            .every(function () {
                let column = this;
                let title = column.header().textContent;
 
                if(title != 'Site' && title != 'Production (kWh)' && title != 'Insolation (kWh/m2)'){
                    // Create input element and add event listener
                    $('<input type="text" placeholder="Search ' + title + '" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change clear', function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });

                    }
            });
    }           
});




$(function sites_name(){

    $.ajax({
        type: "POST",
        url: "scripts/get_all_sites.php",
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            // console.log(data.statusCode);
            
            // console.log(data.data);
            
            let sites = data.data;
            
            for(var i = 0; i < sites.length; i++){
                
                let option = "<option value = '"+ sites[i][0] +"'>"+ sites[i][1] +"</option>";
                
                $('#site').append(option);
            }
        }
    });
});


function validate_date(){
    
    let site_id = document.getElementById("site").value;
    
    
    $.ajax({
        type: "POST",
        url: "scripts/get_archive_date_bounds.php",
        data: {
            "site" : site_id
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            // console.log(data.statusCode);
            
            // console.log(data.data);
            
            let min_date = data.min;
            let max_date = data.max;
            
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";
            
            document.getElementById("startDate").setAttribute("min", min_date);
            document.getElementById("startDate").setAttribute("max", max_date);
            
            document.getElementById("endDate").setAttribute("min", min_date); 
            document.getElementById("endDate").setAttribute("max", max_date);
            
        }
    });
    
}

var custom_archive;

function render_chart(dates, prod_dataset, ins_dataset,){

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
    
    const dataArchive = {
        labels: dates,
        datasets: [
                {
                    type: 'line',
                    label: 'Insolation (kWh/m2)',
                    yAxisID: 'B',
                    data: ins_dataset,
                    // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
                    fill: false,
                    backgroundColor: '#F2BB46',
                    borderColor: '#F2BB46',
                    title: 'Irradiance (W/2)',
                    tension: 0.4,
                    pointRadius: 1,
                },      
                {
                    type: 'bar',
                    label: 'Production (kWh)',
                    yAxisID: 'C',
                    data: prod_dataset,
                    // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                    backgroundColor: '#CA5952',
                    borderColor: '#CA5952',
                    barThickness:30,
                }
        ]
        
    }
    
    
    
    const configArchive = {
        data: dataArchive,
      options: {
        enabled: true,
        scales: {       
            A: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Production (kWh)',
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
                    text: 'Insolation (kWh/m2)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }                
                },
                grid:{
                    display:false
                }
            },
            x: {
              title: {
                display: true,
                text: 'Date/Time',
                font: {
                    size: 13,
                    weight:'bold'
                }            
              }
            },              
            },
            plugins: {
                zoom: zoomOptions,
                legend: {
                    position: 'bottom',
                    display: true,
                },
                tooltip: {
                    enabled: true,
                },
            },
            animation: false,            
        },
        plugins: [white_back],
        maintainAspectRatio:true,
        responsive: true,
        // interaction: {
        //     mode: 'index'
        // },
      
    };
    
    let chartStatus = Chart.getChart("archive_chart"); // <canvas> id
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    
    // render init block
    custom_archive = new Chart(
      document.getElementById('archive_chart'),
      configArchive
    );

}

function query(){
    
    let site = document.getElementById("site").value;
    
    let start_date = new Date(document.getElementById("startDate").value).toISOString().split('T')[0];
    let end_date = new Date(document.getElementById("endDate").value).toISOString().split('T')[0];
    
    
    
    $.ajax({
        type: "POST",
        url: "scripts/get_archive_data.php",
        data: {
            "site" : site,
            "start_date": start_date,
            "end_date": end_date
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            // console.log(data.statusCode);
            
            // console.log(data);
            
            let site_name = data.site_name;
            let archive = data.archive;
            
            let tempDate, date, prod, insolation;
            
            let date_dataset = [];
            let prod_dataset = [];
            let ins_dataset = [];
            let total_prod = 0, total_ins = 0;
            
            archive_table.rows().remove().draw();
            if(archive.length > 0){
                for(let i = 0; i < archive.length; i++){
                    
                    tempDate = new Date(archive[i].date);
                    
                    date = [tempDate.getDate(), tempDate.getMonth() + 1, tempDate.getFullYear()].join('/');
                    
                    prod = archive[i].production;
                    insolation = archive[i].insolation;
                    
                    archive_table.row.add([date, site_name, prod, insolation]).draw();
                    
                    date_dataset.push(archive[i].date);
                    prod_dataset.push(prod);
                    ins_dataset.push(insolation);
                    
                    total_prod += parseFloat(prod);
                    total_ins += parseFloat(insolation);
                    
                }
            }
            else{
                alert("No data for this date!");
            }
            
            if(total_prod < 1000){
                document.getElementById("total_prod").innerHTML = total_prod.toFixed(2) + " kWh";
            }
            else{
                document.getElementById("total_prod").innerHTML = (total_prod/1000).toFixed(2) + " MWh";
            }
            
            
            document.getElementById("total_insolation").innerHTML = total_ins.toFixed(2) + " kWh/m<sup>2</sup>";
            
            render_chart(date_dataset, prod_dataset, ins_dataset);
            $(".custom_hide_card").css('display','block');
        }
    });
    
    
}

function resetZoomBtn(){
    custom_archive.resetZoom();
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


//Archive chart
    
</script>

<script>
function downloadCustom() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('archive_chart');
    imageLink.download = 'archive_chart.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}    
</script>

</body>
</html>