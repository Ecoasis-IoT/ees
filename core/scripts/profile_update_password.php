<?php
ob_start();
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/authorization.php';
require_once __DIR__ . '/../common/security_logging.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); http_response_code(405);
    echo json_encode(['status' => 'Err', 'message' => 'Method not allowed']); exit;
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
    echo json_encode(['status' => 'Err', 'message' => 'Unauthorized']); exit;
}

$current_pass = $_POST['current_password'] ?? '';
$new_pass     = $_POST['new_password']     ?? '';
$confirm_pass = $_POST['confirm_password'] ?? '';

if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => 'All password fields are required']); exit;
}

if ($new_pass !== $confirm_pass) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => 'New passwords do not match']); exit;
}

if (strlen($new_pass) < 8) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => 'Password must be at least 8 characters']); exit;
}

try {
    $pdo = getDB('admin');

    $stmt = $pdo->prepare("SELECT password FROM tbl_user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_pass, $user['password'])) {
        logSecurityEvent('password_change_failed', ['user_id' => $user_id, 'reason' => 'wrong_current_password'], 'WARNING');
        ob_clean(); http_response_code(403);
        echo json_encode(['status' => 'Err', 'message' => 'Current password is incorrect']); exit;
    }

    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE tbl_user SET password = :pass WHERE id = :id");
    $upd->execute([':pass' => $hashed, ':id' => $user_id]);

    logSecurityEvent('password_changed', ['user_id' => $user_id], 'INFO');

    ob_clean();
    echo json_encode(['status' => 'auth', 'message' => 'Password changed successfully']);
} catch (PDOException $e) {
    error_log("profile_update_password PDO: " . $e->getMessage());
    ob_clean(); http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Database error']);
}
