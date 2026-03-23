<?php
/**
 * Save Configuration to .env file
 * Updates .env file with new configuration values (admin only)
 */
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';

if (!isset($_SESSION['group_id']) || (int)$_SESSION['group_id'] !== 1) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => 'Unauthorized – admin access required'],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => 'Invalid security token'],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    exit;
}

$env_file = __DIR__ . '/../../.env';

if (!file_exists($env_file)) {
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => '.env file not found'],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_writable($env_file)) {
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => '.env file is not writable. Check file permissions.'],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    exit;
}

function boolStr($v): string {
    return ($v === '1' || $v === 'true' || $v === true || $v === 1) ? 'true' : 'false';
}

try {
    $env_content = file_get_contents($env_file);
    $lines       = explode("\n", $env_content);
    $updates     = [];

    // ── Security / Session ────────────────────────────────────────────────
    if (isset($_POST['SESSION_LIFETIME']))              $updates['SESSION_LIFETIME']              = max(300,  min(86400,  intval($_POST['SESSION_LIFETIME'])));
    if (isset($_POST['SESSION_COOKIE_LIFETIME']))       $updates['SESSION_COOKIE_LIFETIME']       = max(0,    min(86400,  intval($_POST['SESSION_COOKIE_LIFETIME'])));
    if (isset($_POST['SESSION_COOKIE_HTTPONLY']))       $updates['SESSION_COOKIE_HTTPONLY']       = boolStr($_POST['SESSION_COOKIE_HTTPONLY']);
    if (isset($_POST['SESSION_COOKIE_SECURE']))         $updates['SESSION_COOKIE_SECURE']         = boolStr($_POST['SESSION_COOKIE_SECURE']);
    if (isset($_POST['SESSION_USE_STRICT_MODE']))       $updates['SESSION_USE_STRICT_MODE']       = boolStr($_POST['SESSION_USE_STRICT_MODE']);
    if (isset($_POST['SESSION_COOKIE_SAMESITE'])) {
        $ss = $_POST['SESSION_COOKIE_SAMESITE'];
        if (in_array($ss, ['Strict', 'Lax', 'None'], true)) $updates['SESSION_COOKIE_SAMESITE'] = $ss;
    }
    if (isset($_POST['ACCOUNT_LOCKOUT_ATTEMPTS']))      $updates['ACCOUNT_LOCKOUT_ATTEMPTS']      = max(3,    min(10,    intval($_POST['ACCOUNT_LOCKOUT_ATTEMPTS'])));
    if (isset($_POST['ACCOUNT_LOCKOUT_DURATION']))      $updates['ACCOUNT_LOCKOUT_DURATION']      = max(300,  min(3600,  intval($_POST['ACCOUNT_LOCKOUT_DURATION'])));
    if (isset($_POST['LOGIN_RATE_LIMIT']))               $updates['LOGIN_RATE_LIMIT']               = max(5,    min(50,    intval($_POST['LOGIN_RATE_LIMIT'])));
    if (isset($_POST['LOGIN_RATE_LIMIT_WINDOW']))        $updates['LOGIN_RATE_LIMIT_WINDOW']        = max(30,   min(300,   intval($_POST['LOGIN_RATE_LIMIT_WINDOW'])));
    if (isset($_POST['HTTPS_ENABLED']))                  $updates['HTTPS_ENABLED']                  = boolStr($_POST['HTTPS_ENABLED']);
    if (isset($_POST['CSP_ENABLED']))                    $updates['CSP_ENABLED']                    = boolStr($_POST['CSP_ENABLED']);
    if (isset($_POST['HSTS_ENABLED']))                   $updates['HSTS_ENABLED']                   = boolStr($_POST['HSTS_ENABLED']);

    // ── Two-Factor Authentication ──────────────────────────────────────────
    if (isset($_POST['TWO_FACTOR_ENABLED']))             $updates['TWO_FACTOR_ENABLED']             = boolStr($_POST['TWO_FACTOR_ENABLED']);
    if (isset($_POST['TWO_FACTOR_ISSUER']))              $updates['TWO_FACTOR_ISSUER']              = trim($_POST['TWO_FACTOR_ISSUER']);
    if (isset($_POST['TWO_FACTOR_REQUIRED_FOR_ADMIN'])) $updates['TWO_FACTOR_REQUIRED_FOR_ADMIN']  = boolStr($_POST['TWO_FACTOR_REQUIRED_FOR_ADMIN']);
    if (isset($_POST['TWO_FACTOR_BACKUP_CODES_COUNT'])) $updates['TWO_FACTOR_BACKUP_CODES_COUNT']  = max(5, min(20, intval($_POST['TWO_FACTOR_BACKUP_CODES_COUNT'])));
    if (isset($_POST['TWO_FACTOR_WINDOW']))              $updates['TWO_FACTOR_WINDOW']              = max(1, min(3,  intval($_POST['TWO_FACTOR_WINDOW'])));

    // ── Password Policy ────────────────────────────────────────────────────
    if (isset($_POST['MIN_PASSWORD_LENGTH']))            $updates['MIN_PASSWORD_LENGTH']            = max(6,  min(20,  intval($_POST['MIN_PASSWORD_LENGTH'])));
    if (isset($_POST['MAX_PASSWORD_DISPLAY_LENGTH']))    $updates['MAX_PASSWORD_DISPLAY_LENGTH']    = max(10, min(50,  intval($_POST['MAX_PASSWORD_DISPLAY_LENGTH'])));
    if (isset($_POST['PASSWORD_HISTORY_COUNT']))         $updates['PASSWORD_HISTORY_COUNT']         = max(3,  min(10,  intval($_POST['PASSWORD_HISTORY_COUNT'])));
    if (isset($_POST['PASSWORD_EXPIRATION_DAYS']))       $updates['PASSWORD_EXPIRATION_DAYS']       = max(0,  min(365, intval($_POST['PASSWORD_EXPIRATION_DAYS'])));

    // ── Application ────────────────────────────────────────────────────────
    if (isset($_POST['BASE_URL']))      $updates['BASE_URL']      = trim($_POST['BASE_URL']);
    if (isset($_POST['TIMEZONE']))      $updates['TIMEZONE']      = trim($_POST['TIMEZONE']);
    if (isset($_POST['ENVIRONMENT'])) {
        $env = $_POST['ENVIRONMENT'];
        if (in_array($env, ['development', 'production'], true)) $updates['ENVIRONMENT'] = $env;
    }
    if (isset($_POST['DISPLAY_ERRORS'])) $updates['DISPLAY_ERRORS'] = boolStr($_POST['DISPLAY_ERRORS']);

    // ── Email / SMTP ───────────────────────────────────────────────────────
    if (isset($_POST['SMTP_HOST']))       $updates['SMTP_HOST']       = trim($_POST['SMTP_HOST']);
    if (isset($_POST['SMTP_PORT']))       $updates['SMTP_PORT']       = max(1, min(65535, intval($_POST['SMTP_PORT'])));
    if (isset($_POST['SMTP_USERNAME']))   $updates['SMTP_USERNAME']   = trim($_POST['SMTP_USERNAME']);
    if (isset($_POST['SMTP_PASSWORD']) && $_POST['SMTP_PASSWORD'] !== '')
                                          $updates['SMTP_PASSWORD']   = trim($_POST['SMTP_PASSWORD']);
    if (isset($_POST['SMTP_FROM_EMAIL'])) $updates['SMTP_FROM_EMAIL'] = trim($_POST['SMTP_FROM_EMAIL']);
    if (isset($_POST['SMTP_FROM_NAME']))  $updates['SMTP_FROM_NAME']  = trim($_POST['SMTP_FROM_NAME']);

    // ── CAPTCHA ────────────────────────────────────────────────────────────
    if (isset($_POST['CAPTCHA_ENABLED']))            $updates['CAPTCHA_ENABLED']            = boolStr($_POST['CAPTCHA_ENABLED']);
    if (isset($_POST['CAPTCHA_ATTEMPTS_THRESHOLD'])) $updates['CAPTCHA_ATTEMPTS_THRESHOLD'] = max(1, min(10, intval($_POST['CAPTCHA_ATTEMPTS_THRESHOLD'])));
    if (isset($_POST['RECAPTCHA_SITE_KEY']))         $updates['RECAPTCHA_SITE_KEY']         = trim($_POST['RECAPTCHA_SITE_KEY']);
    if (isset($_POST['RECAPTCHA_SECRET_KEY']) && $_POST['RECAPTCHA_SECRET_KEY'] !== '')
                                                     $updates['RECAPTCHA_SECRET_KEY']       = trim($_POST['RECAPTCHA_SECRET_KEY']);

    // ── File Upload ────────────────────────────────────────────────────────
    if (isset($_POST['MAX_UPLOAD_SIZE'])) {
        $mb = max(1, min(100, intval($_POST['MAX_UPLOAD_SIZE'])));
        $updates['MAX_UPLOAD_SIZE'] = $mb * 1048576;
    }
    if (isset($_POST['UPLOAD_DIR']))              $updates['UPLOAD_DIR']              = trim($_POST['UPLOAD_DIR']);
    if (isset($_POST['UPLOAD_ALLOWED_TYPES']))    $updates['UPLOAD_ALLOWED_TYPES']    = trim($_POST['UPLOAD_ALLOWED_TYPES']);
    if (isset($_POST['UPLOAD_USE_SECURE_NAMES'])) $updates['UPLOAD_USE_SECURE_NAMES'] = boolStr($_POST['UPLOAD_USE_SECURE_NAMES']);

    // ── API Rate Limiting ──────────────────────────────────────────────────
    $api_int_fields = [
        'API_RATE_LIMIT_LOGIN_MAX', 'API_RATE_LIMIT_LOGIN_WINDOW',
        'API_RATE_LIMIT_PASSWORD_RESET_MAX', 'API_RATE_LIMIT_PASSWORD_RESET_WINDOW',
        'API_RATE_LIMIT_REGISTRATION_MAX', 'API_RATE_LIMIT_REGISTRATION_WINDOW',
        'API_RATE_LIMIT_LIST_MAX', 'API_RATE_LIMIT_LIST_WINDOW',
        'API_RATE_LIMIT_CREATE_MAX', 'API_RATE_LIMIT_CREATE_WINDOW',
        'API_RATE_LIMIT_UPDATE_MAX', 'API_RATE_LIMIT_UPDATE_WINDOW',
        'API_RATE_LIMIT_DELETE_MAX', 'API_RATE_LIMIT_DELETE_WINDOW',
        'API_RATE_LIMIT_DEFAULT_MAX', 'API_RATE_LIMIT_DEFAULT_WINDOW',
    ];
    foreach ($api_int_fields as $f) {
        if (isset($_POST[$f])) $updates[$f] = max(1, intval($_POST[$f]));
    }

    // ── Webhook ────────────────────────────────────────────────────────────
    if (isset($_POST['WEBHOOK_SECRET']) && $_POST['WEBHOOK_SECRET'] !== '')
                                                     $updates['WEBHOOK_SECRET']              = trim($_POST['WEBHOOK_SECRET']);
    if (isset($_POST['WEBHOOK_REQUIRE_SIGNATURE']))  $updates['WEBHOOK_REQUIRE_SIGNATURE']   = boolStr($_POST['WEBHOOK_REQUIRE_SIGNATURE']);
    if (isset($_POST['WEBHOOK_IP_WHITELIST']))        $updates['WEBHOOK_IP_WHITELIST']        = trim($_POST['WEBHOOK_IP_WHITELIST']);
    if (isset($_POST['WEBHOOK_RATE_LIMIT_MAX']))      $updates['WEBHOOK_RATE_LIMIT_MAX']      = max(1, intval($_POST['WEBHOOK_RATE_LIMIT_MAX']));
    if (isset($_POST['WEBHOOK_RATE_LIMIT_WINDOW']))   $updates['WEBHOOK_RATE_LIMIT_WINDOW']   = max(1, intval($_POST['WEBHOOK_RATE_LIMIT_WINDOW']));

    // ── Registration & Validation ──────────────────────────────────────────
    if (isset($_POST['REGISTRATION_LINK_ENABLED']))  $updates['REGISTRATION_LINK_ENABLED']  = boolStr($_POST['REGISTRATION_LINK_ENABLED']);
    if (isset($_POST['REGISTRATION_LINK_SECRET']) && $_POST['REGISTRATION_LINK_SECRET'] !== '')
                                                     $updates['REGISTRATION_LINK_SECRET']   = trim($_POST['REGISTRATION_LINK_SECRET']);
    if (isset($_POST['REGISTRATION_LINK_EXPIRY']))   $updates['REGISTRATION_LINK_EXPIRY']   = max(3600, min(604800, intval($_POST['REGISTRATION_LINK_EXPIRY'])));
    if (isset($_POST['ADMIN_CAN_VIEW_ALL_PROFILES'])) $updates['ADMIN_CAN_VIEW_ALL_PROFILES'] = boolStr($_POST['ADMIN_CAN_VIEW_ALL_PROFILES']);
    if (isset($_POST['MAX_EMAIL_LENGTH']))            $updates['MAX_EMAIL_LENGTH']            = max(20, min(100,  intval($_POST['MAX_EMAIL_LENGTH'])));
    if (isset($_POST['MAX_NAME_LENGTH']))             $updates['MAX_NAME_LENGTH']             = max(10, min(100,  intval($_POST['MAX_NAME_LENGTH'])));
    if (isset($_POST['MIN_NAME_LENGTH']))             $updates['MIN_NAME_LENGTH']             = max(1,  min(10,   intval($_POST['MIN_NAME_LENGTH'])));

    // ── Rewrite .env lines ──────────────────────────────────────────────────
    $written_keys  = [];
    $updated_lines = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || $trimmed[0] === '#') {
            $updated_lines[] = $line;
            continue;
        }
        $replaced = false;
        foreach ($updates as $key => $value) {
            if (preg_match('/^' . preg_quote($key, '/') . '\s*=/', $trimmed)) {
                $updated_lines[] = $key . '=' . $value;
                $written_keys[]  = $key;
                $replaced        = true;
                break;
            }
        }
        if (!$replaced) $updated_lines[] = $line;
    }

    // Append any keys not already in the file
    foreach ($updates as $key => $value) {
        if (!in_array($key, $written_keys, true)) {
            $updated_lines[] = $key . '=' . $value;
        }
    }

    // Backup then write
    copy($env_file, $env_file . '.backup');
    if (file_put_contents($env_file, implode("\n", $updated_lines)) === false) {
        ob_clean();
        echo json_encode(['statusCode' => 'error', 'message' => 'Failed to write .env file'],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    logSecurityEvent('config_updated', [
        'user_id'      => $_SESSION['id'] ?? 0,
        'updated_keys' => array_keys($updates),
    ], 'INFO');

    ob_clean();
    echo json_encode([
        'statusCode' => 'success',
        'message'    => 'Configuration saved successfully',
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("save_config error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => 'Error saving configuration: ' . $e->getMessage()],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}
?>
