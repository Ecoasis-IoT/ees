<?php
/**
 * Security Headers Helper
 * Sets HTTP security headers based on environment configuration.
 * Loaded automatically by config.php.
 */

if (!headers_sent()) {
    $environment  = $_ENV['ENVIRONMENT']  ?? 'production';
    $https_enabled = filter_var($_ENV['HTTPS_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
    $csp_enabled   = filter_var($_ENV['CSP_ENABLED']   ?? 'true',  FILTER_VALIDATE_BOOLEAN);
    $hsts_enabled  = filter_var($_ENV['HSTS_ENABLED']  ?? 'false', FILTER_VALIDATE_BOOLEAN);
    $hsts_max_age  = intval($_ENV['HSTS_MAX_AGE']      ?? 31536000);

    // Content Security Policy — only in production when enabled
    if ($csp_enabled && $environment === 'production') {
        // Keep in sync with every external host referenced by pages (scan for src=/href= https://)
        $cdn_hosts = "https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdn.datatables.net " .
                     "https://unpkg.com https://ajax.googleapis.com https://stackpath.bootstrapcdn.com";
        $mapbox    = "https://api.mapbox.com";

        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$cdn_hosts} {$mapbox}; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com {$cdn_hosts} {$mapbox}; " .
               "font-src 'self' data: https://fonts.gstatic.com {$cdn_hosts}; " .
               "img-src 'self' data: blob: https:; " .
               "connect-src 'self' {$cdn_hosts} {$mapbox} https://events.mapbox.com; " .
               "worker-src 'self' blob:; " .
               "child-src 'self' blob:; " .
               "frame-ancestors 'self';";
        header("Content-Security-Policy: " . $csp);
    }

    // HSTS — only when HTTPS is active
    if ($hsts_enabled && $https_enabled) {
        header("Strict-Transport-Security: max-age={$hsts_max_age}; includeSubDomains; preload");
    }

    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

function setErrorReporting() {
    $environment   = $_ENV['ENVIRONMENT']   ?? 'production';
    $display_errors = filter_var($_ENV['DISPLAY_ERRORS'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

    if ($environment === 'development' && $display_errors) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
    }
}

setErrorReporting();
