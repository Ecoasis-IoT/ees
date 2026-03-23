<?php
/**
 * POST: Update an existing user
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/validation.php';
require_once __DIR__ . '/../../common/security_logging.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

requireAdmin();

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/user_update'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$id       = intval($_POST['id'] ?? 0);
$fname    = sanitizeString($_POST['firstname'] ?? '');
$lname    = sanitizeString($_POST['lastname']  ?? '');
$email    = sanitizeEmail($_POST['email']      ?? '');
$group_id = intval($_POST['group_id'] ?? 0);
$password = $_POST['password'] ?? '';

if (!$id || !$fname || !$lname || !$email) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Required fields missing']);
    exit;
}

$pdo = getDB('admin');

try {
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "UPDATE tbl_user SET firstname=:fname, lastname=:lname, email=:email, group_id=:gid, password=:pwd WHERE id=:id"
        );
        $stmt->execute([':fname' => $fname, ':lname' => $lname, ':email' => $email, ':gid' => $group_id, ':pwd' => $hashed, ':id' => $id]);
    } else {
        $stmt = $pdo->prepare(
            "UPDATE tbl_user SET firstname=:fname, lastname=:lname, email=:email, group_id=:gid WHERE id=:id"
        );
        $stmt->execute([':fname' => $fname, ':lname' => $lname, ':email' => $email, ':gid' => $group_id, ':id' => $id]);
    }

    logSecurityEvent('user_updated', ['user_id' => $id, 'by' => $_SESSION['id']], 'INFO');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'User updated successfully']);
} catch (PDOException $e) {
    error_log("user_update error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
