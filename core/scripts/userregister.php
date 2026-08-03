<?php
/**
 * User Registration Handler
 * POST: username, fname, lname, email, password, csrf_token
 * Returns JSON: { statusCode: "auth"|"Err"|"duplicate", message?: string }
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';
require_once __DIR__ . '/../common/validation.php';
require_once __DIR__ . '/../common/session_cookie_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

// Refuse registrations if the feature is disabled in .env
if (!defined('REGISTRATION_LINK_ENABLED') || !REGISTRATION_LINK_ENABLED) {
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Registration is currently disabled.']);
    exit;
}

applySessionCookieConfig();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'userregister'], 'WARNING');
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$username = sanitizeString($_POST['username'] ?? '');
$fname    = sanitizeString($_POST['fname']    ?? '');
$lname    = sanitizeString($_POST['lname']    ?? '');
$email    = sanitizeEmail($_POST['email']     ?? '');
$pass     = $_POST['password'] ?? '';

// Validate inputs
if (empty($username) || empty($fname) || empty($lname) || empty($email) || empty($pass)) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'All fields are required']);
    exit;
}

$email_check = validateEmail($email);
if ($email_check !== true) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => $email_check]);
    exit;
}

$pdo = getDB('admin');

try {
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_user WHERE username = :u OR email = :e");
    $stmt->execute([':u' => $username, ':e' => $email]);
    if ((int)$stmt->fetchColumn() > 0) {
        ob_end_clean();
        echo json_encode(['statusCode' => 'duplicate', 'message' => 'Username or email already exists']);
        exit;
    }

    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    $ins = $pdo->prepare(
        "INSERT INTO tbl_user (firstname, lastname, username, password, email, date_added)
         VALUES (:fname, :lname, :username, :password, :email, NOW())"
    );
    $ins->execute([
        ':fname'    => $fname,
        ':lname'    => $lname,
        ':username' => $username,
        ':password' => $hashed,
        ':email'    => $email,
    ]);

    require_once __DIR__ . '/../common/user_notifications.php';
    ees_set_password_changed_at((int)$pdo->lastInsertId(), $pdo);

    logSecurityEvent('user_registered', ['username' => $username, 'email' => $email], 'INFO');

    ob_end_clean();
    echo json_encode(['statusCode' => 'auth']);
} catch (PDOException $e) {
    error_log("userregister PDO error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Server error']);
}
