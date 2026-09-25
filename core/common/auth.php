<?php
/**
 * Authentication and Session Guard
 * Include at the top of every protected page under core/.
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once __DIR__ . '/session_cookie_config.php';

if (session_status() === PHP_SESSION_NONE) {
    applySessionCookieConfig();
    session_start();
}

$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
$script_name = basename($_SERVER['SCRIPT_NAME'] ?? '');
$twofa_pending = !empty($_SESSION['2fa_pending']);
$twofa_pending_allowed = ['verify_2fa.php', 'send_2fa_email.php', 'userlogin.php', 'login.php', 'cancel_2fa.php'];

if ($twofa_pending && !in_array($script_name, $twofa_pending_allowed, true)) {
    unset(
        $_SESSION['id'],
        $_SESSION['name'],
        $_SESSION['firstname'],
        $_SESSION['lastname'],
        $_SESSION['last_name'],
        $_SESSION['email'],
        $_SESSION['username'],
        $_SESSION['group_id'],
        $_SESSION['created']
    );
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'Err', 'message' => 'Finish two-factor authentication to continue.']);
    } else {
        header('Location: ' . ees_url_path('login.php'));
    }
    exit;
}

$logged_in = isset($_SESSION['id']) && isset($_SESSION['created']);
$expired = $logged_in && (time() - (int)($_SESSION['last_activity'] ?? $_SESSION['created'])) >= $session_lifetime;

if (!$logged_in || $expired) {
    if ($expired) {
        $_SESSION = [];
        session_destroy();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'Err', 'message' => 'Session expired. Please log in again.']);
    } else {
        header('Location: ' . ees_url_path('login.php'));
    }
    exit;
}

if (!isset($_SESSION['last_regenerated']) || (time() - (int)$_SESSION['last_regenerated']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regenerated'] = time();
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

if (isset($_SESSION['id'])) {
    require_once __DIR__ . '/two_factor_auth.php';
    $pdo = isset($pdo) && $pdo instanceof PDO ? $pdo : getDB('admin');
    if (userMustSetup2FA($pdo, (int)$_SESSION['id'])) {
        $allowed = ['profile.php', 'get_user_profile.php', 'setup_2fa.php', 'logout.php'];
        if (!in_array($script_name, $allowed, true)) {
            $is_api = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/scripts/') !== false
                || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            if ($is_api) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'statusCode' => 'Err',
                    'message' => 'Set up two-factor authentication before continuing.',
                ]);
                exit;
            }
            header('Location: ' . ees_url_path('profile.php'));
            exit;
        }
    }
}
