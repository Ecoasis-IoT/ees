<?php
/**
 * POST: Create a new user
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
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/user_create'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$fname    = sanitizeString($_POST['firstname'] ?? '');
$lname    = sanitizeString($_POST['lastname']  ?? '');
$username = sanitizeString($_POST['username']  ?? '');
$email    = sanitizeEmail($_POST['email']      ?? '');
$password = $_POST['password'] ?? '';
$group_id = intval($_POST['group_id'] ?? 0);

if (!$fname || !$lname || !$username || !$email || !$password) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'All fields are required']);
    exit;
}

$email_check = validateEmail($email);
if ($email_check !== true) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => $email_check]);
    exit;
}

$pdo = getDB('admin');

try {
    $dup = $pdo->prepare("SELECT COUNT(*) FROM tbl_user WHERE username = :u OR email = :e");
    $dup->execute([':u' => $username, ':e' => $email]);
    if ((int)$dup->fetchColumn() > 0) {
        ob_end_clean();
        echo json_encode(['status' => 'Err', 'message' => 'Username or email already exists']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare(
        "INSERT INTO tbl_user (firstname, lastname, username, password, email, group_id, date_added)
         VALUES (:fname, :lname, :username, :password, :email, :group_id, NOW())"
    );
    $ins->execute([
        ':fname'    => $fname,
        ':lname'    => $lname,
        ':username' => $username,
        ':password' => $hashed,
        ':email'    => $email,
        ':group_id' => $group_id,
    ]);

    $new_id = $pdo->lastInsertId();
    logSecurityEvent('user_created', ['new_user_id' => $new_id, 'by' => $_SESSION['id']], 'INFO');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'id' => $new_id, 'message' => 'User created successfully']);
} catch (PDOException $e) {
    error_log("user_create error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
