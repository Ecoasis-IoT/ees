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
         WHERE username = :username
         LIMIT 1"
    );
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // If is_active column doesn't exist yet, retry without it
    if (strpos($e->getMessage(), 'is_active') !== false) {
        try {
            $stmt = $pdo->prepare(
                "SELECT id, firstname, lastname, username, email, password, group_id
                 FROM tbl_user
                 WHERE username = :username
                 LIMIT 1"
            );
            $stmt->execute([':username' => $username]);
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

if ($user && password_verify($pass, $user['password'])) {
    // Successful login
    session_regenerate_id(true);
    $_SESSION['id']            = $user['id'];
    $_SESSION['firstname']     = $user['firstname'];
    $_SESSION['lastname']      = $user['lastname'];
    $_SESSION['name']          = $user['firstname'];
    $_SESSION['last_name']     = $user['lastname'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['group_id']      = $user['group_id'] ?? 0;
    $_SESSION['created']       = time();
    $_SESSION['last_activity'] = time();

    resetLoginAttempts($username);
    logSecurityEvent('login_success', ['username' => $username, 'ip' => $ip_address], 'INFO');

    ob_end_clean();
    echo json_encode(['statusCode' => 'auth']);
} else {
    // Failed login
    recordFailedLoginAttempt($username, $ip_address);
    logSecurityEvent('login_failed', ['username' => $username, 'ip' => $ip_address], 'WARNING');

    ob_end_clean();
    echo json_encode(['statusCode' => 'Err']);
}
