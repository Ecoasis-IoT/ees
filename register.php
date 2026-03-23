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
                            <p class="lead">Create your account</p>
                        </div>
                        <div class="body">
                            <form class="form-auth-small" method="POST">
                                <div class="form-group mb-2">
                                    <input type="username" class="form-control" id="uname" placeholder="Username">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="email" class="form-control" id="email" placeholder="Email">
                                </div>    
                                <div class="form-group mb-2">
                                    <input type="firstname" class="form-control" id="fname" placeholder="First Name">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="lastname" class="form-control" id="lname" placeholder="Last Name">
                                </div>  
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="password" placeholder="Password">
                                </div>
                                <input type="button" class="btn btn-primary btn-lg btn-block" onclick="auth()" value = "Register">
                                <div class="bottom">
                                    <span class="helper-text">Already have an account? <a href="login.php">Login</a></span>
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
            url: "auth/userregister.php",
            data: {
                "username": document.getElementById('uname').value,
                "fname": document.getElementById('fname').value,
                "lname": document.getElementById('lname').value,
                "email": document.getElementById('email').value,
                "password": document.getElementById('password').value
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                if(data.statusCode == "Err") {
                   alert('Error in registering your Account!');
               }
               else if (data.statusCode == "auth"){
                   window.location.replace("login.php");
               }
            }
            });
    
    }
    
</script>

</body>
</html>
