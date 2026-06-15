<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/audit_logging.php';

$csrf_token = generateCSRFToken();
$expired    = isset($_GET['expired']) && $_GET['expired'] === '1';

ees_audit_log_public_page_view('forgot-password', [
    'expired_redirect' => $expired,
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password | EES</title>
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
                <i class="fa fa-lock"></i>
                <span>Password reset via secure email link</span>
            </div>
            <div class="ees-auth-feature">
                <i class="fa fa-shield"></i>
                <span>Links expire after 1 hour for security</span>
            </div>
            <div class="ees-auth-feature">
                <i class="fa fa-envelope"></i>
                <span>Check your inbox and spam folder</span>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="ees-auth-panel-right">
        <div class="ees-auth-card">

            <div class="ees-auth-card-header">
                <h2>Forgot Password?</h2>
                <p>Enter your email address and we'll send you a reset link.</p>
            </div>

            <?php if ($expired): ?>
            <div class="alert alert-warning" style="margin-bottom:18px;">
                <i class="fa fa-clock-o"></i>&nbsp;
                Your reset link has expired. Please request a new one.
            </div>
            <?php endif; ?>

            <form onsubmit="return false;">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email"
                           placeholder="Enter your email" autocomplete="email">
                    <small style="font-size:12px;color:var(--ees-text-muted);margin-top:5px;display:block;">
                        If the email exists in our system, a reset link will be sent.
                    </small>
                </div>

                <div style="margin-top:24px;">
                    <button type="button" class="btn btn-primary btn-lg btn-block" onclick="auth()">
                        Send Reset Link
                    </button>
                </div>

                <div class="ees-auth-footer-link" style="margin-top:16px;">
                    <a href="login"><i class="fa fa-arrow-left" style="margin-right:4px;"></i> Back to Sign In</a>
                </div>
            </form>

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
