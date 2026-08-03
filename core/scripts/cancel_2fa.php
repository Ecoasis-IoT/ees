<?php
/**
 * Cancel pending login 2FA verification.
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/auth_security.php';
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
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

ees_clear_pending_2fa();
ob_end_clean();
echo json_encode(['statusCode' => 'ok']);
