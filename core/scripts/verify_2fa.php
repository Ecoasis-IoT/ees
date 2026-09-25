<?php
/**
 * Login 2FA verification — TOTP, emailed code, then backup code.
 * POST: code, csrf_token
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';
require_once __DIR__ . '/../common/auth_security.php';
require_once __DIR__ . '/../common/two_factor_auth.php';
require_once __DIR__ . '/../common/session_cookie_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

applySessionCookieConfig();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ees_verify_2fa_json(string $status, string $message): void {
    ob_end_clean();
    echo json_encode(['statusCode' => $status, 'message' => $message]);
    exit;
}

if (empty($_SESSION['2fa_pending'])) {
    $csrf_token = trim($_POST['csrf_token'] ?? '');
    if ($csrf_token === '' || !validateCSRFToken($csrf_token)) {
        logSecurityEvent('csrf_failure', ['endpoint' => 'verify_2fa'], 'WARNING');
        ees_verify_2fa_json('Err', 'Your session has expired. Please sign in again.');
    }
    ees_verify_2fa_json('Err', 'No verification in progress. Please sign in again.');
}

$pending_limit = !empty($_SESSION['2fa_email_code_expires']) ? 900 : 300;
if (!empty($_SESSION['2fa_created']) && (time() - (int)$_SESSION['2fa_created']) > $pending_limit) {
    ees_clear_pending_2fa();
    ees_verify_2fa_json('timeout', 'Verification timed out. Please sign in again.');
}

$code = trim($_POST['code'] ?? '');
if ($code === '') {
    ees_verify_2fa_json('Err', 'Please enter your verification code.');
}

$user_id = (int)$_SESSION['2fa_user_id'];
$username = $_SESSION['2fa_login_id'] ?? $_SESSION['2fa_username'] ?? '';
$ip_address = getClientIP();

try {
    $pdo = getDB('admin');
    $totp_valid = verifyUserTOTPCode($pdo, $user_id, $code);
    $email_valid = false;
    $backup_valid = false;

    if (!$totp_valid && preg_match('/^\d{6}$/', $code)) {
        $email_valid = verify2FAEmailCode($code);
    }
    if (!$totp_valid && !$email_valid) {
        $backup_valid = verifyUserBackupCode($pdo, $user_id, $code);
    }

    if (!$totp_valid && !$email_valid && !$backup_valid) {
        logSecurityEvent('2fa_verification_failed', [
            'username' => $username,
            'user_id'  => $user_id,
            'ip'       => $ip_address,
        ], 'WARNING');
        ees_verify_2fa_json('Err', 'Invalid verification code. Please try again.');
    }

    $user = ees_pending_2fa_user_from_session();
    if (!$user) {
        ees_verify_2fa_json('Err', 'Session expired. Please sign in again.');
    }

    ees_establish_user_session($user);
    ees_clear_pending_2fa();

    logSecurityEvent('login_success', [
        'username'    => $username,
        'user_id'     => $user_id,
        'ip'          => $ip_address,
        '2fa_enabled' => true,
        'method'      => $totp_valid ? 'totp' : ($email_valid ? 'email' : 'backup_code'),
    ], 'INFO');

    require_once __DIR__ . '/../common/user_notifications.php';
    ees_sync_password_expiry_notification($user_id, $pdo);

    ob_end_clean();
    echo json_encode([
        'statusCode' => 'auth',
        'message'    => 'Signed in successfully.',
        'link'       => 'dashboard',
    ]);
} catch (Exception $e) {
    error_log('verify_2fa error: ' . $e->getMessage());
    ees_verify_2fa_json('Err', 'Server error. Please try again.');
}
