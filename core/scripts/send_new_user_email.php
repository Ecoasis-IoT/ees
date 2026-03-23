<?php
/**
 * Send welcome / invitation email to a new user
 * POST: email, old_user
 * Returns JSON: { statusCode: "ok"|"Err" }
 */

ob_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid request']);
    exit;
}

// Admin only
if (empty($_SESSION['id']) || (int)($_SESSION['group_id'] ?? 0) !== (int)ADMIN_USERGROUP_ID) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Unauthorized']);
    exit;
}

// CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid CSRF token']);
    exit;
}

$new_user_email = trim($_POST['email']    ?? '');
$invited_by     = trim($_POST['old_user'] ?? '');

if (empty($new_user_email) || !filter_var($new_user_email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err', 'message' => 'Invalid email address']);
    exit;
}

$base_url   = defined('BASE_URL') ? BASE_URL : 'https://ees.ecoasisenergy.com';
$from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@ecoasisenergy.com';
$from_name  = defined('SMTP_FROM_NAME')  ? SMTP_FROM_NAME  : 'Ecoasis';

try {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;
    $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.hostinger.com';
    $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $mail->SMTPAuth   = true;
    $mail->Username   = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
    $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom($from_email, $from_name);
    $mail->addReplyTo($from_email, $from_name);
    $mail->addAddress($new_user_email);

    $mail->Subject = 'Register - Ecoasis PV Monitoring Platform';
    $mail->IsHTML(true);
    $mail->Body =
        '<b><h2>Welcome to Ecoasis PV Monitoring Platform</h2></b>' .
        '<p>' . htmlspecialchars($invited_by, ENT_QUOTES, 'UTF-8') . ' wants to add you as a new user.</p>' .
        '<p>If you received this message by mistake, please ignore this email.</p>' .
        '<p>If you are the intended recipient, please register here: ' .
        '<a href="' . $base_url . '/core/register.php">Click Here to Register</a></p>';

    if ($mail->send()) {
        ob_end_clean();
        echo json_encode(['statusCode' => 'ok']);
    } else {
        error_log("send_new_user_email PHPMailer error: " . $mail->ErrorInfo);
        ob_end_clean();
        echo json_encode(['statusCode' => 'Err']);
    }
} catch (\Exception $e) {
    error_log("send_new_user_email error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['statusCode' => 'Err']);
}
