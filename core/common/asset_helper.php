<?php
/**
 * Asset Helper
 * Cache-busting (mtime-based) and version helpers for local static assets.
 */

function getAppVersion(): string {
    static $version = null;
    if ($version !== null) return $version;

    $version      = '1.0.0';
    $version_file = __DIR__ . '/../../version.json';

    if (file_exists($version_file)) {
        $data = json_decode(file_get_contents($version_file), true);
        if (isset($data['version'])) {
            $version = $data['version'];
        }
    }

    return $version;
}

/**
 * Return a ?t=<mtime> cache-busting string for a local asset.
 * $file_path is relative to core/ (e.g. "assets/css/pages/dashboard.css").
 */
function getAssetVersion(string $file_path): string {
    $file_path = ltrim($file_path, '/');
    $full_path = __DIR__ . '/../' . $file_path;

    return file_exists($full_path)
        ? '?t=' . filemtime($full_path)
        : '?t=' . time();
}

/**
 * Render a <script> tag with cache busting.
 * $src is relative to core/ (e.g. "assets/js/pages/login.js").
 */
function assetScriptTag(string $src, array $attributes = []): string {
    $versioned = $src . getAssetVersion($src);
    $attrs     = _buildAttrs($attributes);
    return '<script src="' . htmlspecialchars($versioned, ENT_QUOTES, 'UTF-8') . '"' . $attrs . '></script>';
}

/**
 * Render a <link rel="stylesheet"> tag with cache busting.
 * $href is relative to core/ (e.g. "assets/css/pages/login.css").
 */
function assetCssTag(string $href, array $attributes = []): string {
    $versioned = $href . getAssetVersion($href);
    $attrs     = _buildAttrs($attributes);
    return '<link rel="stylesheet" href="' . htmlspecialchars($versioned, ENT_QUOTES, 'UTF-8') . '"' . $attrs . '>';
}

/** @internal */
function _buildAttrs(array $attributes): string {
    $out = '';
    foreach ($attributes as $k => $v) {
        $out .= ' ' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"';
    }
    return $out;
}

// Aliases
function scriptTag(string $src,  array $attributes = []): string { return assetScriptTag($src,  $attributes); }
function cssTag(string $href,    array $attributes = []): string { return assetCssTag($href,    $attributes); }
