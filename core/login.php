<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/session_cookie_config.php';

if (session_status() === PHP_SESSION_NONE) {
    applySessionCookieConfig();
    session_start();
}
$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
if (isset($_SESSION['id']) && (time() - ($_SESSION['created'] ?? 0)) < $session_lifetime) {
    header('Location: ' . ees_url_path('dashboard.php'));
    exit;
}

$twofa_pending = !empty($_SESSION['2fa_pending']) && !empty($_SESSION['2fa_user_id']);

require_once __DIR__ . '/common/csrf.php';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign In | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">
</head>
<body data-2fa-pending="<?= $twofa_pending ? '1' : '0' ?>">

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
                <i class="fa fa-map-marker"></i>
                <span>Geo-mapped site overview</span>
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
                <h2 id="login-title">Welcome back</h2>
                <p id="login-subtitle">Sign in to your account to continue</p>
            </div>

            <div id="login-step-credentials">
            <form onsubmit="return false;">
                <div class="form-group">
                    <label class="form-label" for="signin-user">Username</label>
                    <input type="text" class="form-control" id="signin-user"
                           placeholder="Enter your username" autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="signin-password">Password</label>
                    <div class="ees-password-wrapper">
                        <input type="password" class="form-control" id="signin-password"
                               placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="ees-password-toggle" id="toggle-password"
                                tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fa fa-eye" id="toggle-password-icon"></i>
                        </button>
                    </div>
                </div>

                <div id="login-error" class="alert alert-danger" role="alert" style="display:none;"></div>

                <div style="margin-top:24px;">
                    <input type="submit" class="btn btn-primary btn-lg btn-block" id="login-submit-btn"
                           onclick="auth(); return false;" value="Sign In">
                </div>

                <div class="ees-auth-footer-link" style="margin-top:16px;">
                    <i class="fa fa-lock" style="margin-right:4px;opacity:.5;"></i>
                    <a href="forgot-password">Forgot your password?</a>
                </div>
            </form>
            </div>

            <div id="login-step-2fa" style="display:none;">
                <form onsubmit="return false;">
                    <p class="text-muted" style="margin-bottom:16px;">
                        Enter the 6-digit code from your authenticator app, or use a backup code.
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="signin-2fa-code">Verification code</label>
                        <input type="text" class="form-control" id="signin-2fa-code"
                               placeholder="000000" maxlength="8" inputmode="numeric" autocomplete="one-time-code">
                    </div>
                    <div class="form-check" style="margin-bottom:16px;">
                        <input type="checkbox" class="form-check-input" id="signin-2fa-backup">
                        <label class="form-check-label" for="signin-2fa-backup">Use a backup code instead</label>
                    </div>

                    <div id="login-2fa-error" class="alert alert-danger" role="alert" style="display:none;"></div>

                    <div style="margin-top:24px;">
                        <input type="submit" class="btn btn-primary btn-lg btn-block" id="login-2fa-submit-btn"
                               onclick="verify2FA(); return false;" value="Verify">
                    </div>
                    <div class="ees-auth-footer-link" style="margin-top:16px;">
                        <a href="#" id="login-2fa-back-link"><i class="fa fa-arrow-left"></i> Back to sign in</a>
                    </div>
                </form>
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
