<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="../assets/images/logo_icon.png">

<link rel="stylesheet" href="../assets/css/dataTables.min.css">

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/main.css">

<style>

table.table-bordered.dataTable thead tr:first-child th {
    color: white;
}

table.dataTable thead>tr>th.sorting:before {
    color:black;
}

.dt-button {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;        
}    

.dataTables_wrapper .dataTables_paginate .paginate_button {
     padding: 0; 
     margin-left: 0;
     
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
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;">Device List</h2> 
                            <a href="add-energy-meter.php" class="btn btn-primary mb-2" style="float: right;"><i class="fa fa-plus"></i> Add Device</a>     
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="example3" class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom export">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>Serial No.</th>
                                            <th>Site</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Energy Meter 1</td>
                                            <td>231241323123</td>
                                            <td>Mall of Mauritius @ Bagatelle</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr>  
                                        <tr>
                                            <td>Energy Meter 2</td>
                                            <td>231241323123</td>
                                            <td>Phoenix Mall</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr>    
                                        <tr>
                                            <td>Energy Meter 3</td>
                                            <td>231241323123</td>
                                            <td>Trou aux Biches Hotel</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr> 
                                        <tr>
                                            <td>Energy Meter 4</td>
                                            <td>231241323123</td>
                                            <td>Paradis Golf Resort & Spa</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr> 
                                        <tr>
                                            <td>Energy Meter 5</td>
                                            <td>231241323123</td>
                                            <td>Le Mauricia Hotel</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr> 
                                        <tr>
                                            <td>Energy Meter 6</td>
                                            <td>231241323123</td>
                                            <td>Deco City Bagatelle</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr>
                                        <tr>
                                            <td>Energy Meter 7</td>
                                            <td>231241323123</td>
                                            <td>Kendra Commercial Centre</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr> 
                                        <tr>
                                            <td>Energy Meter 8</td>
                                            <td>231241323123</td>
                                            <td>Victoria Hotel</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr>
                                        <tr>
                                            <td>Energy Meter 9</td>
                                            <td>231241323123</td>
                                            <td>Shandrani Resort & Spa</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
                                        </tr>  
                                        <tr>
                                            <td>Energy Meter 10</td>
                                            <td>231241323123</td>
                                            <td>ENL HOUSE</td> 
                                            <td><a href="edit-energy-meter.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>  
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

<!-- Javascript -->
<script src="../assets/bundles/libscripts.bundle.js"></script>    
<script src="../assets/bundles/vendorscripts.bundle.js"></script>

<script src="../assets/bundles/datatablescripts.bundle.js"></script>

<script src="../assets/bundles/mainscripts.bundle.js"></script>
<script src="../assets/js/pages/tables/jquery-datatable.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
</body>
</html>
