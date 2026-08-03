<?php
/**
 * CSRF Protection
 * Generates and validates CSRF tokens for all state-changing requests.
 */

function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $session_lifetime        = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
    $token_regeneration_time = max(1800, intval($session_lifetime * 0.5));

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > $token_regeneration_time) {
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    return $_SESSION['csrf_token'];
}

function validateCSRFToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    $session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
    if (!empty($_SESSION['id']) && isset($_SESSION['created']) && (time() - $_SESSION['created']) >= $session_lifetime) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFTokenJSON(): string {
    return json_encode(['csrf_token' => generateCSRFToken()]);
}
