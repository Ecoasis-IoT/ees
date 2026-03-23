<?php
/**
 * Gateway network status email alert
 * Included from cron/gateway_status.php — $status, $str_timenow, $last_seen are provided by caller.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Load SMTP constants from root config if not already defined
if (!defined('SMTP_HOST')) {
    require_once __DIR__ . '/../../config.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$clients = ['y.baboolull@ecoasis.mu', 'd.bhagooli@ecoasis.mu'];

$from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@ecoasisenergy.com';
$from_name  = defined('SMTP_FROM_NAME')  ? SMTP_FROM_NAME  : 'Ecoasis';

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
$mail->IsHTML(true);

if ($status == '1') {
    foreach ($clients as $client) {
        $mail->clearAddresses();
        $mail->addAddress($client);
        $mail->Subject = '[PHOENIX MALL]: IoT GATEWAY ONLINE';
        $mail->Body    = '<h2>Last Seen at ' . htmlspecialchars($str_timenow, ENT_QUOTES, 'UTF-8') . '</h2>';
        $mail->send();
    }
} elseif ($status == '0') {
    foreach ($clients as $client) {
        $mail->clearAddresses();
        $mail->addAddress($client);
        $mail->Subject = '[PHOENIX MALL]: IoT GATEWAY OFFLINE';
        $mail->Body    = '<h2>Last Seen at ' . htmlspecialchars($last_seen, ENT_QUOTES, 'UTF-8') . '</h2>';
        $mail->send();
    }
}
