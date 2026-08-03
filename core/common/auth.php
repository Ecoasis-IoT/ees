<?php
/**
 * Authentication and Session Guard
 * Include at the top of every protected page under core/.
 *
 * Redirect target: extensionless login URL (see .htaccess_production).
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once __DIR__ . '/session_cookie_config.php';

if (session_status() === PHP_SESSION_NONE) {
    applySessionCookieConfig();
    session_start();

    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created']       = time();
        $_SESSION['last_activity'] = time();
    } elseif ((time() - ($_SESSION['last_activity'] ?? time())) > 1800) {
        // Regenerate session ID every 30 minutes of activity to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
    }
}

$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;

// Expire on inactivity: last_activity not updated within SESSION_LIFETIME seconds
if (!isset($_SESSION['id']) || (time() - ($_SESSION['last_activity'] ?? 0)) >= $session_lifetime) {
    $_SESSION = [];
    session_destroy();
    // Return JSON for AJAX requests; redirect for normal page loads
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'Err', 'message' => 'Session expired. Please log in again.']);
    } else {
        header('Location: ' . ees_url_path('login.php'));
    }
    die();
}

$_SESSION['last_activity'] = time();

require_once __DIR__ . '/audit_logging.php';
ees_audit_log_page_view();

require_once __DIR__ . '/user_notifications.php';
if (!empty($_SESSION['id'])) {
    $pw_sync_key = 'notif_password_sync';
    if (empty($_SESSION[$pw_sync_key]) || (time() - (int)$_SESSION[$pw_sync_key]) > 3600) {
        ees_sync_password_expiry_notification((int)$_SESSION['id']);
        $_SESSION[$pw_sync_key] = time();
    }
}
