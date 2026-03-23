<?php
ob_start();
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/authorization.php';
require_once __DIR__ . '/../common/two_factor_auth.php';
require_once __DIR__ . '/../common/security_logging.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); http_response_code(405);
    echo json_encode(['status' => 'Err']); exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();

$csrf = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf)) {
    ob_clean(); http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid security token']); exit;
}

$user_id = getCurrentUserId();
if (!$user_id) {
    ob_clean(); http_response_code(401);
    echo json_encode(['status' => 'Err']); exit;
}

$code = trim($_POST['code'] ?? '');
if (empty($code)) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => 'Verification code is required']); exit;
}

try {
    $pdo    = getDB('admin');
    $secret = $_SESSION['pending_2fa_secret'] ?? null;

    if (!$secret) {
        ob_clean(); http_response_code(400);
        echo json_encode(['status' => 'Err', 'message' => 'Setup session expired. Please start 2FA setup again.']); exit;
    }

    if (!verifyTOTPCode($secret, $code)) {
        logSecurityEvent('2fa_setup_code_invalid', ['user_id' => $user_id], 'WARNING');
        ob_clean(); http_response_code(400);
        echo json_encode(['status' => 'Err', 'message' => 'Invalid verification code. Please try again.']); exit;
    }

    enable2FA($pdo, $user_id);
    unset($_SESSION['pending_2fa_secret']);

    logSecurityEvent('2fa_enabled', ['user_id' => $user_id], 'INFO');

    ob_clean();
    echo json_encode(['status' => 'auth', 'message' => '2FA has been enabled successfully.']);
} catch (Exception $e) {
    error_log("profile_verify_2fa: " . $e->getMessage());
    ob_clean(); http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
