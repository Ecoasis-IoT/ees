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
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Add User</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>                            
                            <li class="breadcrumb-item">Users</li>
                            <li class="breadcrumb-item active">Add User</li>
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
                                <table class="table table-striped table-bordered table-hover">                       
                                    <tbody>
                                        <tr>
                                            <td colspan="2"><b>Enter Email Address of New User</b></td>
                                        </tr>                                    
                                        <tr>
                                            <td>Email Address</td>
                                            <td>
                                                <input style="width:30vw" type="email" class="editbox" name="new_email" id="new_email" placeholder="Email Address">
                                            </td>
                                        </tr>                                    
                                                                                                                   
                                    </tbody>
                                </table>
                                
                                <a class="btn btn-outline-dark" href="user-management">Discard</a>
                                <input class="btn btn-primary" id="btn-add-user" type="button" value="Add User" onclick="send_email(this)">

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



<script>

function send_email(btn){
    EES.btnLoad(btn, 'Sending…');
    $.ajax({
        type: "POST",
        url: "scripts/send_new_user_email",
        data: {
            "csrf_token": "<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>",
            "old_user": "<?php echo htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>",
            "email": document.getElementById('new_email').value
        },
        success: function(data) {
            EES.btnReset(btn);
            if (data.statusCode == "Err") {
                EES.alert('Failed to Add User!', 'error');
            } else if (data.statusCode == "ok") {
                EES.alert('An email has been sent to the user!', 'success');
                setTimeout(function(){ window.location.replace("user-management"); }, 1500);
            }
        },
        error: function() {
            EES.btnReset(btn);
            EES.alert('A network error occurred. Please try again.', 'error');
        }
    });
}


</script>








</body>
</html>
