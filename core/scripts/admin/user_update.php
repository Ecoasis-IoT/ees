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
$password = (string)($_POST['password'] ?? '');

if (!$id || !$fname || !$lname || !$email || !$group_id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Required fields missing']);
    exit;
}

$name_check = validateName($fname);
if ($name_check !== true) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => $name_check]);
    exit;
}
$name_check = validateName($lname);
if ($name_check !== true) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => $name_check]);
    exit;
}
$email_check = validateEmail($email);
if ($email_check !== true) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => $email_check]);
    exit;
}
if ($password !== '' && (strlen($password) < MIN_PASSWORD_LENGTH || strlen($password) > MAX_PASSWORD_LENGTH)) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Password must be ' . MIN_PASSWORD_LENGTH . ' to ' . MAX_PASSWORD_LENGTH . ' characters']);
    exit;
}

$admin_gid = defined('ADMIN_USERGROUP_ID') ? (int)ADMIN_USERGROUP_ID : 1;
if ($id === (int)($_SESSION['id'] ?? 0) && $group_id !== $admin_gid) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'You cannot remove your own admin access']);
    exit;
}

$pdo = getDB('admin');

try {
    $group = $pdo->prepare('SELECT id FROM tbl_user_group WHERE id = :id LIMIT 1');
    $group->execute([':id' => $group_id]);
    if (!$group->fetch()) {
        ob_end_clean();
        echo json_encode(['status' => 'Err', 'message' => 'Unknown role']);
        exit;
    }

    $dup = $pdo->prepare('SELECT id FROM tbl_user WHERE email = :email AND id <> :id LIMIT 1');
    $dup->execute([':email' => $email, ':id' => $id]);
    if ($dup->fetch()) {
        ob_end_clean();
        echo json_encode(['status' => 'Err', 'message' => 'That email is already used']);
        exit;
    }

    if ($password !== '') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "UPDATE tbl_user SET firstname=:fname, lastname=:lname, email=:email, group_id=:gid, password=:pwd WHERE id=:id"
        );
        $stmt->execute([':fname' => $fname, ':lname' => $lname, ':email' => $email, ':gid' => $group_id, ':pwd' => $hashed, ':id' => $id]);
        require_once __DIR__ . '/../../common/user_notifications.php';
        ees_set_password_changed_at($id, $pdo);
        ees_clear_password_expiry_notifications($id, $pdo);
    } else {
        $stmt = $pdo->prepare(
            "UPDATE tbl_user SET firstname=:fname, lastname=:lname, email=:email, group_id=:gid WHERE id=:id"
        );
        $stmt->execute([':fname' => $fname, ':lname' => $lname, ':email' => $email, ':gid' => $group_id, ':id' => $id]);
    }

    logSecurityEvent('user_updated', ['user_id' => $id, 'by' => $_SESSION['id']], 'INFO');

    if ($id === (int)($_SESSION['id'] ?? 0)) {
        $_SESSION['firstname'] = $fname;
        $_SESSION['lastname']  = $lname;
        $_SESSION['name']      = $fname;
        $_SESSION['last_name'] = $lname;
        $_SESSION['email']     = $email;
    }

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'User updated successfully']);
} catch (PDOException $e) {
    error_log("user_update error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
