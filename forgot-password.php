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
                            <p class="lead">Recover my password</p>
                        </div>
                        <div class="body">
                            <p>Please enter your email address below to receive instructions for resetting password.</p>
                            <form class="form-auth-small">
                                <div class="form-group mb-2">                                    
                                    <input type="email" class="form-control" id="email" placeholder="Email">
                                    <br>
                                    <p class="helper-text">If the email is found in our database, you will receive an email with a link to reset your password.</p>
                                </div>
                                <button type="button" class="btn btn-primary btn-lg btn-block" onclick="auth()">Get Link to Reset Password</button>
                                <div class="bottom">
                                    <span class="helper-text">Know your password? <a href="login.php">Login</a></span>
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
            url: "auth/password_reset_email.php",
            data: {
                "email": document.getElementById('email').value
            },
            success: function(dataResult) {
                var data = JSON.parse(dataResult);
                // console.log(data.statusCode);
                
                if (data.statusCode == "Err"){
                   window.location.replace("Internal Error! Contact your Administrator.");
                }
                else if(data.statusCode == "ok"){
                    alert("An email with reset instructions has been sent!");
                }
            }
        });
    
    }
    
     
     </script>
	
	
</body>
</html>
