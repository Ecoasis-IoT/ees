<?php
/**
 * Session Cookie Configuration
 * Call applySessionCookieConfig() before session_start() in login/auth scripts
 * to ensure consistent cookie settings across all entry points.
 */

function applySessionCookieConfig(): void {
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $httponly   = filter_var($_ENV['SESSION_COOKIE_HTTPONLY']  ?? 'true',   FILTER_VALIDATE_BOOLEAN);
    $secure     = filter_var($_ENV['SESSION_COOKIE_SECURE']    ?? 'false',  FILTER_VALIDATE_BOOLEAN);
    $samesite   = $_ENV['SESSION_COOKIE_SAMESITE']             ?? 'Strict';
    $strict     = filter_var($_ENV['SESSION_USE_STRICT_MODE']  ?? 'true',   FILTER_VALIDATE_BOOLEAN);
    $lifetime   = intval($_ENV['SESSION_COOKIE_LIFETIME']      ?? 0);

    // Honour HTTPS_ENABLED if SESSION_COOKIE_SECURE is not explicitly true
    if (!$secure && defined('HTTPS_ENABLED') && HTTPS_ENABLED) {
        $secure = true;
    }

    ini_set('session.cookie_httponly',  $httponly  ? 1 : 0);
    ini_set('session.cookie_secure',    $secure    ? 1 : 0);
    ini_set('session.cookie_samesite',  $samesite);
    ini_set('session.use_strict_mode',  $strict    ? 1 : 0);
    ini_set('session.cookie_lifetime',  $lifetime);
}
