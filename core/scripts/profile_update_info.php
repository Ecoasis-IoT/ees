<?php
ob_start();
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/validation.php';
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

$fname = sanitizeString($_POST['fname'] ?? '');
$lname = sanitizeString($_POST['lname'] ?? '');
$email = sanitizeEmail($_POST['email'] ?? '');

if (empty($fname) || empty($lname) || empty($email)) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => 'All fields are required']); exit;
}

$email_check = validateEmail($email);
if ($email_check !== true) {
    ob_clean(); http_response_code(400);
    echo json_encode(['status' => 'Err', 'message' => $email_check]); exit;
}

try {
    $pdo = getDB('admin');

    // Check email not taken by another user
    $stmt = $pdo->prepare("SELECT id FROM tbl_user WHERE email = :email AND id != :id LIMIT 1");
    $stmt->execute([':email' => $email, ':id' => $user_id]);
    if ($stmt->fetch()) {
        ob_clean(); http_response_code(409);
        echo json_encode(['status' => 'Err', 'message' => 'Email already in use']); exit;
    }

    $upd = $pdo->prepare(
        "UPDATE tbl_user SET firstname = :fname, lastname = :lname, email = :email WHERE id = :id"
    );
    $upd->execute([':fname' => $fname, ':lname' => $lname, ':email' => $email, ':id' => $user_id]);

    // Update session
    $_SESSION['fname'] = $fname;
    $_SESSION['lname'] = $lname;

    logSecurityEvent('profile_updated', ['user_id' => $user_id], 'INFO');

    ob_clean();
    echo json_encode(['status' => 'auth', 'message' => 'Profile updated successfully']);
} catch (PDOException $e) {
    error_log("profile_update_info PDO: " . $e->getMessage());
    ob_clean(); http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Database error']);
}
