<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/session_cookie_config.php';

if (!defined('REGISTRATION_LINK_ENABLED') || !REGISTRATION_LINK_ENABLED) {
    http_response_code(404);
    include __DIR__ . '/error-404.php';
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    applySessionCookieConfig();
    session_start();
}
$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
if (isset($_SESSION['id']) && (time() - ($_SESSION['last_activity'] ?? 0)) < $session_lifetime) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/common/csrf.php';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Account | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">
</head>
<body>

<div class="ees-auth-body">

    <!-- Left brand panel -->
    <div class="ees-auth-panel-left">
        <div class="ees-auth-brand">
            <img src="assets/images/logo_icon.png" alt="Ecoasis">
            <h1>Ecoasis EES</h1>
            <p>Ecoasis Energy System — monitor and manage your solar installations in real time.</p>
        </div>
        <div class="ees-auth-features">
            <div class="ees-auth-feature">
                <i class="fa fa-bolt"></i>
                <span>Real-time energy production monitoring</span>
            </div>
            <div class="ees-auth-feature">
                <i class="fa fa-line-chart"></i>
                <span>Multi-site analytics and reporting</span>
            </div>
            <div class="ees-auth-feature">
                <i class="fa fa-shield"></i>
                <span>Secure, role-based access control</span>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="ees-auth-panel-right">
        <div class="ees-auth-card">

            <div class="ees-auth-card-header">
                <h2>Create Account</h2>
                <p>Fill in the details below to register</p>
            </div>

            <form onsubmit="return false;">
                <div class="form-group">
                    <label class="form-label" for="uname">Username</label>
                    <input type="text" class="form-control" id="uname"
                           placeholder="Choose a username" autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email"
                           placeholder="Enter your email" autocomplete="email">
                </div>
                <div style="display:flex;gap:12px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="fname">First Name</label>
                        <input type="text" class="form-control" id="fname"
                               placeholder="First name" autocomplete="given-name">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="lname">Last Name</label>
                        <input type="text" class="form-control" id="lname"
                               placeholder="Last name" autocomplete="family-name">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password"
                           placeholder="Create a strong password" autocomplete="new-password">
                </div>

                <div style="margin-top:24px;">
                    <input type="button" class="btn btn-primary btn-lg btn-block"
                           onclick="auth()" value="Create Account">
                </div>

                <div class="ees-auth-footer-link" style="margin-top:16px;">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </form>

        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="assets/js/ees-utils.js"></script>
<script src="assets/js/pages/register.js"></script>
</body>
</html>
