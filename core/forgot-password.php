<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/common/csrf.php';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ecoasis - Positive Energies | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/forgot-password.css">
</head>
<body data-theme="theme-cyan">
    <div id="wrapper">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="card">
                        <img src="assets/images/ecoasis_logo.jpg" id="eco-logo" alt="Ecoasis Logo">
                        <div class="header">
                            <p class="lead">Recover my password</p>
                        </div>
                        <div class="body">
                            <p>Please enter your email address below to receive instructions for resetting your password.</p>
                            <form class="form-auth-small" onsubmit="return false;">
                                <div class="form-group mb-2">
                                    <input type="email" class="form-control" id="email" placeholder="Email" autocomplete="email">
                                    <br>
                                    <p class="helper-text">If the email is found in our database, you will receive an email with a reset link.</p>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="assets/js/ees-utils.js"></script>
    <script src="assets/js/pages/forgot-password.js"></script>
</body>
</html>
