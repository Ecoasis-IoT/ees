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
    <title>Ecoasis - Reset Password | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/reset-password.css">
</head>
<body data-theme="theme-cyan">
    <div id="wrapper">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="card">
                        <img src="assets/images/ecoasis_logo.jpg" id="eco-logo" alt="Ecoasis Logo">
                        <div class="header">
                            <p class="lead">Reset your Password</p>
                        </div>
                        <div class="body">
                            <div id="reset-alert" style="display:none;"></div>
                            <form class="form-auth-small" onsubmit="return false;">
                                <input type="hidden" id="reset-token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="password" placeholder="New Password" autocomplete="new-password">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="password" class="form-control" id="password_con" placeholder="Confirm New Password" autocomplete="new-password">
                                </div>
                                <input type="button" class="btn btn-primary btn-lg btn-block" onclick="submitReset()" value="Reset Password">
                                <div class="bottom">
                                    <span class="helper-text"><a href="login.php">Back to Login</a></span>
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
    <script src="assets/js/pages/reset-password.js"></script>
</body>
</html>
