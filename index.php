<?php
/**
 * Root entry point — routes authenticated users to dashboard, others to login.
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;

// Use last_activity for consistency with auth.php (idle timeout, not absolute timeout)
if (isset($_SESSION['id']) && (time() - ($_SESSION['last_activity'] ?? 0)) < $session_lifetime) {
    header('Location: ' . ees_url_path('core/dashboard.php'));
} else {
    header('Location: ' . ees_url_path('core/login.php'));
}
exit;
