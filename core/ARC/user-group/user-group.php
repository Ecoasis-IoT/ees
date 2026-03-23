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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> User Group</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard/dashboard.php"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">User Management</li>
                            <li class="breadcrumb-item active">User Group</li>
                        </ul>
                    </div>            
                </div>
            </div>
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="display:inline-block;">User Group</h2> 
                            <a href="add-user-group.php" class="btn btn-primary mb-2" style="float: right;"><i class="fa fa-plus"></i> Add User Group</a>                                               
                        </div>
                        <div class="body">                           
                            <div class="table-responsive tbl_alerts">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable table-custom">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Manager</td>
                                            <td><a href="edit-group.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>
                                        </tr>  
                                        <tr>
                                            <td>Admin</td>
                                            <td><a href="edit-group.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>
                                        </tr>    
                                        <tr>
                                            <td>Operator</td>
                                            <td><a href="edit-group.php" class="btn btn-primary"><i class="icon-pencil" aria-hidden="true"></i> Edit</a></td>
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
</body>
</html>
