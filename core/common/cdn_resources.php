<?php
/**
 * CDN Resources Helper
 * Centralised CDN includes with version-pinning and optional SRI hashes.
 * All versions configurable via .env.
 */

if (!defined('JQUERY_VERSION'))         define('JQUERY_VERSION',         $_ENV['JQUERY_VERSION']         ?? '3.7.1');
if (!defined('SWEETALERT2_VERSION'))    define('SWEETALERT2_VERSION',    $_ENV['SWEETALERT2_VERSION']    ?? '11.11.0');
if (!defined('FONTAWESOME_VERSION'))    define('FONTAWESOME_VERSION',    $_ENV['FONTAWESOME_VERSION']    ?? '6.5.1');
if (!defined('JQUERY_SRI_HASH'))        define('JQUERY_SRI_HASH',        $_ENV['JQUERY_SRI_HASH']        ?? 'sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==');
if (!defined('SWEETALERT2_JS_SRI_HASH'))  define('SWEETALERT2_JS_SRI_HASH',  $_ENV['SWEETALERT2_JS_SRI_HASH']  ?? '');
if (!defined('SWEETALERT2_CSS_SRI_HASH')) define('SWEETALERT2_CSS_SRI_HASH', $_ENV['SWEETALERT2_CSS_SRI_HASH'] ?? '');
if (!defined('FONTAWESOME_SRI_HASH'))   define('FONTAWESOME_SRI_HASH',   $_ENV['FONTAWESOME_SRI_HASH']   ?? '');
if (!defined('CDN_CDNJS_URL'))          define('CDN_CDNJS_URL',          $_ENV['CDN_CDNJS_URL']          ?? 'https://cdnjs.cloudflare.com');
if (!defined('CDN_JSDELIVR_URL'))       define('CDN_JSDELIVR_URL',       $_ENV['CDN_JSDELIVR_URL']       ?? 'https://cdn.jsdelivr.net');
if (!defined('CDN_GOOGLE_FONTS_URL'))   define('CDN_GOOGLE_FONTS_URL',   $_ENV['CDN_GOOGLE_FONTS_URL']   ?? 'https://fonts.googleapis.com');

function cdnScript(string $url, string $integrity = ''): string {
    $tag = '<script src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
    if (!empty($integrity)) {
        $tag .= ' integrity="' . htmlspecialchars($integrity, ENT_QUOTES, 'UTF-8') . '" crossorigin="anonymous"';
    }
    $tag .= ' referrerpolicy="no-referrer"></script>';
    return $tag;
}

function cdnStylesheet(string $url, string $integrity = ''): string {
    $tag = '<link rel="stylesheet" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
    if (!empty($integrity)) {
        $tag .= ' integrity="' . htmlspecialchars($integrity, ENT_QUOTES, 'UTF-8') . '" crossorigin="anonymous"';
    }
    $tag .= '>';
    return $tag;
}

function getJQuery(): string {
    return cdnScript(
        CDN_CDNJS_URL . '/ajax/libs/jquery/' . JQUERY_VERSION . '/jquery.min.js',
        JQUERY_SRI_HASH
    );
}

function getSweetAlert2JS(): string {
    return cdnScript(
        CDN_JSDELIVR_URL . '/npm/sweetalert2@' . SWEETALERT2_VERSION . '/dist/sweetalert2.all.min.js',
        SWEETALERT2_JS_SRI_HASH
    );
}

function getSweetAlert2CSS(): string {
    return cdnStylesheet(
        CDN_JSDELIVR_URL . '/npm/sweetalert2@' . SWEETALERT2_VERSION . '/dist/sweetalert2.min.css',
        SWEETALERT2_CSS_SRI_HASH
    );
}

function getFontAwesome(): string {
    $version = FONTAWESOME_VERSION;
    if (version_compare($version, '6.0.0', '>=')) {
        $url      = CDN_CDNJS_URL . '/ajax/libs/font-awesome/' . $version . '/css/all.min.css';
        $sri_hash = $_ENV['FONTAWESOME_SRI_HASH'] ?? 'sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==';
    } else {
        $url      = CDN_CDNJS_URL . '/ajax/libs/font-awesome/' . $version . '/css/font-awesome.min.css';
        $sri_hash = FONTAWESOME_SRI_HASH;
    }
    return cdnStylesheet($url, $sri_hash);
}

function getGoogleFonts(string $family = 'Inter:wght@300;400;500;600;700'): string {
    return cdnStylesheet(CDN_GOOGLE_FONTS_URL . '/css2?family=' . urlencode($family) . '&display=swap');
}

function getGlobalStyles(): string {
    return '<link rel="stylesheet" href="assets/css/global-styles.css">';
}
