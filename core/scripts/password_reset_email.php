<?php
/**
 * Password Reset Email Handler
 * POST: email, csrf_token
 * Sends a reset link to the user's email using PHPMailer.
 * Returns JSON: { statusCode: "ok"|"Err" }
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';
require_once __DIR__ . '/../common/session_cookie_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once __DIR__ . '/../../vendor/autoload.php';

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
    logSecurityEvent('csrf_failure', ['endpoint' => 'password_reset_email'], 'WARNING');
    http_response_code(403);
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'ok']); // Always respond OK to prevent email enumeration
    exit;
}

$pdo = getDB('admin');

try {
    $stmt = $pdo->prepare("SELECT id, firstname FROM tbl_user WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("password_reset_email PDO error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err']);
    exit;
}

// Always respond OK regardless of whether email exists (prevents enumeration)
if (!$user) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'ok']);
    exit;
}

// Generate a secure, single-use reset token and store it (migration 003 required)
$token     = bin2hex(random_bytes(32));
$token_exp = date('Y-m-d H:i:s', strtotime('+1 hour'));

try {
    $upd = $pdo->prepare(
        "UPDATE tbl_user SET reset_token = :token, reset_token_exp = :exp WHERE id = :id"
    );
    $upd->execute([':token' => $token, ':exp' => $token_exp, ':id' => $user['id']]);
} catch (PDOException $e) {
    error_log("password_reset_email token store error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err']);
    exit;
}

$base_url   = defined('BASE_URL') ? BASE_URL : 'https://ees.ecoasisenergy.com';
$reset_link = $base_url . '/core/reset-password.php?token=' . urlencode($token);

try {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;
    $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.hostinger.com';
    $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $mail->SMTPAuth   = true;
    $mail->Username   = defined('SMTP_USERNAME')   ? SMTP_USERNAME   : '';
    $mail->Password   = defined('SMTP_PASSWORD')   ? SMTP_PASSWORD   : '';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    $from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@ecoasisenergy.com';
    $from_name  = defined('SMTP_FROM_NAME')  ? SMTP_FROM_NAME  : 'Ecoasis';

    $mail->setFrom($from_email, $from_name);
    $mail->addReplyTo($from_email, $from_name);
    $mail->addAddress($email);
    $mail->Subject  = 'Reset Password - Ecoasis EES Platform';
    $mail->IsHTML(true);
    $mail->Body     =
        '<b><h2>Ecoasis EES Platform - Password Reset</h2></b>' .
        '<p>Hello ' . htmlspecialchars($user['firstname'], ENT_QUOTES, 'UTF-8') . ',</p>' .
        '<p>You requested to reset your platform password.</p>' .
        '<p>If you did not request this, please ignore this email.</p>' .
        '<p><a href="' . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . '">Click here to reset your password</a></p>' .
        '<p>This link will expire in 24 hours.</p>';

    if ($mail->send()) {
        logSecurityEvent('password_reset_email_sent', ['email' => $email], 'INFO');
        ob_end_clean();
        echo json_encode(['statusCode' => 'ok']);
    } else {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        ob_end_clean();
        echo json_encode(['statusCode' => 'Err']);
    }
} catch (\Exception $e) {
    error_log("password_reset_email error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err']);
}
