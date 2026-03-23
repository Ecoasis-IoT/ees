<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';
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

<link rel="stylesheet" href="assets/css/dataTables.min.css">

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">

<style>

.dt-button {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;        
}    

.card .body {
    padding: 15px;    
}

.connected {
    color:green;
    text-align: center;
    border:1px solid green;
    padding:5px;
    margin:10px;
    border-radius:5px;
    background:transparent;
    width:40%;
}

.disconnected {
    color:red;
    margin: 10px;
    text-align: center;
    border:1px solid red;
    padding:5px;
    border-radius:5px;
    background:transparent;
    width:40%;
}

#tbl_site thead th, #tbl_site tr {
    text-align: center;
    /*justify-content: center;*/
    /*display: flex;*/
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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Site</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Device Management</li>
                            <li class="breadcrumb-item active">Site</li>
                        </ul>
                    </div>            
                </div>
            </div>
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;">Site List</h2> 
                            <!--<a href="add-site.php" class="btn btn-primary mb-2" style="float: right;"><i class="fa fa-plus"></i> Add Site</a>     -->
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table id="tbl_site" class="table table-bordered table-striped table-hover dataTable">
                                    <thead>
                                        <tr>
                                            <th>Site</th>
                                            <th>Site Capacity (kWp)</th>
                                            <th>Gateway Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <!--<tr>-->
                                        <!--    <td>Phoenix Mall</td> -->
                                        <!--    <td>720</td>-->
                                        <!--    <td><button class="disconnected"></button></td>-->
                                        <!--    <td><a href="edit-site.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> View Meters</a></td>  -->
                                        <!--</tr>-->
                                        
                                        <!--<tr>-->
                                        <!--    <td>Phoenix Mall</td> -->
                                        <!--    <td>737</td>-->
                                        <!--    <td><button class="connected"></button></td>-->
                                        <!--    <td><a href="edit-site.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> View Meters</a></td>  -->
                                        <!--</tr>                                        -->
                                        
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


    
    //get_all_sites
    $(function sites_name(){
    
        $.ajax({
            type: "POST",
            url: "scripts/get_all_sites.php",
            dataType: 'json',
            data: {
                
            },
            success: function(data) {
                var sites = data.data || [];

                for (let i = 0; i < sites.length; i++) {
                    var s       = sites[i];
                    var gateway = parseInt(s.gateway_status) === 1
                        ? "<p class='connected'>ONLINE</p>"
                        : "<p class='disconnected'>OFFLINE</p>";
                    var cap = s.capacity ? s.capacity + ' kWp' : '—';

                    var row = "<tr>" +
                        "<td>" + s.site_name + "</td>" +
                        "<td>" + cap + "</td>" +
                        "<td class='justify-content-center d-flex'>" + gateway + "</td>" +
                        "<td><a href='devices.php?site=" + s.id + "' class='btn btn-primary'>" +
                            "<i class='icon-energy' aria-hidden='true'></i> View Devices</a></td>" +
                        "</tr>";

                    $('#tbl_site tbody').append(row);
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
<!--<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>-->
<!--<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>-->
<!--<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>-->
</body>
</html>
