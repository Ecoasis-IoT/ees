<?php
/**
 * Authentication and Session Guard
 * Include at the top of every protected page under core/.
 *
 * Redirect target: login.php (relative; resolves to core/login.php from any page under core/).
 */

if (session_status() === PHP_SESSION_NONE) {
    $https_enabled = defined('HTTPS_ENABLED') ? HTTPS_ENABLED : false;

    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure',   $https_enabled ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_lifetime', 0);

    session_start();

    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created']       = time();
        $_SESSION['last_activity'] = time();
    } elseif (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
        // Regenerate session ID every 30 minutes to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
    } else {
        $_SESSION['last_activity'] = time();
    }
}

$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;

if (!isset($_SESSION['id']) || (time() - $_SESSION['created']) >= $session_lifetime) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    die();
}

$_SESSION['last_activity'] = time();
