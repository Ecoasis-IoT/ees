<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/session_cookie_config.php';

if (session_status() === PHP_SESSION_NONE) {
    applySessionCookieConfig();
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
    <link rel="stylesheet" href="assets/css/pages/login.css">
</head>
<body data-theme="theme-cyan">
    <div id="wrapper">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="card">
                        <img src="assets/images/ecoasis_logo.jpg" id="eco-logo" alt="Ecoasis Logo">
                        <div class="header">
                            <p class="lead">Login to your account</p>
                        </div>
                        <div class="body">
                            <form class="form-auth-small" method="POST" onsubmit="return false;">
                                <div class="form-group mb-2">
                                    <input type="text" class="form-control" id="signin-user" placeholder="Username" autocomplete="username">
                                </div>
                                <div class="form-group mb-2 password-wrapper">
                                    <input type="password" class="form-control" id="signin-password" placeholder="Password" autocomplete="current-password">
                                    <button type="button" id="toggle-password" class="password-toggle" tabindex="-1" aria-label="Toggle password visibility">
                                        <i class="fa fa-eye" id="toggle-password-icon"></i>
                                    </button>
                                </div>
                                <div id="login-error" class="alert alert-danger" role="alert" style="display:none;"></div>
                                <input type="submit" class="btn btn-primary btn-lg btn-block" onclick="auth(); return false;" value="Sign In">
                                <div class="bottom">
                                    <span class="helper-text m-b-10">
                                        <i class="fa fa-lock"></i>
                                        <a href="forgot-password.php">Forgot Password?</a>
                                    </span>
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
    <script src="assets/js/pages/login.js"></script>
</body>
</html>
