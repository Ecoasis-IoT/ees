<?php

require "auth/config.php";

$email = $_GET['Q91Sx6YvS17KZdeS7ypKJEHSASwfbF'];

$query = "SELECT * FROM `tbl_user` WHERE `email` = '$email';";
$result = mysqli_query($admin_link, $query);
$num_rows = mysqli_num_rows($result);

if($num_rows == 0){
    header('Location: https://ees.ecoasisenergy.com');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<?php include("global/head_links.php"); ?>
<link rel="shortcut icon" type="image/x-icon" href="core/assets/images/logo_icon.png" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- MAIN CSS -->
<link rel="stylesheet" href="core/assets/css/main.css">

<style>

body {
    background: url("core/assets/images/homepage-banner.jpg") no-repeat fixed center;
}

#eco-logo {
    width:200px;
    height:auto;    
    margin: auto;
    display: block;
}

.header p {
    text-align: center;    
}

.btn {
    font-size: 20px;
    width: 100%;
}

</style>

</head>

<body data-theme="theme-cyan">
	<!-- WRAPPER -->
	<div id="wrapper">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle auth-main">
				<div class="auth-box">
					<div class="card">
                        <img src="core/assets/images/ecoasis_logo.jpg" id="eco-logo" alt="" style=""/>
                        <div class="header">
                            <p class="lead">Reset your Password</p>
                        </div>
                        <div class="body">
                            <form class="form-auth-small" method="POST">
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="password" placeholder="Password">
                                </div>  
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="password_con" placeholder="Confirm Password">
                                </div>
                                <input type="button" class="btn btn-primary btn-lg btn-block" onclick="auth()" value = "Reset">
                            </form>
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
	<!-- END WRAPPER -->
	
<script>
    
    function auth(){
        
        $.ajax({
            type: "POST",
            url: "auth/password_reset.php",
            data: {
                "password1": document.getElementById('password').value,
                "password2": document.getElementById('password_con').value,
                "email": '<?php echo $email ?>'
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                if(data.statusCode == "Err1") {
                   alert('Passwords do not match!');
                }
                else if(data.statusCode == "Err2") {
                   alert('Error resetting your Password! Please contact your administrator.');
               }
               else if (data.statusCode == "ok"){
                   window.location.replace("login.php");
               }
            }
            });
    
    }
    
</script>

</body>
</html>
