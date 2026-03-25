<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/common/csrf.php';

$token = trim($_GET['token'] ?? '');

// Validate token exists and is not expired (silent redirect on bad token)
$token_valid = false;
if (!empty($token)) {
    try {
        $pdo  = getDB('admin');
        $stmt = $pdo->prepare(
            "SELECT id FROM tbl_user WHERE reset_token = :token AND reset_token_exp > NOW() LIMIT 1"
        );
        $stmt->execute([':token' => $token]);
        $token_valid = (bool) $stmt->fetch();
    } catch (PDOException $e) {
        error_log("reset-password token check error: " . $e->getMessage());
    }
}

if (!$token_valid) {
    header('Location: forgot-password.php?expired=1');
    exit;
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password | EES</title>
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
                <span>Choose a strong, unique password</span>
            </div>
            <div class="ees-auth-feature">
                <i class="fa fa-shield"></i>
                <span>At least 8 characters recommended</span>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="ees-auth-panel-right">
        <div class="ees-auth-card">

            <div class="ees-auth-card-header">
                <h2>Set New Password</h2>
                <p>Enter and confirm your new password below</p>
            </div>

            <div id="reset-alert" style="display:none;" class="alert"></div>

            <form onsubmit="return false;">
                <input type="hidden" id="reset-token"
                       value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <div class="ees-password-wrapper">
                        <input type="password" class="form-control" id="password"
                               placeholder="Enter new password" autocomplete="new-password">
                        <button type="button" class="ees-password-toggle" id="toggle-pw1"
                                aria-label="Toggle password visibility">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_con">Confirm Password</label>
                    <div class="ees-password-wrapper">
                        <input type="password" class="form-control" id="password_con"
                               placeholder="Confirm new password" autocomplete="new-password">
                        <button type="button" class="ees-password-toggle" id="toggle-pw2"
                                aria-label="Toggle password visibility">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div style="margin-top:24px;">
                    <input type="button" class="btn btn-primary btn-lg btn-block"
                           onclick="submitReset()" value="Reset Password">
                </div>

                <div class="ees-auth-footer-link" style="margin-top:16px;">
                    <a href="login.php"><i class="fa fa-arrow-left" style="margin-right:4px;"></i> Back to Sign In</a>
                </div>
            </form>

            <script>
            // Password toggles
            (function () {
                function toggle(btnId, inputId) {
                    var btn = document.getElementById(btnId);
                    var inp = document.getElementById(inputId);
                    if (!btn || !inp) return;
                    btn.addEventListener('click', function () {
                        var isText = inp.type === 'text';
                        inp.type = isText ? 'password' : 'text';
                        this.querySelector('i').className = isText ? 'fa fa-eye' : 'fa fa-eye-slash';
                    });
                }
                toggle('toggle-pw1', 'password');
                toggle('toggle-pw2', 'password_con');
            }());
            </script>

        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="assets/js/pages/reset-password.js"></script>
</body>
</html>
