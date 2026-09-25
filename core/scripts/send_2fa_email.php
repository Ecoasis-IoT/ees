<?php
/**
 * Email a 6-digit sign-in code during a pending 2FA login.
 * Never returns the code in JSON.
 */

ob_start();

header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/security_logging.php';
require_once __DIR__ . '/../common/auth_security.php';
require_once __DIR__ . '/../common/two_factor_auth.php';
require_once __DIR__ . '/../common/session_cookie_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

applySessionCookieConfig();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ees_2fa_email_json(string $status, string $message): void {
    ob_end_clean();
    echo json_encode(['statusCode' => $status, 'message' => $message]);
    exit;
}

if (empty($_SESSION['2fa_pending'])) {
    $csrf_token = trim($_POST['csrf_token'] ?? '');
    if ($csrf_token === '' || !validateCSRFToken($csrf_token)) {
        ees_2fa_email_json('Err', 'Your session has expired. Please sign in again.');
    }
}

if (empty($_SESSION['2fa_pending']) || empty($_SESSION['2fa_user_id']) || empty($_SESSION['2fa_email'])) {
    ees_2fa_email_json('Err', 'Sign in again before requesting an email code.');
}

$pending_limit = !empty($_SESSION['2fa_email_code_expires']) ? 900 : 300;
if (!empty($_SESSION['2fa_created']) && (time() - (int)$_SESSION['2fa_created']) > $pending_limit) {
    ees_clear_pending_2fa();
    ees_2fa_email_json('Err', 'Sign-in expired. Please login again.');
}

$now = time();
$last_sent = intval($_SESSION['2fa_email_sent_at'] ?? 0);
if ($last_sent > 0 && ($now - $last_sent) < 60) {
    $wait = 60 - ($now - $last_sent);
    ees_2fa_email_json('Err', 'Please wait ' . $wait . ' seconds before requesting another code.');
}

$send_count = intval($_SESSION['2fa_email_send_count'] ?? 0);
if ($send_count >= 3) {
    ees_2fa_email_json('Err', 'Too many email codes were requested. Please login again.');
}

$email = $_SESSION['2fa_email'];
$code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$app_name = defined('APP_NAME') ? APP_NAME : 'EES Platform';
$template_path = __DIR__ . '/../assets/template/two_factor_email.php';
$template = is_file($template_path) ? file_get_contents($template_path) : '';
if ($template === false || $template === '') {
    $template = '<p>Your sign-in code is <strong>{$code}</strong>. It expires in 15 minutes.</p>';
}
$email_body = str_replace(
    ['{$code}', '{$app_name}'],
    [$code, htmlspecialchars($app_name, ENT_QUOTES, 'UTF-8')],
    $template
);

try {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.hostinger.com';
    $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $mail->SMTPAuth = true;
    $mail->Username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
    $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ];
    $mail->setFrom(
        defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@ecoasisenergy.com',
        defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'EES Platform'
    );
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->addAddress($email);
    $mail->Subject = $app_name . ' sign-in code';
    $mail->Body = $email_body;

    if (!$mail->send()) {
        logSecurityEvent('2fa_email_code_failed', [
            'user_id' => $_SESSION['2fa_user_id'],
            'email' => $email,
        ], 'ERROR');
        ees_2fa_email_json('Err', 'Could not send the email. Try again in a moment.');
    }
} catch (Exception $e) {
    error_log('2FA email code error: ' . $e->getMessage());
    logSecurityEvent('2fa_email_code_failed', [
        'user_id' => $_SESSION['2fa_user_id'],
        'email' => $email,
    ], 'ERROR');
    ees_2fa_email_json('Err', 'Could not send the email. Try again in a moment.');
}

store2FAEmailCode($code);
$_SESSION['2fa_email_sent_at'] = $now;
$_SESSION['2fa_email_send_count'] = $send_count + 1;

logSecurityEvent('2fa_email_code_sent', [
    'user_id' => $_SESSION['2fa_user_id'],
    'email' => $email,
], 'INFO');

ees_2fa_email_json('success', 'A 6-digit code was sent to ' . mask2FAEmail($email) . '. It expires in 15 minutes.');
