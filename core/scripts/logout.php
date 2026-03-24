<?php
/**
 * Logout Handler
 * Destroys the session and redirects to login.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/security_logging.php';

$user_id = $_SESSION['id'] ?? null;
logSecurityEvent('logout', ['user_id' => $user_id], 'INFO');

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: ../login.php');
exit;
