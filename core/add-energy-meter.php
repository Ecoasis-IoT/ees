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

<link rel="stylesheet" href="assets/css/dataTables.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

<style>
input, select {
    padding:5px;
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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Add Energy Meter</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Energy Meters</li>
                            <li class="breadcrumb-item active">Add Energy Meter</li>
                        </ul>
                    </div>            
                </div>
            </div>
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2>Energy Meters</h2>                       
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover editsitetbl">                       
                                    <tbody>
                                        <tr>
                                            <td colspan="2"><b>Device</b></td>
                                        </tr>                                    
                                        <tr>
                                            <td>Energy Meter</td>
                                            <td>
                                                <input style="width:30vw" type="text" class="editbox" name="device" id="deviceId" value="">
                                            </td>
                                        </tr>                                    
                                        <tr>
                                            <td>Serial No.</td>
                                            <td>
                                                <input style="width:30vw" type="numerical" class="editbox" name="serial" id="serialId" value="">
                                            </td>
                                        </tr>                                      
                                        <tr>
                                            <td colspan="2"><b>Site</b></td>
                                        </tr>
                                        <tr>
                                            <td>Site</td>
                                            <td>
                                                <select style="width:20vw" id="choose_group">
                                                    <option value="1">Mall of Mauritius @ Bagatelle</option>
                                                    <option value="2">ENL HOUSE</option>
                                                    <option value="3">Trou aux Biches Hotel</option>
                                                    <option value="3">Paradis Golf Resort & Spa</option>
                                                    <option value="3">Le Mauricia Hotel</option>
                                                    <option value="3">Deco City Bagatelle</option>
                                                    <option value="3">Kendra Commercial Centre</option>
                                                    <option value="3">Victoria Hotel</option>
                                                    <option value="3">Shandrani Resort & Spa</option>
                                                </select>
                                            </td>
                                        </tr>                                       
                                    </tbody>
                                </table>
                                
                                <a class="btn btn-outline-dark" href="energy-meters.php">Discard</a>
                                <input class="btn btn-danger" type="button" value="Delete" onclick="">
                                <input class="btn btn-primary" type="submit" value="Save Changes" onclick="">

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

<script src="assets/bundles/datatablescripts.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/pages/tables/jquery-datatable.js"></script>
</body>
</html>
