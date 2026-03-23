<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
if (isset($_SESSION['id']) && (time() - ($_SESSION['created'] ?? 0)) < $session_lifetime) {
    header('Location: dashboard.php');
    exit;
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
    <link rel="stylesheet" href="assets/css/pages/register.css">
</head>
<body data-theme="theme-cyan">
    <div id="wrapper">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="card">
                        <img src="assets/images/ecoasis_logo.jpg" id="eco-logo" alt="Ecoasis Logo">
                        <div class="header">
                            <p class="lead">Create your account</p>
                        </div>
                        <div class="body">
                            <form class="form-auth-small" method="POST" onsubmit="return false;">
                                <div class="form-group mb-2">
                                    <input type="text" class="form-control" id="uname" placeholder="Username" autocomplete="username">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="email" class="form-control" id="email" placeholder="Email" autocomplete="email">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="text" class="form-control" id="fname" placeholder="First Name" autocomplete="given-name">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="text" class="form-control" id="lname" placeholder="Last Name" autocomplete="family-name">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="password" placeholder="Password" autocomplete="new-password">
                                </div>
                                <input type="button" class="btn btn-primary btn-lg btn-block" onclick="auth()" value="Register">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="assets/js/pages/register.js"></script>
</body>
</html>
