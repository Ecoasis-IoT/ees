<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | EES</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body { background: #f0f3f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .error-card { background: #fff; border-radius: 8px; padding: 50px 40px; text-align: center; max-width: 480px; width: 100%; box-shadow: 0 2px 20px rgba(0,0,0,.08); }
        .error-code { font-size: 90px; font-weight: 700; color: #dc3545; line-height: 1; margin-bottom: 10px; }
        .error-title { font-size: 22px; font-weight: 600; color: #333; margin-bottom: 12px; }
        .error-msg { color: #888; margin-bottom: 30px; font-size: 15px; line-height: 1.6; }
        .btn-home { display: inline-block; padding: 10px 28px; background: #26a69a; color: #fff; border-radius: 4px; text-decoration: none; font-size: 15px; transition: background .2s; }
        .btn-home:hover { background: #1e857a; color: #fff; }
        .logo { margin-bottom: 30px; }
        .logo img { height: 45px; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="logo">
            <img src="assets/images/logo_icon.png" alt="EES Logo">
        </div>
        <div class="error-code">500</div>
        <h2 class="error-title">Internal Server Error</h2>
        <p class="error-msg">Something went wrong on our end. Our team has been notified. Please try again in a few moments.</p>
        <a href="dashboard.php" class="btn-home"><i class="fa fa-home"></i>&nbsp; Go to Dashboard</a>
    </div>
</body>
</html>
