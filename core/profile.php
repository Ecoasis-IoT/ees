<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

$csrf_token = generateCSRFToken();

$pdo     = getDB('admin');
$user_id = (int)$_SESSION['id'];

$stmt = $pdo->prepare("SELECT firstname, lastname, username, email FROM tbl_user WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

// 2FA status
$has2fa     = false;
$tfa_status = ['enabled' => false, 'has_secret' => false, 'backup_codes_count' => 0];
if (file_exists(__DIR__ . '/common/two_factor_auth.php')) {
    require_once __DIR__ . '/common/two_factor_auth.php';
    $has2fa     = true;
    $tfa_status = get2FAStatus($pdo, $user_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/form-pages.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .profile-section { margin-bottom: 30px; }
        .alert { padding: 10px 16px; border-radius: 4px; margin-bottom: 15px; display: none; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger  { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .tfa-badge-on  { display:inline-block; background:#28a745; color:#fff; padding:2px 10px; border-radius:12px; font-size:12px; }
        .tfa-badge-off { display:inline-block; background:#6c757d; color:#fff; padding:2px 10px; border-radius:12px; font-size:12px; }
        #qr-setup-section { display:none; }
        .backup-codes-list { column-count:2; list-style:none; padding:0; font-family:monospace; font-size:15px; }
        .backup-codes-list li { padding:3px 0; }
    </style>
</head>
<body data-theme="theme-cyan">

<?php include_once("common/page-loader.php") ?>

<div id="wrapper">
    <?php include_once("common/header.php") ?>
    <?php include_once("common/sidebar.php") ?>

    <div id="main-content">
        <div class="container-fluid">
            <div class="block-header">
                <div class="row g-3">
                    <div class="col-lg-6 col-md-8 col-sm-12">
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> Profile Settings</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Basic Info -->
                <div class="col-lg-6">
                    <div class="card profile-section">
                        <div class="header"><h2>Basic Information</h2></div>
                        <div class="body">
                            <div class="alert alert-success" id="info-success"></div>
                            <div class="alert alert-danger"  id="info-error"></div>
                            <table class="table table-bordered">
                                <tr><td style="width:160px;">Username</td>
                                    <td><input class="form-control" id="profile-username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly style="background:#f5f5f5;"></td></tr>
                                <tr><td>First Name</td>
                                    <td><input class="form-control" id="profile-fname" value="<?= htmlspecialchars($user['firstname'] ?? '') ?>"></td></tr>
                                <tr><td>Last Name</td>
                                    <td><input class="form-control" id="profile-lname" value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"></td></tr>
                                <tr><td>Email</td>
                                    <td><input class="form-control" id="profile-email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"></td></tr>
                            </table>
                            <button class="btn btn-primary" onclick="saveProfileInfo()">Save Changes</button>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-lg-6">
                    <div class="card profile-section">
                        <div class="header"><h2>Change Password</h2></div>
                        <div class="body">
                            <div class="alert alert-success" id="pass-success"></div>
                            <div class="alert alert-danger"  id="pass-error"></div>
                            <table class="table table-bordered">
                                <tr><td style="width:180px;">Current Password</td>
                                    <td><input type="password" class="form-control" id="current-pass"></td></tr>
                                <tr><td>New Password</td>
                                    <td><input type="password" class="form-control" id="new-pass"></td></tr>
                                <tr><td>Confirm New Password</td>
                                    <td><input type="password" class="form-control" id="confirm-pass"></td></tr>
                            </table>
                            <button class="btn btn-warning" onclick="changePassword()">Change Password</button>
                        </div>
                    </div>
                </div>

                <?php if ($has2fa): ?>
                <!-- 2FA -->
                <div class="col-lg-12">
                    <div class="card profile-section">
                        <div class="header">
                            <h2>Two-Factor Authentication (2FA)
                                <?php if ($tfa_status['enabled']): ?>
                                    <span class="tfa-badge-on">ENABLED</span>
                                <?php else: ?>
                                    <span class="tfa-badge-off">DISABLED</span>
                                <?php endif; ?>
                            </h2>
                        </div>
                        <div class="body">
                            <div class="alert alert-success" id="tfa-success"></div>
                            <div class="alert alert-danger"  id="tfa-error"></div>

                            <?php if (!$tfa_status['enabled']): ?>
                            <p>Two-factor authentication adds an extra layer of security to your account. Once enabled, you will need to enter a 6-digit code from your authenticator app when logging in.</p>
                            <button class="btn btn-success" onclick="initiate2FA()"><i class="fa fa-shield"></i> Set Up 2FA</button>

                            <div id="qr-setup-section" class="mt-4">
                                <h5>1. Scan this QR code with your authenticator app</h5>
                                <img id="qr-image" src="" alt="QR Code" style="border:1px solid #ddd; padding:8px; margin:10px 0;">
                                <p><small>Or enter this secret manually: <code id="tfa-secret-text" style="font-size:14px;"></code></small></p>
                                <h5>2. Save your backup codes (one-time use)</h5>
                                <ul class="backup-codes-list" id="backup-codes-list"></ul>
                                <h5>3. Enter the 6-digit code from your app to verify</h5>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4">
                                        <input type="text" class="form-control" id="tfa-verify-code" placeholder="000000" maxlength="6">
                                    </div>
                                    <div class="col-lg-2 col-md-3 mt-2 mt-md-0">
                                        <button class="btn btn-primary" onclick="verify2FA()">Verify &amp; Enable</button>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <p>2FA is currently <strong>enabled</strong>. You have <strong><?= $tfa_status['backup_codes_count'] ?></strong> backup codes remaining.</p>
                            <p>To disable 2FA, enter your current password:</p>
                            <div class="row">
                                <div class="col-lg-3 col-md-4">
                                    <input type="password" class="form-control" id="tfa-disable-pass" placeholder="Current password">
                                </div>
                                <div class="col-lg-2 col-md-3 mt-2 mt-md-0">
                                    <button class="btn btn-danger" onclick="disable2FA()">Disable 2FA</button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php include_once("common/footer.php") ?>
        </div>
    </div>
</div>

<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>

<script>
var CSRF_TOKEN = '<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>';

function showMsg(successId, errorId, isSuccess, msg) {
    $('#' + successId + ', #' + errorId).hide();
    if (isSuccess) { $('#' + successId).text(msg).show(); }
    else           { $('#' + errorId).text(msg).show(); }
}

function saveProfileInfo() {
    $.ajax({
        type: 'POST', url: 'scripts/profile_update_info.php',
        data: {
            csrf_token: CSRF_TOKEN,
            fname: $('#profile-fname').val(),
            lname: $('#profile-lname').val(),
            email: $('#profile-email').val()
        },
        success: function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            showMsg('info-success','info-error', d.status==='auth', d.message || (d.status==='auth' ? 'Saved' : 'Error'));
        },
        error: function() { showMsg('info-success','info-error', false, 'Request failed'); }
    });
}

function changePassword() {
    $.ajax({
        type: 'POST', url: 'scripts/profile_update_password.php',
        data: {
            csrf_token:       CSRF_TOKEN,
            current_password: $('#current-pass').val(),
            new_password:     $('#new-pass').val(),
            confirm_password: $('#confirm-pass').val()
        },
        success: function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            showMsg('pass-success','pass-error', d.status==='auth', d.message || (d.status==='auth' ? 'Password changed' : 'Error'));
            if (d.status === 'auth') { $('#current-pass,#new-pass,#confirm-pass').val(''); }
        },
        error: function() { showMsg('pass-success','pass-error', false, 'Request failed'); }
    });
}

function initiate2FA() {
    $.ajax({
        type: 'POST', url: 'scripts/profile_setup_2fa.php',
        data: { csrf_token: CSRF_TOKEN },
        success: function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (d.status === 'auth') {
                $('#qr-image').attr('src', d.qr_url);
                $('#tfa-secret-text').text(d.secret);
                var html = '';
                $.each(d.backup_codes, function(i,c){ html += '<li>' + c + '</li>'; });
                $('#backup-codes-list').html(html);
                $('#qr-setup-section').slideDown();
            } else {
                showMsg('tfa-success','tfa-error', false, d.message || 'Failed to initiate 2FA setup');
            }
        },
        error: function() { showMsg('tfa-success','tfa-error', false, 'Request failed'); }
    });
}

function verify2FA() {
    $.ajax({
        type: 'POST', url: 'scripts/profile_verify_2fa.php',
        data: { csrf_token: CSRF_TOKEN, code: $('#tfa-verify-code').val() },
        success: function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (d.status === 'auth') {
                showMsg('tfa-success','tfa-error', true, d.message || '2FA enabled');
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                showMsg('tfa-success','tfa-error', false, d.message || 'Invalid code');
            }
        },
        error: function() { showMsg('tfa-success','tfa-error', false, 'Request failed'); }
    });
}

function disable2FA() {
    $.ajax({
        type: 'POST', url: 'scripts/profile_disable_2fa.php',
        data: { csrf_token: CSRF_TOKEN, password: $('#tfa-disable-pass').val() },
        success: function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (d.status === 'auth') {
                showMsg('tfa-success','tfa-error', true, d.message || '2FA disabled');
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                showMsg('tfa-success','tfa-error', false, d.message || 'Failed');
            }
        },
        error: function() { showMsg('tfa-success','tfa-error', false, 'Request failed'); }
    });
}
</script>
</body>
</html>
