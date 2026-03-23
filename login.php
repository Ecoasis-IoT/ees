<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

<link rel="shortcut icon" type="image/x-icon" href="core/assets/images/logo_icon.png" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- MAIN CSS -->
<link rel="stylesheet" href="core/assets/css/main.css">

<style>

body {
    background: url("core/assets/images/login-img.jpeg") no-repeat fixed center;
    background-size: cover;
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
                            <p class="lead">Login to your account</p>
                        </div>
                        <div class="body">
                            <form class="form-auth-small" method="POST">
                                <div class="form-group mb-2">
                                    <input type="username" class="form-control" id="signin-user" placeholder="Username">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="signin-password" placeholder="Password">
                                </div>
                                <input type="button" class="btn btn-primary btn-lg btn-block" onclick="auth()" value = "Sign In">
                                <div class="bottom">
                                    <span class="helper-text m-b-10"><i class="fa fa-lock"></i> <a href="forgot-password.php">Forgot Password?</a></span>
                                </div>
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
        url: "auth/userlogin.php",
        data: {
            "username": document.getElementById('signin-user').value,
            "pass": document.getElementById('signin-password').value
        },
        success: function(dataResult) {
            var data = JSON.parse(dataResult);
            // console.log(data.statusCode);
            
            if(data.statusCode == "Err") {
               alert('Incorrect Credentials!');
           }
           else if (data.statusCode == "auth"){
               window.location.replace("core/dashboard.php");
           }
        }
        });
    
    }
    
     
     </script>
	
	
</body>
</html>
