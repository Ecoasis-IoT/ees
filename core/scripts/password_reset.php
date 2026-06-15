<?php
/**
 * Password Reset Handler
 * POST: token, password1, password2, csrf_token
 * Returns JSON: { statusCode: "ok"|"Err1"|"Err2"|"expired"|"Err" }
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';
require_once __DIR__ . '/../common/audit_logging.php';
require_once __DIR__ . '/../common/validation.php';
require_once __DIR__ . '/../common/session_cookie_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['statusCode' => 'Err']);
    exit;
}

applySessionCookieConfig();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'password_reset'], 'WARNING');
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$token = trim($_POST['token'] ?? '');
$pass1 = $_POST['password1'] ?? '';
$pass2 = $_POST['password2'] ?? '';

if (empty($token)) {
    ees_audit_log_password_reset('password_reset_token_invalid', [
        'token'  => $token,
        'reason' => 'missing_token',
    ], 'WARNING');
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err2', 'message' => 'Invalid or missing reset token']);
    exit;
}

if ($pass1 !== $pass2) {
    ees_audit_log_password_reset('password_reset_validation_failed', [
        'token'  => $token,
        'reason' => 'password_mismatch',
    ], 'WARNING');
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err1', 'message' => 'Passwords do not match']);
    exit;
}

if (empty($pass1) || strlen($pass1) < 8) {
    ees_audit_log_password_reset('password_reset_validation_failed', [
        'token'  => $token,
        'reason' => 'password_too_short',
    ], 'WARNING');
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Password must be at least 8 characters']);
    exit;
}

$pdo = getDB('admin');

try {
    $stmt = $pdo->prepare(
        "SELECT id, email FROM tbl_user
         WHERE reset_token = :token
           AND reset_token_exp > NOW()
         LIMIT 1"
    );
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        ees_audit_log_password_reset('password_reset_token_invalid', [
            'token'  => $token,
            'reason' => 'invalid_or_expired',
        ], 'WARNING');
        ob_end_clean();
        echo json_encode(['statusCode' => 'expired', 'message' => 'Reset link is invalid or has expired']);
        exit;
    }

    $hashed = password_hash($pass1, PASSWORD_DEFAULT);
    $upd    = $pdo->prepare(
        "UPDATE tbl_user SET password = :password, reset_token = NULL, reset_token_exp = NULL WHERE id = :id"
    );
    $upd->execute([':password' => $hashed, ':id' => $user['id']]);

    require_once __DIR__ . '/../common/user_notifications.php';
    ees_set_password_changed_at((int)$user['id'], $pdo);
    ees_clear_password_expiry_notifications((int)$user['id'], $pdo);

    logSecurityEvent('password_reset_completed', ['email' => $user['email']], 'INFO');

    ob_end_clean();
    echo json_encode(['statusCode' => 'ok']);
} catch (PDOException $e) {
    error_log("password_reset PDO error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err']);
}
