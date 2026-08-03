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

// Require current password to disable 2FA
$current_pass = $_POST['password'] ?? '';
if (empty($current_pass)) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => 'Please enter your password to disable 2FA']); exit;
}

try {
    $pdo  = getDB('admin');
    $stmt = $pdo->prepare("SELECT password FROM tbl_user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_pass, $user['password'])) {
        logSecurityEvent('2fa_disable_failed', ['user_id' => $user_id, 'reason' => 'wrong_password'], 'WARNING');
        ob_clean(); http_response_code(403);
        echo json_encode(['status' => 'Err', 'message' => 'Incorrect password']); exit;
    }

    disable2FA($pdo, $user_id);
    logSecurityEvent('2fa_disabled', ['user_id' => $user_id], 'INFO');

    ob_clean();
    echo json_encode(['status' => 'auth', 'message' => '2FA has been disabled.']);
} catch (Exception $e) {
    error_log("profile_disable_2fa: " . $e->getMessage());
    ob_clean(); http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
