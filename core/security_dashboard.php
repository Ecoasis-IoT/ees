<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

// Admin only
if (!isset($_SESSION['group_id']) || (int)$_SESSION['group_id'] !== (int)ADMIN_USERGROUP_ID) {
    header('Location: dashboard.php');
    exit;
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Security Dashboard | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- DataTables Buttons -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stat-card { border-left: 4px solid #26a69a; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-val { font-size: 2rem; font-weight: 700; line-height: 1.1; }
        .stat-label { color: #888; font-size: 13px; margin-top: 4px; }
        .trend-up { color: #dc3545; font-size: 12px; }
        .trend-down { color: #28a745; font-size: 12px; }
        .severity-info     { color: #17a2b8; }
        .severity-warning  { color: #ffc107; }
        .severity-error    { color: #fd7e14; }
        .severity-critical { color: #dc3545; font-weight: 600; }
        .chart-container { position: relative; height: 260px; }
        .refresh-btn { float: right; margin-top: -3px; }
        /* Logs table */
        .log-filters .form-control { font-size: 13px; height: 32px; padding: 4px 8px; }
        .log-filters label { font-size: 12px; font-weight: 600; margin-bottom: 2px; }
        .badge-severity-INFO     { background:#17a2b8; color:#fff; }
        .badge-severity-WARNING  { background:#ffc107; color:#212529; }
        .badge-severity-ERROR    { background:#fd7e14; color:#fff; }
        .badge-severity-CRITICAL { background:#dc3545; color:#fff; }
        #logs-table_wrapper .dataTables_filter { display: none; }
        .log-detail-pre { white-space: pre-wrap; word-break: break-word; font-size: 12px;
                          max-height: 200px; overflow-y: auto; background: #f8f9fa;
                          border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; margin: 0; }
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
                    <div class="col-lg-8 col-md-8 col-sm-12">
                        <h2><a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a>
                            Security Dashboard <small>Last 7 days</small>
                        </h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>
                            <li class="breadcrumb-item active">Security</li>
                        </ul>
                    </div>
                    <div class="col-lg-4 text-right">
                        <button class="btn btn-outline-primary refresh-btn" onclick="loadStats()"><i class="fa fa-refresh"></i> Refresh</button>
                    </div>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="row clearfix g-3 mb-3" id="stat-cards">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card danger">
                        <div class="body">
                            <div class="stat-val" id="stat-failed-logins">—</div>
                            <div class="stat-label">Failed Logins (24h)</div>
                            <div id="stat-failed-trend"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card warning">
                        <div class="body">
                            <div class="stat-val" id="stat-locked">—</div>
                            <div class="stat-label">Locked Accounts</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card">
                        <div class="body">
                            <div class="stat-val" id="stat-total-events">—</div>
                            <div class="stat-label">Security Events (7d)</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card">
                        <div class="body">
                            <div class="stat-val" id="stat-event-types">—</div>
                            <div class="stat-label">Distinct Event Types</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="header"><h2>Login Attempts <small>Last 7 days</small></h2></div>
                        <div class="body">
                            <div class="chart-container"><canvas id="loginChart"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card">
                        <div class="header"><h2>Event Types <small>Last 7 days</small></h2></div>
                        <div class="body">
                            <div class="chart-container"><canvas id="eventChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header"><h2>Recent Security Events <small>Latest 20</small></h2></div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="events-table">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Severity</th>
                                            <th>IP Address</th>
                                            <th>User ID</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="events-tbody">
                                        <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════
                 System Configuration
                 ═══════════════════════════════════════════════════ -->
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2><i class="fa fa-cog"></i> System Configuration <small>Edit and save .env settings</small></h2>
                        </div>
                        <div class="body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" href="#cfg-security"  data-bs-toggle="tab"><i class="fa fa-shield"></i> Security</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-2fa"       data-bs-toggle="tab"><i class="fa fa-key"></i> Two-Factor</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-password"  data-bs-toggle="tab"><i class="fa fa-lock"></i> Password</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-app"       data-bs-toggle="tab"><i class="fa fa-globe"></i> Application</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-smtp"      data-bs-toggle="tab"><i class="fa fa-envelope"></i> Email/SMTP</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-captcha"   data-bs-toggle="tab"><i class="fa fa-check-square-o"></i> CAPTCHA</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-upload"    data-bs-toggle="tab"><i class="fa fa-upload"></i> File Upload</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-api"       data-bs-toggle="tab"><i class="fa fa-code"></i> API Limits</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-webhook"   data-bs-toggle="tab"><i class="fa fa-plug"></i> Webhook</a></li>
                                <li class="nav-item"><a class="nav-link" href="#cfg-reg"       data-bs-toggle="tab"><i class="fa fa-user-plus"></i> Registration</a></li>
                            </ul>

                            <div class="tab-content mt-3" id="configTabContent">

                                <!-- ── Security ────────────────────────────── -->
                                <div class="tab-pane fade show active" id="cfg-security">
                                    <form class="cfg-form" data-section="Security">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-lg-12"><h6>Session</h6></div>
                                            <div class="col-md-4 form-group">
                                                <label>Session Lifetime (seconds)</label>
                                                <input type="number" class="form-control" name="SESSION_LIFETIME" value="<?= intval($_ENV['SESSION_LIFETIME'] ?? 14400) ?>" min="300" max="86400">
                                                <small class="text-muted">Idle timeout (300–86400, default 14400 = 4 h)</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Cookie Lifetime (seconds)</label>
                                                <input type="number" class="form-control" name="SESSION_COOKIE_LIFETIME" value="<?= intval($_ENV['SESSION_COOKIE_LIFETIME'] ?? 0) ?>" min="0" max="86400">
                                                <small class="text-muted">0 = expires when browser closes</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>SameSite Policy</label>
                                                <select class="form-control" name="SESSION_COOKIE_SAMESITE">
                                                    <?php foreach (['Strict','Lax','None'] as $opt): ?>
                                                    <option value="<?= $opt ?>" <?= (($_ENV['SESSION_COOKIE_SAMESITE'] ?? 'Strict') === $opt ? 'selected' : '') ?>><?= $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="d-block">HttpOnly Cookie</label>
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="SESSION_COOKIE_HTTPONLY" value="1" <?= filter_var($_ENV['SESSION_COOKIE_HTTPONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Enable HttpOnly</span></label>
                                                </div>
                                                <small class="text-muted">Prevents JS access to session cookie</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="d-block">Secure Cookie (HTTPS only)</label>
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="SESSION_COOKIE_SECURE" value="1" <?= filter_var($_ENV['SESSION_COOKIE_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Enable Secure flag</span></label>
                                                </div>
                                                <small class="text-muted">Leave off for localhost</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="d-block">Strict Session Mode</label>
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="SESSION_USE_STRICT_MODE" value="1" <?= filter_var($_ENV['SESSION_USE_STRICT_MODE'] ?? 'true', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Enable Strict Mode</span></label>
                                                </div>
                                                <small class="text-muted">Prevents session fixation</small>
                                            </div>

                                            <div class="col-lg-12"><hr><h6>Account Lockout</h6></div>
                                            <div class="col-md-4 form-group">
                                                <label>Lockout Attempts</label>
                                                <input type="number" class="form-control" name="ACCOUNT_LOCKOUT_ATTEMPTS" value="<?= intval($_ENV['ACCOUNT_LOCKOUT_ATTEMPTS'] ?? 5) ?>" min="3" max="10">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Lockout Duration (seconds)</label>
                                                <input type="number" class="form-control" name="ACCOUNT_LOCKOUT_DURATION" value="<?= intval($_ENV['ACCOUNT_LOCKOUT_DURATION'] ?? 1800) ?>" min="300" max="3600">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Login Rate Limit (requests)</label>
                                                <input type="number" class="form-control" name="LOGIN_RATE_LIMIT" value="<?= intval($_ENV['LOGIN_RATE_LIMIT'] ?? 10) ?>" min="5" max="50">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Rate Limit Window (seconds)</label>
                                                <input type="number" class="form-control" name="LOGIN_RATE_LIMIT_WINDOW" value="<?= intval($_ENV['LOGIN_RATE_LIMIT_WINDOW'] ?? 60) ?>" min="30" max="300">
                                            </div>

                                            <div class="col-lg-12"><hr><h6>Security Headers</h6></div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="HTTPS_ENABLED" value="1" <?= filter_var($_ENV['HTTPS_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Require HTTPS</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="CSP_ENABLED" value="1" <?= filter_var($_ENV['CSP_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Content Security Policy</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="HSTS_ENABLED" value="1" <?= filter_var($_ENV['HSTS_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>HSTS Header</span></label>
                                                </div>
                                            </div>

                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Security Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── Two-Factor Auth ──────────────────────── -->
                                <div class="tab-pane fade" id="cfg-2fa">
                                    <form class="cfg-form" data-section="Two-Factor Auth">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="TWO_FACTOR_ENABLED" value="1" <?= filter_var($_ENV['TWO_FACTOR_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Enable 2FA System</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="TWO_FACTOR_REQUIRED_FOR_ADMIN" value="1" <?= filter_var($_ENV['TWO_FACTOR_REQUIRED_FOR_ADMIN'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Require 2FA for Admins</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Issuer Name (shown in auth app)</label>
                                                <input type="text" class="form-control" name="TWO_FACTOR_ISSUER" value="<?= htmlspecialchars($_ENV['TWO_FACTOR_ISSUER'] ?? 'EES System', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Backup Codes Count (5–20)</label>
                                                <input type="number" class="form-control" name="TWO_FACTOR_BACKUP_CODES_COUNT" value="<?= intval($_ENV['TWO_FACTOR_BACKUP_CODES_COUNT'] ?? 10) ?>" min="5" max="20">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>TOTP Time Window (1–3)</label>
                                                <input type="number" class="form-control" name="TWO_FACTOR_WINDOW" value="<?= intval($_ENV['TWO_FACTOR_WINDOW'] ?? 1) ?>" min="1" max="3">
                                                <small class="text-muted">1 = ±30 s drift tolerance</small>
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save 2FA Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── Password Policy ──────────────────────── -->
                                <div class="tab-pane fade" id="cfg-password">
                                    <form class="cfg-form" data-section="Password Policy">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-3 form-group">
                                                <label>Min Password Length</label>
                                                <input type="number" class="form-control" name="MIN_PASSWORD_LENGTH" value="<?= intval($_ENV['MIN_PASSWORD_LENGTH'] ?? 8) ?>" min="6" max="20">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Max Display Length</label>
                                                <input type="number" class="form-control" name="MAX_PASSWORD_DISPLAY_LENGTH" value="<?= intval($_ENV['MAX_PASSWORD_DISPLAY_LENGTH'] ?? 30) ?>" min="10" max="50">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Password History Count</label>
                                                <input type="number" class="form-control" name="PASSWORD_HISTORY_COUNT" value="<?= intval($_ENV['PASSWORD_HISTORY_COUNT'] ?? 5) ?>" min="3" max="10">
                                                <small class="text-muted">Prevent reuse of last N passwords</small>
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Expiration Days (0 = disabled)</label>
                                                <input type="number" class="form-control" name="PASSWORD_EXPIRATION_DAYS" value="<?= intval($_ENV['PASSWORD_EXPIRATION_DAYS'] ?? 0) ?>" min="0" max="365">
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Password Policy</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── Application ──────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-app">
                                    <form class="cfg-form" data-section="Application">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>Base URL</label>
                                                <input type="url" class="form-control" name="BASE_URL" value="<?= htmlspecialchars($_ENV['BASE_URL'] ?? 'http://localhost/EES', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Timezone</label>
                                                <input type="text" class="form-control" name="TIMEZONE" value="<?= htmlspecialchars($_ENV['TIMEZONE'] ?? 'Indian/Mauritius', ENT_QUOTES, 'UTF-8') ?>">
                                                <small class="text-muted">PHP timezone identifier</small>
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Environment</label>
                                                <select class="form-control" name="ENVIRONMENT">
                                                    <option value="development" <?= (($_ENV['ENVIRONMENT'] ?? 'development') === 'development' ? 'selected' : '') ?>>Development</option>
                                                    <option value="production"  <?= (($_ENV['ENVIRONMENT'] ?? 'development') === 'production'  ? 'selected' : '') ?>>Production</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="DISPLAY_ERRORS" value="1" <?= filter_var($_ENV['DISPLAY_ERRORS'] ?? 'true', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Display PHP Errors</span></label>
                                                </div>
                                                <small class="text-muted">Disable in production</small>
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save App Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── Email / SMTP ──────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-smtp">
                                    <form class="cfg-form" data-section="Email/SMTP">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label>SMTP Host</label>
                                                <input type="text" class="form-control" name="SMTP_HOST" value="<?= htmlspecialchars($_ENV['SMTP_HOST'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label>Port</label>
                                                <input type="number" class="form-control" name="SMTP_PORT" value="<?= intval($_ENV['SMTP_PORT'] ?? 587) ?>" min="1" max="65535">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Username</label>
                                                <input type="text" class="form-control" name="SMTP_USERNAME" value="<?= htmlspecialchars($_ENV['SMTP_USERNAME'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Password <small class="text-muted">(leave blank to keep)</small></label>
                                                <input type="password" class="form-control" name="SMTP_PASSWORD" placeholder="••••••••">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>From Email</label>
                                                <input type="email" class="form-control" name="SMTP_FROM_EMAIL" value="<?= htmlspecialchars($_ENV['SMTP_FROM_EMAIL'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>From Name</label>
                                                <input type="text" class="form-control" name="SMTP_FROM_NAME" value="<?= htmlspecialchars($_ENV['SMTP_FROM_NAME'] ?? 'EES Platform', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save SMTP Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── CAPTCHA ────────────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-captcha">
                                    <form class="cfg-form" data-section="CAPTCHA">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="CAPTCHA_ENABLED" value="1" <?= filter_var($_ENV['CAPTCHA_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Enable CAPTCHA</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Show CAPTCHA After N Failed Attempts</label>
                                                <input type="number" class="form-control" name="CAPTCHA_ATTEMPTS_THRESHOLD" value="<?= intval($_ENV['CAPTCHA_ATTEMPTS_THRESHOLD'] ?? 3) ?>" min="1" max="10">
                                            </div>
                                            <div class="col-lg-12"><hr><h6>Google reCAPTCHA v2</h6>
                                                <p class="text-muted">Get keys from <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a>. Use <strong>Challenge (v2) → "I'm not a robot"</strong>.</p>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>Site Key</label>
                                                <input type="text" class="form-control" name="RECAPTCHA_SITE_KEY" value="<?= htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>Secret Key <small class="text-muted">(leave blank to keep)</small></label>
                                                <input type="password" class="form-control" name="RECAPTCHA_SECRET_KEY" placeholder="••••••••">
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save CAPTCHA Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── File Upload ────────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-upload">
                                    <form class="cfg-form" data-section="File Upload">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-3 form-group">
                                                <label>Max Upload Size (MB)</label>
                                                <input type="number" class="form-control" name="MAX_UPLOAD_SIZE" value="<?= intval(($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880) / 1048576) ?>" min="1" max="100">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Upload Directory</label>
                                                <input type="text" class="form-control" name="UPLOAD_DIR" value="<?= htmlspecialchars($_ENV['UPLOAD_DIR'] ?? 'upload', ENT_QUOTES, 'UTF-8') ?>">
                                                <small class="text-muted">Relative to core/</small>
                                            </div>
                                            <div class="col-md-5 form-group">
                                                <label>Allowed MIME Types</label>
                                                <input type="text" class="form-control" name="UPLOAD_ALLOWED_TYPES" value="<?= htmlspecialchars($_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'image/jpeg,image/png,application/pdf', ENT_QUOTES, 'UTF-8') ?>">
                                                <small class="text-muted">Comma-separated MIME types</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="UPLOAD_USE_SECURE_NAMES" value="1" <?= filter_var($_ENV['UPLOAD_USE_SECURE_NAMES'] ?? 'true', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Use Random Filenames</span></label>
                                                </div>
                                                <small class="text-muted">Prevents path traversal &amp; conflicts</small>
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Upload Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── API Rate Limits ────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-api">
                                    <form class="cfg-form" data-section="API Rate Limits">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <?php
                                            $api_groups = [
                                                'Login'          => ['API_RATE_LIMIT_LOGIN_MAX',          'API_RATE_LIMIT_LOGIN_WINDOW'],
                                                'Password Reset' => ['API_RATE_LIMIT_PASSWORD_RESET_MAX', 'API_RATE_LIMIT_PASSWORD_RESET_WINDOW'],
                                                'Registration'   => ['API_RATE_LIMIT_REGISTRATION_MAX',   'API_RATE_LIMIT_REGISTRATION_WINDOW'],
                                                'List / Read'    => ['API_RATE_LIMIT_LIST_MAX',           'API_RATE_LIMIT_LIST_WINDOW'],
                                                'Create'         => ['API_RATE_LIMIT_CREATE_MAX',         'API_RATE_LIMIT_CREATE_WINDOW'],
                                                'Update'         => ['API_RATE_LIMIT_UPDATE_MAX',         'API_RATE_LIMIT_UPDATE_WINDOW'],
                                                'Delete'         => ['API_RATE_LIMIT_DELETE_MAX',         'API_RATE_LIMIT_DELETE_WINDOW'],
                                                'Default'        => ['API_RATE_LIMIT_DEFAULT_MAX',        'API_RATE_LIMIT_DEFAULT_WINDOW'],
                                            ];
                                            $defaults = [
                                                'API_RATE_LIMIT_LOGIN_MAX'=>10,'API_RATE_LIMIT_LOGIN_WINDOW'=>60,
                                                'API_RATE_LIMIT_PASSWORD_RESET_MAX'=>5,'API_RATE_LIMIT_PASSWORD_RESET_WINDOW'=>3600,
                                                'API_RATE_LIMIT_REGISTRATION_MAX'=>3,'API_RATE_LIMIT_REGISTRATION_WINDOW'=>3600,
                                                'API_RATE_LIMIT_LIST_MAX'=>100,'API_RATE_LIMIT_LIST_WINDOW'=>60,
                                                'API_RATE_LIMIT_CREATE_MAX'=>20,'API_RATE_LIMIT_CREATE_WINDOW'=>60,
                                                'API_RATE_LIMIT_UPDATE_MAX'=>20,'API_RATE_LIMIT_UPDATE_WINDOW'=>60,
                                                'API_RATE_LIMIT_DELETE_MAX'=>10,'API_RATE_LIMIT_DELETE_WINDOW'=>60,
                                                'API_RATE_LIMIT_DEFAULT_MAX'=>100,'API_RATE_LIMIT_DEFAULT_WINDOW'=>60,
                                            ];
                                            foreach ($api_groups as $label => [$maxKey, $winKey]):
                                            ?>
                                            <div class="col-lg-12"><h6 class="mt-2"><?= htmlspecialchars($label) ?></h6></div>
                                            <div class="col-md-3 form-group">
                                                <label>Max Requests</label>
                                                <input type="number" class="form-control" name="<?= $maxKey ?>" value="<?= intval($_ENV[$maxKey] ?? $defaults[$maxKey]) ?>" min="1" max="1000">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Window (seconds)</label>
                                                <input type="number" class="form-control" name="<?= $winKey ?>" value="<?= intval($_ENV[$winKey] ?? $defaults[$winKey]) ?>" min="1" max="86400">
                                            </div>
                                            <?php endforeach; ?>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save API Rate Limits</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── Webhook ────────────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-webhook">
                                    <form class="cfg-form" data-section="Webhook">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>Webhook Secret <small class="text-muted">(leave blank to keep)</small></label>
                                                <input type="text" class="form-control" name="WEBHOOK_SECRET" placeholder="Generate: openssl rand -hex 32">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>IP Whitelist</label>
                                                <input type="text" class="form-control" name="WEBHOOK_IP_WHITELIST" value="<?= htmlspecialchars($_ENV['WEBHOOK_IP_WHITELIST'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="192.168.1.1, 10.0.0.0/8">
                                                <small class="text-muted">Comma-separated IPs/CIDR (empty = allow all)</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="WEBHOOK_REQUIRE_SIGNATURE" value="1" <?= filter_var($_ENV['WEBHOOK_REQUIRE_SIGNATURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Require HMAC Signature</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Rate Limit Max Requests</label>
                                                <input type="number" class="form-control" name="WEBHOOK_RATE_LIMIT_MAX" value="<?= intval($_ENV['WEBHOOK_RATE_LIMIT_MAX'] ?? 100) ?>" min="1" max="10000">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Rate Limit Window (seconds)</label>
                                                <input type="number" class="form-control" name="WEBHOOK_RATE_LIMIT_WINDOW" value="<?= intval($_ENV['WEBHOOK_RATE_LIMIT_WINDOW'] ?? 60) ?>" min="1" max="3600">
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Webhook Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- ── Registration ───────────────────────────── -->
                                <div class="tab-pane fade" id="cfg-reg">
                                    <form class="cfg-form" data-section="Registration">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="REGISTRATION_LINK_ENABLED" value="1" <?= filter_var($_ENV['REGISTRATION_LINK_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Require Secure Registration Links</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Registration Link Secret <small class="text-muted">(blank = keep)</small></label>
                                                <input type="text" class="form-control" name="REGISTRATION_LINK_SECRET" placeholder="64-char hex string">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Link Expiry (seconds)</label>
                                                <input type="number" class="form-control" name="REGISTRATION_LINK_EXPIRY" value="<?= intval($_ENV['REGISTRATION_LINK_EXPIRY'] ?? 86400) ?>" min="3600" max="604800">
                                                <small class="text-muted">Default 86400 = 24 h</small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="fancy-checkbox">
                                                    <label><input type="checkbox" name="ADMIN_CAN_VIEW_ALL_PROFILES" value="1" <?= filter_var($_ENV['ADMIN_CAN_VIEW_ALL_PROFILES'] ?? 'true', FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>><span>Admin Can View All Profiles</span></label>
                                                </div>
                                            </div>
                                            <div class="col-lg-12"><hr><h6>Input Validation Limits</h6></div>
                                            <div class="col-md-3 form-group">
                                                <label>Max Email Length</label>
                                                <input type="number" class="form-control" name="MAX_EMAIL_LENGTH" value="<?= intval($_ENV['MAX_EMAIL_LENGTH'] ?? 60) ?>" min="20" max="100">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Max Name Length</label>
                                                <input type="number" class="form-control" name="MAX_NAME_LENGTH" value="<?= intval($_ENV['MAX_NAME_LENGTH'] ?? 50) ?>" min="10" max="100">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Min Name Length</label>
                                                <input type="number" class="form-control" name="MIN_NAME_LENGTH" value="<?= intval($_ENV['MIN_NAME_LENGTH'] ?? 1) ?>" min="1" max="10">
                                            </div>
                                            <div class="col-lg-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Registration Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div><!-- /tab-content -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- View All Security Logs -->
            <div class="row clearfix g-3 mb-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2>View All Security Logs <small>Server-side searchable &amp; filterable</small></h2>
                        </div>
                        <div class="body">
                            <!-- Filter row -->
                            <div class="row log-filters mb-3">
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Severity</label>
                                    <select id="log-filter-severity" class="form-control">
                                        <option value="">All severities</option>
                                        <option value="INFO">INFO</option>
                                        <option value="WARNING">WARNING</option>
                                        <option value="ERROR">ERROR</option>
                                        <option value="CRITICAL">CRITICAL</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Event Type</label>
                                    <input type="text" id="log-filter-event" class="form-control" placeholder="e.g. login_failed">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>IP Address</label>
                                    <input type="text" id="log-filter-ip" class="form-control" placeholder="e.g. 192.168.1.1">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Date From</label>
                                    <input type="date" id="log-filter-date-from" class="form-control">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label>Date To</label>
                                    <input type="date" id="log-filter-date-to" class="form-control">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2" style="display:flex;align-items:flex-end;gap:6px;">
                                    <button id="log-filter-apply" class="btn btn-sm btn-primary" style="flex:1;"><i class="fa fa-filter"></i> Apply</button>
                                    <button id="log-filter-clear"  class="btn btn-sm btn-default" style="flex:1;"><i class="fa fa-times"></i> Clear</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped" id="logs-table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width:160px;">Timestamp</th>
                                            <th>Event Type</th>
                                            <th style="width:90px;">Severity</th>
                                            <th>User / Email</th>
                                            <th style="width:130px;">IP Address</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once("common/footer.php") ?>
        </div>
    </div>
</div>

<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>
<!-- DataTables core (must load before Buttons plugins) -->
<script src="assets/bundles/datatablescripts.bundle.js"></script>
<!-- DataTables Buttons plugins -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
var CSRF_TOKEN = '<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>';
var loginChart, eventChart;

function loadStats() {
    $.ajax({
        type: 'POST', url: 'scripts/get_security_stats.php',
        data: { csrf_token: CSRF_TOKEN },
        success: function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (d.statusCode !== 'success') { alert('Error loading stats'); return; }
            var s = d.stats;

            $('#stat-failed-logins').text(s.failed_logins_24h || 0);
            var trend = s.failed_logins_trend || 0;
            if (trend > 0) {
                $('#stat-failed-trend').html('<span class="trend-up"><i class="fa fa-arrow-up"></i> +' + trend.toFixed(1) + '%</span>');
            } else if (trend < 0) {
                $('#stat-failed-trend').html('<span class="trend-down"><i class="fa fa-arrow-down"></i> ' + trend.toFixed(1) + '%</span>');
            }

            $('#stat-locked').text(s.locked_accounts || 0);
            $('#stat-event-types').text((s.event_types_chart || []).length);

            var totalEvents = 0;
            $.each(s.login_attempts_chart || [], function(i, row){ totalEvents += parseInt(row.successful||0) + parseInt(row.failed||0); });
            $('#stat-total-events').text(totalEvents);

            renderLoginChart(s.login_attempts_chart || []);
            renderEventChart(s.event_types_chart || []);
            renderRecentEvents(s.recent_events || []);
        },
        error: function() { alert('Failed to load security statistics.'); }
    });
}

function renderLoginChart(data) {
    var labels = [], successful = [], failed = [];
    $.each(data, function(i, row){ labels.push(row.date); successful.push(row.successful); failed.push(row.failed); });

    if (loginChart) loginChart.destroy();
    loginChart = new Chart(document.getElementById('loginChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Successful', data: successful, backgroundColor: '#28a745' },
                { label: 'Failed',     data: failed,     backgroundColor: '#dc3545' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { x: { stacked: false }, y: { beginAtZero: true } },
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

function renderEventChart(data) {
    var labels = [], counts = [];
    var colors = ['#26a69a','#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#E7E9ED','#71B37C'];
    $.each(data, function(i, row){ labels.push(row.event_type); counts.push(row.count); });

    if (eventChart) eventChart.destroy();
    if (labels.length === 0) { $('#eventChart').parent().html('<p class="text-muted text-center py-4">No events in last 7 days</p>'); return; }
    eventChart = new Chart(document.getElementById('eventChart'), {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: counts, backgroundColor: colors }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });
}

function renderRecentEvents(events) {
    if (!events.length) {
        $('#events-tbody').html('<tr><td colspan="5" class="text-center text-muted">No recent events</td></tr>');
        return;
    }
    var rows = '';
    $.each(events, function(i, e) {
        var sev = (e.severity || 'INFO').toUpperCase();
        var sevClass = sev === 'CRITICAL' ? 'severity-critical' : sev === 'ERROR' ? 'severity-error' : sev === 'WARNING' ? 'severity-warning' : 'severity-info';
        rows += '<tr>' +
            '<td>' + $('<span>').text(e.event_type).html() + '</td>' +
            '<td><span class="' + sevClass + '">' + sev + '</span></td>' +
            '<td>' + $('<span>').text(e.ip_address || '—').html() + '</td>' +
            '<td>' + (e.user_id || '—') + '</td>' +
            '<td>' + $('<span>').text(e.created_at).html() + '</td>' +
            '</tr>';
    });
    $('#events-tbody').html(rows);
}

$(document).ready(function() {
    loadStats();
    initLogsTable();
});

// ─── Config Form Submissions ──────────────────────────────────────────────

$(document).on('submit', '.cfg-form', function(e) {
    e.preventDefault();
    var $form    = $(this);
    var section  = $form.data('section') || 'Settings';
    var $btn     = $form.find('[type=submit]');
    var origHtml = $btn.html(); // preserve exact label to restore after request

    // Force unchecked checkboxes to send '0' so the backend sees them
    var formData = new FormData(this);
    $form.find('input[type=checkbox]').each(function() {
        if (!this.checked) formData.set(this.name, '0');
    });

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');

    $.ajax({
        url: 'scripts/save_config.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(r) {
            var d = (typeof r === 'string') ? JSON.parse(r) : r;
            if (d.statusCode === 'success') {
                showAlert('success', section + ' saved successfully.');
            } else {
                showAlert('danger', d.message || 'Failed to save ' + section + '.');
            }
        },
        error: function() {
            showAlert('danger', 'Connection error — could not save ' + section + '.');
        },
        complete: function() {
            $btn.prop('disabled', false).html(origHtml); // restore original label exactly
        }
    });
});

function showAlert(type, msg) {
    var $a = $('<div class="alert alert-' + type + ' alert-dismissible" role="alert">' +
               '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
               '<strong>' + (type === 'success' ? '<i class="fa fa-check"></i> Saved!' : '<i class="fa fa-exclamation-triangle"></i> Error') + '</strong> ' +
               $('<span>').text(msg).html() +
               '</div>');
    $('#configTabs').before($a);
    setTimeout(function() { $a.alert('close'); }, 4000);
}

// ─── Security Logs DataTable ───────────────────────────────────────────────

var logsTable = null;

function initLogsTable() {
    logsTable = $('#logs-table').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,           // Don't auto-load — wait for user action
        ajax: {
            url: 'scripts/get_security_logs.php',
            type: 'POST',
            data: function(d) {
                d.csrf_token        = CSRF_TOKEN;
                d.filter_severity   = $('#log-filter-severity').val();
                d.filter_event_type = $('#log-filter-event').val();
                d.filter_ip         = $('#log-filter-ip').val();
                d.filter_date_from  = $('#log-filter-date-from').val();
                d.filter_date_to    = $('#log-filter-date-to').val();
            },
            error: function(xhr) {
                console.error('Logs AJAX error', xhr.responseText);
            }
        },
        columns: [
            { data: 'created_at',  title: 'Timestamp', width: '160px' },
            { data: 'event_type',  title: 'Event Type' },
            {
                data: 'severity', title: 'Severity', width: '90px',
                render: function(val) {
                    var sev = (val || 'INFO').toUpperCase();
                    return '<span class="badge badge-severity-' + sev + '">' + sev + '</span>';
                }
            },
            {
                data: 'email', title: 'User / Email',
                render: function(val) { return val ? $('<span>').text(val).html() : '<span class="text-muted">—</span>'; }
            },
            {
                data: 'ip_address', title: 'IP Address', width: '130px',
                render: function(val) { return val ? $('<span>').text(val).html() : '<span class="text-muted">—</span>'; }
            },
            {
                data: 'details', title: 'Details',
                render: function(val) {
                    if (!val) return '<span class="text-muted">—</span>';
                    // Try pretty-print JSON
                    try {
                        var obj = JSON.parse(val);
                        val = JSON.stringify(obj, null, 2);
                    } catch(e) {}
                    return '<pre class="log-detail-pre">' + $('<span>').text(val).html() + '</pre>';
                }
            }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        dom: 'lfrtipB',
        buttons: [
            { extend: 'csvHtml5',   text: '<i class="fa fa-download"></i> CSV',   className: 'btn btn-sm btn-default', title: 'security_logs' },
            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i> Excel', className: 'btn btn-sm btn-default', title: 'security_logs' },
            { extend: 'print',      text: '<i class="fa fa-print"></i> Print', className: 'btn btn-sm btn-default',
              customize: function(win) {
                  $(win.document.body).find('pre.log-detail-pre').each(function() {
                      $(this).css({ 'max-height': 'none', 'white-space': 'pre-wrap' });
                  });
              }
            }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading…',
            zeroRecords: 'No matching logs found',
            emptyTable:  'No logs loaded yet — apply filters or search to begin'
        }
    });

    // Apply button: reload with current filters
    $('#log-filter-apply').on('click', function() {
        logsTable.ajax.reload();
    });

    // Clear button: reset all filters and reload
    $('#log-filter-clear').on('click', function() {
        $('#log-filter-severity').val('');
        $('#log-filter-event').val('');
        $('#log-filter-ip').val('');
        $('#log-filter-date-from').val('');
        $('#log-filter-date-to').val('');
        logsTable.search('').ajax.reload();
    });

    // Allow Enter key in filter inputs to trigger apply
    $('#log-filter-event, #log-filter-ip, #log-filter-date-from, #log-filter-date-to').on('keydown', function(e) {
        if (e.which === 13) { e.preventDefault(); $('#log-filter-apply').trigger('click'); }
    });
}
</script>
</body>
</html>
