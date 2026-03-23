<?php
/**
 * Root entry point — routes authenticated users to dashboard, others to login.
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;

if (isset($_SESSION['id']) && (time() - ($_SESSION['created'] ?? 0)) < $session_lifetime) {
    header('Location: core/dashboard.php');
} else {
    header('Location: core/login.php');
}
exit;
