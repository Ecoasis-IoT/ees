<?php
/**
 * User Login Handler
 * POST: username, pass, csrf_token
 * Returns JSON: { statusCode: "auth"|"Err"|"locked"|"rate_limit" }
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';
require_once __DIR__ . '/../common/auth_security.php';
require_once __DIR__ . '/../common/session_cookie_config.php';

$has_2fa_helper = file_exists(__DIR__ . '/../common/two_factor_auth.php');
if ($has_2fa_helper) {
    require_once __DIR__ . '/../common/two_factor_auth.php';
}

/**
 * Verify password (bcrypt via password_verify, or legacy unsalted MD5) and whether to rehash in DB.
 *
 * @return array{0: bool, 1: bool} [matches, needs_rehash]
 */
function ees_login_password_verify(string $plain, string $stored): array {
    if ($plain === '' || $stored === '') {
        return [false, false];
    }
    if (password_verify($plain, $stored)) {
        return [true, password_needs_rehash($stored, PASSWORD_DEFAULT)];
    }
    $md5 = strtolower($stored);
    if (strlen($md5) === 32 && ctype_xdigit($md5) && hash_equals($md5, md5($plain))) {
        return [true, true];
    }
    return [false, false];
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

// CSRF validation
applySessionCookieConfig();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'userlogin'], 'WARNING');
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$ip_address = getClientIP();

// Rate-limit check
if (checkRateLimit($ip_address)) {
    logSecurityEvent('rate_limit_exceeded', ['ip' => $ip_address, 'endpoint' => 'userlogin'], 'WARNING');
    echo json_encode(['statusCode' => 'rate_limit']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$pass     = $_POST['pass'] ?? '';

if (empty($username) || empty($pass)) {
    echo json_encode(['statusCode' => 'Err', 'message' => 'Missing credentials']);
    exit;
}

// Account lockout check
$lockout = checkAccountLockout($username);
if ($lockout['locked']) {
    logSecurityEvent('login_blocked_locked', ['username' => $username, 'ip' => $ip_address], 'WARNING');
    echo json_encode(['statusCode' => 'locked']);
    exit;
}

$pdo = getDB('admin');

try {
    $stmt = $pdo->prepare(
        "SELECT id, firstname, lastname, username, email, password, group_id, is_active
         FROM tbl_user
         WHERE username = :login OR email = :login_email
         LIMIT 1"
    );
    $stmt->execute([':login' => $username, ':login_email' => $username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // If is_active column doesn't exist yet, retry without it
    if (strpos($e->getMessage(), 'is_active') !== false) {
        try {
            $stmt = $pdo->prepare(
                "SELECT id, firstname, lastname, username, email, password, group_id
                 FROM tbl_user
                 WHERE username = :login OR email = :login_email
                 LIMIT 1"
            );
            $stmt->execute([':login' => $username, ':login_email' => $username]);
            $user = $stmt->fetch();
        } catch (PDOException $e2) {
            error_log("userlogin PDO error: " . $e2->getMessage());
            ob_end_clean();
            echo json_encode(['statusCode' => 'Err', 'message' => 'Server error']);
            exit;
        }
    } else {
        error_log("userlogin PDO error: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['statusCode' => 'Err', 'message' => 'Server error']);
        exit;
    }
}

// Check if account is blocked (is_active = 0)
// Column may not exist yet (before migration 003); treat missing as active
if ($user && isset($user['is_active']) && (int)$user['is_active'] === 0) {
    logSecurityEvent('login_blocked_account', ['username' => $username, 'ip' => $ip_address], 'WARNING');
    ob_end_clean();
    echo json_encode(['statusCode' => 'blocked', 'message' => 'Account is disabled. Please contact the administrator.']);
    exit;
}

[$password_ok, $needs_rehash] = $user
    ? ees_login_password_verify($pass, (string)($user['password'] ?? ''))
    : [false, false];

if ($user && $password_ok) {
    if ($needs_rehash) {
        try {
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            $rehash  = $pdo->prepare('UPDATE tbl_user SET password = :p WHERE id = :id LIMIT 1');
            $rehash->execute([':p' => $newHash, ':id' => $user['id']]);
        } catch (PDOException $e) {
            error_log('userlogin password rehash error: ' . $e->getMessage());
        }
    }

    resetLoginAttempts($username);

    if ($has_2fa_helper && userHas2FAEnabled($pdo, (int)$user['id'])) {
        ees_begin_pending_2fa($user, $username);
        logSecurityEvent('login_2fa_required', [
            'username' => $username,
            'user_id'    => $user['id'],
            'ip'         => $ip_address,
        ], 'INFO');

        ob_end_clean();
        echo json_encode([
            'statusCode' => '2fa_required',
            'message'    => 'Enter the code from your authenticator app.',
        ]);
        exit;
    }

    ees_establish_user_session($user);
    logSecurityEvent('login_success', ['username' => $username, 'ip' => $ip_address], 'INFO');

    require_once __DIR__ . '/../common/user_notifications.php';
    ees_sync_password_expiry_notification((int)$user['id'], $pdo);

    ob_end_clean();
    echo json_encode(['statusCode' => 'auth']);
} else {
    // Failed login (unknown user or bad password — same response to avoid user enumeration)
    recordFailedLoginAttempt($username, $ip_address);
    logSecurityEvent('login_failed', ['username' => $username, 'ip' => $ip_address], 'WARNING');

    ob_end_clean();
    echo json_encode([
        'statusCode' => 'Err',
        'message'    => 'Invalid username or password.',
    ]);
}
