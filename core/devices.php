<?php

    include("scripts/auth.php");

    
    //Get site id
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

<link rel="stylesheet" href="assets/css/dataTables.min.css">

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Devices</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Device Management</li>
                            <li class="breadcrumb-item active">Devices</li>
                        </ul>
                    </div>            
                </div>
            </div>
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;">Device List - <?php echo $site_name ?></h2> 
                            <!--<a href="add-energy-meter.php" class="btn btn-primary mb-2" style="float: right;"><i class="fa fa-plus"></i> Add Device</a>     -->
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="tbl_devices" class="table table-bordered table-striped table-hover dataTable">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>Device Type</th>
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


<script>
    
    //get all site meters
    $(function sites_meters(){
    
        $.ajax({
            type: "POST",
            url: "scripts/get_site_meters.php",
            data: {
                "site_db": '<?php echo $site_db;?>'
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                console.log(data);

                for(var i = 0; i < data.length; i++){
                    
                    var row = "<tr><td>" + data[i].meter_name +"</td><td>"+ data[i].device_type +"</td></tr>";
                    
                    $('#tbl_devices tbody').append(row);
                    
                    
                }
                
            }
            });
    });
    
</script>









<!-- Javascript -->
<script src="assets/bundles/libscripts.bundle.js"></script>    
<script src="assets/bundles/vendorscripts.bundle.js"></script>

<script src="assets/bundles/datatablescripts.bundle.js"></script>

<script src="assets/bundles/mainscripts.bundle.js"></script>
<script src="assets/js/pages/tables/jquery-datatable.js"></script>

</body>
</html>
