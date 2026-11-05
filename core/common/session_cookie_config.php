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

    $httponly = filter_var($_ENV['SESSION_COOKIE_HTTPONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    $secure   = filter_var($_ENV['SESSION_COOKIE_SECURE']   ?? 'false', FILTER_VALIDATE_BOOLEAN);
    $samesite = $_ENV['SESSION_COOKIE_SAMESITE']            ?? 'Strict';
    $strict   = filter_var($_ENV['SESSION_USE_STRICT_MODE'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    $lifetime = intval($_ENV['SESSION_COOKIE_LIFETIME']     ?? 0);
    $idle     = defined('SESSION_LIFETIME') ? (int)SESSION_LIFETIME : intval($_ENV['SESSION_LIFETIME'] ?? 14400);
    if ($idle < 300) {
        $idle = 14400;
    }
    if (!in_array($samesite, ['Strict', 'Lax', 'None'], true)) {
        $samesite = 'Lax';
    }

    // Honour HTTPS_ENABLED if SESSION_COOKIE_SECURE is not explicitly true
    if (!$secure && defined('HTTPS_ENABLED') && HTTPS_ENABLED) {
        $secure = true;
    }

    // Keep session files in the app directory. The host's default cleaner
    // (often 24 minutes, sometimes shorter) only sweeps the system temp folder.
    $savePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($savePath)) {
        @mkdir($savePath, 0700, true);
    }
    $guard = $savePath . DIRECTORY_SEPARATOR . '.htaccess';
    if (is_dir($savePath) && !is_file($guard)) {
        @file_put_contents($guard, "Require all denied\nDeny from all\n");
    }
    if (is_dir($savePath) && is_writable($savePath)) {
        ini_set('session.save_path', $savePath);
    }

    ini_set('session.gc_maxlifetime',   (string)$idle);
    ini_set('session.cookie_httponly',  $httponly ? '1' : '0');
    ini_set('session.cookie_secure',    $secure   ? '1' : '0');
    ini_set('session.cookie_samesite',  $samesite);
    ini_set('session.use_strict_mode',  $strict   ? '1' : '0');
    ini_set('session.cookie_lifetime',  (string)$lifetime);
    ini_set('session.cookie_path',      '/');

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite,
    ]);
}
