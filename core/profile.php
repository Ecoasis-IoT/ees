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
if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . ees_url_path('login.php'));
    exit;
}

require_once __DIR__ . '/common/two_factor_auth.php';
$tfa_status = get2FAStatus($pdo, $user_id);
$tfa_mandatory = userMustSetup2FA($pdo, $user_id) || is2FAMandatoryForUser($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/ees-theme.css">
    <link rel="stylesheet" href="assets/css/pages/form-pages.css">
    <link rel="stylesheet" href="assets/css/pages/profile.css">
    <?php
    require_once __DIR__ . '/common/cdn_resources.php';
    echo getSweetAlert2JS();
    ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .tfa-badge-on  { display:inline-block; background:rgba(112,173,71,.15); color:#3d6d1f; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
        .tfa-badge-off { display:inline-block; background:rgba(100,116,139,.12); color:#475569; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
        #qr-setup-section { display:none; }
        .backup-codes-list { column-count:2; list-style:none; padding:0; font-family:monospace; font-size:14px; }
        .backup-codes-list li { padding:4px 0; color:#475569; }
    </style>
</head>
<body data-theme="theme-cyan">

<?php include_once("common/page-loader.php") ?>

<div id="wrapper">
    <?php include_once("common/header.php") ?>
    <?php include_once("common/sidebar.php") ?>

    <div id="content">
        <div id="main-content">
            <div class="container-fluid">
            <div class="block-header">
                <div class="row g-3">
                    <div class="col-lg-6 col-md-8 col-sm-12">
                        <h2>Profile Settings</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="icon-home"></i></a></li>
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
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input class="form-control" id="profile-username"
                                       value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                                       readonly style="background:#F8FAFC;cursor:default;">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">First Name</label>
                                        <input class="form-control" id="profile-fname"
                                               value="<?= htmlspecialchars($user['firstname'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Last Name</label>
                                        <input class="form-control" id="profile-lname"
                                               value="<?= htmlspecialchars($user['lastname'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input class="form-control" id="profile-email"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            </div>
                            <button class="btn btn-primary" onclick="saveProfileInfo(this)">
                                <i class="fa fa-save"></i>&nbsp; Save Changes
                            </button>
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
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current-pass" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new-pass" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm-pass" placeholder="Confirm new password">
                            </div>
                            <button class="btn btn-warning" onclick="changePassword(this)">
                                <i class="fa fa-lock"></i>&nbsp; Change Password
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card profile-section<?= $tfa_mandatory && empty($tfa_status['enabled']) ? ' tfa-required' : '' ?>" id="2fa-section">
                        <div class="header">
                            <h2>Two-Factor Authentication</h2>
                        </div>
                        <div class="body">
                            <p id="2fa-status-text">Loading two-factor status…</p>
                            <div id="2fa-status-section"></div>

                            <div id="2fa-setup-section">
                                <button type="button" class="btn bg-custom" data-action="setup-2fa" onclick="setup2FA()">
                                    <i class="fa fa-shield"></i> Setup 2FA
                                </button>
                                <div id="2fa-qr-section" style="display:none;">
                                    <img id="2fa-qr-code" alt="Authenticator QR code" width="180" height="180">
                                    <div class="tfa-secret-row">
                                        <input type="text" id="2fa-secret-key" class="form-control" readonly>
                                        <button type="button" class="btn bg-custom" onclick="copy2FASecret()">Copy</button>
                                    </div>
                                    <div id="2fa-backup-codes" class="tfa-code-grid"></div>
                                    <button type="button" class="btn bg-custom" onclick="copySetupBackupCodes()">Copy codes</button>
                                    <div class="tfa-verify-row">
                                        <input type="text" id="2fa-verify-code" class="form-control tfa-code-input" maxlength="6" placeholder="000000" inputmode="numeric" autocomplete="one-time-code">
                                    </div>
                                    <button type="button" class="btn bg-custom" onclick="verify2FASetup()">Enable 2FA</button>
                                    <button type="button" class="btn btn-default" onclick="get2FAStatus()">Cancel</button>
                                </div>
                            </div>

                            <div id="2fa-saved-codes" style="display:none;">
                                <div id="2fa-saved-codes-list" class="tfa-code-grid"></div>
                                <p id="2fa-saved-codes-note" class="tfa-note"></p>
                                <button type="button" class="btn bg-custom" onclick="copyBackupCodes()">Copy</button>
                                <button type="button" class="btn bg-custom" onclick="regenerateBackupCodes()">Regenerate</button>
                            </div>

                            <div id="2fa-enabled-section" style="display:none;">
                                <p>To turn 2FA off, enter your current password:</p>
                                <input type="password" class="form-control" id="2fa-disable-password" placeholder="Current password">
                                <button type="button" class="btn btn-danger" onclick="disable2FA()">Disable 2FA</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once("common/footer.php") ?>
            </div><!-- /.container-fluid -->
        </div><!-- /#main-content -->
    </div><!-- /#content -->
</div><!-- /#wrapper -->

<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>

<script>
var CSRF_TOKEN = '<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>';

function showMsg(successId, errorId, isSuccess, msg) {
    $('#' + successId + ', #' + errorId).hide().empty();
    if (isSuccess) { $('#' + successId).text(msg).show(); }
    else           { $('#' + errorId).text(msg).show(); }
}

function saveProfileInfo(btn) {
    EES.btnLoad(btn, 'Saving…');
    $.ajax({
        type: 'POST', url: 'scripts/profile_update_info',
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
        error: function() { showMsg('info-success','info-error', false, 'Request failed'); },
        complete: function() { EES.btnReset(btn); }
    });
}

function changePassword(btn) {
    EES.btnLoad(btn, 'Saving…');
    $.ajax({
        type: 'POST', url: 'scripts/profile_update_password',
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
        error: function() { showMsg('pass-success','pass-error', false, 'Request failed'); },
        complete: function() { EES.btnReset(btn); }
    });
}

</script>
<script src="assets/js/pages/profile.js"></script>
</body>
</html>
