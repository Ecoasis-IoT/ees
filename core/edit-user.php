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

<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">

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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Edit User</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Users</li>
                            <li class="breadcrumb-item active">Edit User</li>
                        </ul>
                    </div>            
                </div>
            </div>
            
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2>Users</h2>                       
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover editsitetbl">                       
                                    <tbody>
                                        <tr>
                                            <td colspan="2"><b>Profile</b></td>
                                        </tr>                                    
                                        <tr>
                                            <td>Username</td>
                                            <td>
                                                <input style="width:30vw" type="text" class="editbox" name="username" id="userName" value="User One">
                                            </td>
                                        </tr>                                    
                                        <tr>
                                            <td>First Name</td>
                                            <td>
                                                <input style="width:30vw" type="text" class="editbox" name="userfname" id="userfname" value="User">
                                            </td>
                                        </tr>                                      
                                        <tr>
                                            <td>Last Name</td>
                                            <td>
                                                <input style="width:30vw" type="text" class="editbox" name="userlname" id="userlname" value="One">
                                            </td>
                                        </tr>                                                                        
                                        <tr>
                                            <td colspan="2"><b>User Group</b></td>
                                        </tr>
                                        <tr>
                                            <td>User Group</td>
                                            <td>
                                                <select style="width:20vw" name="" id="choose_group">
                                                    <option value="1">Admin</option>
                                                    <option value="2" selected="">Manager</option>
                                                    <option value="3">Operator</option>
                                                </select>
                                            </td>
                                        </tr>                                       
                                        <tr>
                                            <td colspan="2"><b>Contact</b></td>
                                        </tr>                                        
                                        <tr>
                                            <td>Email</td>
                                            <td>
                                                <input style="width:30vw" type="text" class="editbox" name="email" id="email" value="user@one.com">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Phone Number</td>
                                            <td>
                                                <input style="width:30vw" type="text" class="editbox" name="phone" id="phone" value="1111111">
                                            </td>
                                        </tr>                                        
                                        <tr>
                                            <td colspan="2"><b>Status</b></td>
                                        </tr>                                        
                                        <tr>
                                            <td>Status</td>
                                            <td>
                                            <select style="width:20vw" name="status" id="choose_status">
                                                <option value="1" selected="">Enabled</option>
                                                <option value="0">Disabled</option>                                            
                                            </select>
                                            </td>
                                        </tr>                                                                            
                                    </tbody>
                                </table>
                                
                                <a class="btn btn-outline-dark" href="user-management">Discard</a>
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
