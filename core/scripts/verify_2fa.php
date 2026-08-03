<?php
/**
 * Login 2FA verification — TOTP or backup code after successful password step.
 * POST: code, csrf_token, is_backup (optional "true")
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

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'verify_2fa'], 'WARNING');
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

if (empty($_SESSION['2fa_pending']) || empty($_SESSION['2fa_user_id'])) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'No verification in progress. Please sign in again.']);
    exit;
}

if (!empty($_SESSION['2fa_created']) && (time() - (int)$_SESSION['2fa_created']) > 600) {
    ees_clear_pending_2fa();
    ob_end_clean();
    echo json_encode([
        'statusCode' => 'timeout',
        'message'    => 'Verification timed out. Please sign in again.',
    ]);
    exit;
}

$code = trim($_POST['code'] ?? '');
if ($code === '') {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Please enter your verification code.']);
    exit;
}

$user_id   = (int)$_SESSION['2fa_user_id'];
$username  = $_SESSION['2fa_login_id'] ?? $_SESSION['2fa_username'] ?? '';
$ip_address = getClientIP();
$is_backup = isset($_POST['is_backup']) && $_POST['is_backup'] === 'true';

try {
    $pdo = getDB('admin');
    $verified = $is_backup
        ? verifyUserBackupCode($pdo, $user_id, $code)
        : verifyUserTOTPCode($pdo, $user_id, $code);

    if (!$verified) {
        logSecurityEvent('2fa_verification_failed', [
            'username'            => $username,
            'user_id'               => $user_id,
            'ip'                    => $ip_address,
            'backup_code_attempted' => $is_backup,
        ], 'WARNING');

        ob_end_clean();
        echo json_encode([
            'statusCode' => 'Err',
            'message'    => 'Invalid verification code. Please try again.',
        ]);
        exit;
    }

    $user = ees_pending_2fa_user_from_session();
    if (!$user) {
        ob_end_clean();
        echo json_encode(['statusCode' => 'Err', 'message' => 'Session expired. Please sign in again.']);
        exit;
    }

    ees_establish_user_session($user);
    ees_clear_pending_2fa();

    logSecurityEvent('login_success', [
        'username'         => $username,
        'user_id'          => $user_id,
        'ip'               => $ip_address,
        '2fa_enabled'      => true,
        'backup_code_used' => $is_backup,
    ], 'INFO');

    require_once __DIR__ . '/../common/user_notifications.php';
    ees_sync_password_expiry_notification($user_id, $pdo);

    ob_end_clean();
    echo json_encode([
        'statusCode'       => 'auth',
        'message'          => 'Signed in successfully.',
        'backup_code_used' => $is_backup,
    ]);
} catch (Exception $e) {
    error_log('verify_2fa error: ' . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Server error. Please try again.']);
}
