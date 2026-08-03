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

try {
    $pdo = getDB('admin');

    // Fetch user email
    $stmt = $pdo->prepare("SELECT email FROM tbl_user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        ob_clean(); http_response_code(404);
        echo json_encode(['status' => 'Err', 'message' => 'User not found']); exit;
    }

    $secret       = generate2FASecret();
    $backup_codes = generateBackupCodes();
    $qr_url       = generate2FAQRCodeData($user['email'], $secret);

    store2FASecret($pdo, $user_id, $secret, $backup_codes);

    // Store secret temporarily in session for verification step
    $_SESSION['pending_2fa_secret'] = $secret;

    ob_clean();
    echo json_encode([
        'status'       => 'auth',
        'qr_url'       => $qr_url,
        'secret'       => $secret,
        'backup_codes' => $backup_codes,
    ]);
} catch (Exception $e) {
    error_log("profile_setup_2fa: " . $e->getMessage());
    ob_clean(); http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
