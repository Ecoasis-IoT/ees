<?php
/**
 * EES Main Configuration File
 * Multi-database PDO factory with environment-variable based credentials.
 * Backward-compatible: exposes $admin_link / $pdo aliases for legacy pages.
 */

// =====================================================
// Environment Loader
// =====================================================
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found. Please copy .env.example to .env and configure it.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (preg_match('/^"(.*)"$/s', $value, $m)) {
                $value = $m[1];
            } elseif (preg_match("/^'(.*)'$/s", $value, $m)) {
                $value = $m[1];
            }
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

$rootPath = dirname(__FILE__);
loadEnv($rootPath . '/.env');

// =====================================================
// Application Constants
// =====================================================
define('TIMEZONE',          $_ENV['TIMEZONE']          ?? 'Indian/Mauritius');
define('BASE_URL',          $_ENV['BASE_URL']           ?? '');
define('SESSION_LIFETIME',  intval($_ENV['SESSION_LIFETIME'] ?? 14400));
define('ENVIRONMENT',       $_ENV['ENVIRONMENT']        ?? 'production');

/**
 * Strip a trailing .php from the path part only (before ?query or #fragment).
 * Relative URLs only — no regex, avoids delimiter edge cases.
 */
function ees_url_path(string $path): string {
    $qpos = strpos($path, '?');
    $hpos = strpos($path, '#');
    $end  = strlen($path);
    if ($qpos !== false) {
        $end = min($end, $qpos);
    }
    if ($hpos !== false) {
        $end = min($end, $hpos);
    }
    $base   = substr($path, 0, $end);
    $suffix = substr($path, $end);
    if (strlen($base) >= 4 && substr($base, -4) === '.php') {
        $base = substr($base, 0, -4);
    }
    return $base . $suffix;
}

/**
 * Web path prefix when the app is served under /core/ (e.g. test host without root rewrite).
 * Set EES_URL_PREFIX=/core in .env to override; otherwise inferred from SCRIPT_NAME.
 */
function ees_url_prefix(): string {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $env = trim((string)($_ENV['EES_URL_PREFIX'] ?? ''));
    if ($env !== '') {
        $cached = '/' . trim($env, '/');
        return $cached;
    }

    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($script, '/core/') !== false) {
        $cached = '/core';
        return $cached;
    }

    $cached = '';
    return $cached;
}

/**
 * Absolute public URL for emails and external links (respects BASE_URL + /core/ when needed).
 */
function ees_public_url(string $path): string {
    $base = rtrim((string)(defined('BASE_URL') ? BASE_URL : ''), '/');
    if ($base === '') {
        $base = 'https://ees.ecoasisenergy.com';
    }

    $prefix = ees_url_prefix();
    if ($prefix !== '' && substr($base, -strlen($prefix)) === $prefix) {
        $prefix = '';
    }

    return $base . $prefix . '/' . ltrim(ees_url_path($path), '/');
}

// HTTPS / Security headers
define('HTTPS_ENABLED',  filter_var($_ENV['HTTPS_ENABLED']  ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('CSP_ENABLED',    filter_var($_ENV['CSP_ENABLED']    ?? 'true',  FILTER_VALIDATE_BOOLEAN));
define('HSTS_ENABLED',   filter_var($_ENV['HSTS_ENABLED']   ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('HSTS_MAX_AGE',   intval($_ENV['HSTS_MAX_AGE']       ?? 31536000));
define('DISPLAY_ERRORS', filter_var($_ENV['DISPLAY_ERRORS'] ?? 'false', FILTER_VALIDATE_BOOLEAN));

// SMTP
define('SMTP_HOST',       $_ENV['SMTP_HOST']       ?? 'smtp.hostinger.com');
define('SMTP_PORT',       intval($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_USERNAME',   $_ENV['SMTP_USERNAME']   ?? '');
define('SMTP_PASSWORD',   $_ENV['SMTP_PASSWORD']   ?? '');
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? '');
define('SMTP_FROM_NAME',  $_ENV['SMTP_FROM_NAME']  ?? 'EES Platform');

// Account security
define('ACCOUNT_LOCKOUT_ATTEMPTS', intval($_ENV['ACCOUNT_LOCKOUT_ATTEMPTS'] ?? 5));
define('ACCOUNT_LOCKOUT_DURATION', intval($_ENV['ACCOUNT_LOCKOUT_DURATION'] ?? 1800));
define('LOGIN_RATE_LIMIT',         intval($_ENV['LOGIN_RATE_LIMIT']         ?? 10));
define('LOGIN_RATE_LIMIT_WINDOW',  intval($_ENV['LOGIN_RATE_LIMIT_WINDOW']  ?? 60));

// Input validation
define('MAX_EMAIL_LENGTH',          intval($_ENV['MAX_EMAIL_LENGTH']          ?? 60));
define('MAX_NAME_LENGTH',           intval($_ENV['MAX_NAME_LENGTH']           ?? 50));
define('MIN_NAME_LENGTH',           intval($_ENV['MIN_NAME_LENGTH']           ?? 1));
define('MAX_PASSWORD_LENGTH',       intval($_ENV['MAX_PASSWORD_LENGTH']       ?? 255));
define('MAX_PASSWORD_DISPLAY_LENGTH', intval($_ENV['MAX_PASSWORD_DISPLAY_LENGTH'] ?? 30));
define('MIN_PASSWORD_LENGTH',       intval($_ENV['MIN_PASSWORD_LENGTH']       ?? 8));

// CAPTCHA
define('CAPTCHA_ENABLED',            filter_var($_ENV['CAPTCHA_ENABLED']            ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('CAPTCHA_ATTEMPTS_THRESHOLD', intval($_ENV['CAPTCHA_ATTEMPTS_THRESHOLD']     ?? 3));
define('RECAPTCHA_SITE_KEY',         $_ENV['RECAPTCHA_SITE_KEY']                    ?? '');
define('RECAPTCHA_SECRET_KEY',       $_ENV['RECAPTCHA_SECRET_KEY']                  ?? '');

// Authorization
define('ADMIN_CAN_VIEW_ALL_PROFILES', filter_var($_ENV['ADMIN_CAN_VIEW_ALL_PROFILES'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('ADMIN_USERGROUP_ID',          intval($_ENV['ADMIN_USERGROUP_ID']              ?? 1));

// Registration
define('REGISTRATION_LINK_ENABLED', filter_var($_ENV['REGISTRATION_LINK_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('REGISTRATION_LINK_SECRET',  $_ENV['REGISTRATION_LINK_SECRET']              ?? '');
define('REGISTRATION_LINK_EXPIRY',  intval($_ENV['REGISTRATION_LINK_EXPIRY']       ?? 86400));

// Upload
define('MAX_UPLOAD_SIZE',        intval($_ENV['MAX_UPLOAD_SIZE']        ?? 5242880));
define('UPLOAD_ALLOWED_TYPES',   $_ENV['UPLOAD_ALLOWED_TYPES']          ?? 'image/jpeg,image/png,image/gif,image/webp,application/pdf');
define('UPLOAD_DIR',             $_ENV['UPLOAD_DIR']                    ?? 'upload');
define('UPLOAD_USE_SECURE_NAMES', filter_var($_ENV['UPLOAD_USE_SECURE_NAMES'] ?? 'true', FILTER_VALIDATE_BOOLEAN));

// Password policy
define('PASSWORD_HISTORY_COUNT',    intval($_ENV['PASSWORD_HISTORY_COUNT']    ?? 5));
define('PASSWORD_EXPIRATION_DAYS',  intval($_ENV['PASSWORD_EXPIRATION_DAYS']  ?? 0));

// API rate limits
define('API_RATE_LIMIT_LOGIN_MAX',              intval($_ENV['API_RATE_LIMIT_LOGIN_MAX']              ?? 10));
define('API_RATE_LIMIT_LOGIN_WINDOW',           intval($_ENV['API_RATE_LIMIT_LOGIN_WINDOW']           ?? 60));
define('API_RATE_LIMIT_PASSWORD_RESET_MAX',     intval($_ENV['API_RATE_LIMIT_PASSWORD_RESET_MAX']     ?? 5));
define('API_RATE_LIMIT_PASSWORD_RESET_WINDOW',  intval($_ENV['API_RATE_LIMIT_PASSWORD_RESET_WINDOW']  ?? 3600));
define('API_RATE_LIMIT_REGISTRATION_MAX',       intval($_ENV['API_RATE_LIMIT_REGISTRATION_MAX']       ?? 3));
define('API_RATE_LIMIT_REGISTRATION_WINDOW',    intval($_ENV['API_RATE_LIMIT_REGISTRATION_WINDOW']    ?? 3600));
define('API_RATE_LIMIT_LIST_MAX',               intval($_ENV['API_RATE_LIMIT_LIST_MAX']               ?? 100));
define('API_RATE_LIMIT_LIST_WINDOW',            intval($_ENV['API_RATE_LIMIT_LIST_WINDOW']            ?? 60));
define('API_RATE_LIMIT_CREATE_MAX',             intval($_ENV['API_RATE_LIMIT_CREATE_MAX']             ?? 20));
define('API_RATE_LIMIT_CREATE_WINDOW',          intval($_ENV['API_RATE_LIMIT_CREATE_WINDOW']          ?? 60));
define('API_RATE_LIMIT_UPDATE_MAX',             intval($_ENV['API_RATE_LIMIT_UPDATE_MAX']             ?? 20));
define('API_RATE_LIMIT_UPDATE_WINDOW',          intval($_ENV['API_RATE_LIMIT_UPDATE_WINDOW']          ?? 60));
define('API_RATE_LIMIT_DELETE_MAX',             intval($_ENV['API_RATE_LIMIT_DELETE_MAX']             ?? 10));
define('API_RATE_LIMIT_DELETE_WINDOW',          intval($_ENV['API_RATE_LIMIT_DELETE_WINDOW']          ?? 60));
define('API_RATE_LIMIT_DEFAULT_MAX',            intval($_ENV['API_RATE_LIMIT_DEFAULT_MAX']            ?? 100));
define('API_RATE_LIMIT_DEFAULT_WINDOW',         intval($_ENV['API_RATE_LIMIT_DEFAULT_WINDOW']         ?? 60));

// Webhook
define('WEBHOOK_SECRET',            $_ENV['WEBHOOK_SECRET']                                          ?? '');
define('WEBHOOK_IP_WHITELIST',      $_ENV['WEBHOOK_IP_WHITELIST']                                    ?? '');
define('WEBHOOK_REQUIRE_SIGNATURE', filter_var($_ENV['WEBHOOK_REQUIRE_SIGNATURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('WEBHOOK_RATE_LIMIT_MAX',    intval($_ENV['WEBHOOK_RATE_LIMIT_MAX']                           ?? 100));
define('WEBHOOK_RATE_LIMIT_WINDOW', intval($_ENV['WEBHOOK_RATE_LIMIT_WINDOW']                        ?? 60));

// 2FA
define('TWO_FACTOR_ENABLED',              filter_var($_ENV['TWO_FACTOR_ENABLED']              ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('TWO_FACTOR_ISSUER',               $_ENV['TWO_FACTOR_ISSUER']                          ?? 'EES System');
define('TWO_FACTOR_REQUIRED_FOR_ADMIN',   filter_var($_ENV['TWO_FACTOR_REQUIRED_FOR_ADMIN']   ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('TWO_FACTOR_BACKUP_CODES_COUNT',   intval($_ENV['TWO_FACTOR_BACKUP_CODES_COUNT']       ?? 10));
define('TWO_FACTOR_WINDOW',               intval($_ENV['TWO_FACTOR_WINDOW']                   ?? 1));

// Branding
define('APP_NAME', $_ENV['APP_NAME'] ?? 'EES');
define('APP_LOGO', $_ENV['APP_LOGO'] ?? 'assets/images/logo_icon.png');

// Timezone
date_default_timezone_set(TIMEZONE);

// Load security headers
require_once __DIR__ . '/core/common/security_headers.php';

// =====================================================
// Multi-Database PDO Factory
// =====================================================
// Maps a short key to the corresponding .env prefix.
// Usage: getDB('admin'), getDB('phoenix'), getDB('factory'), etc.
// =====================================================

$GLOBALS['_ees_db_map'] = [
    'admin'       => 'ADMIN',
    'factory'     => 'FACTORY',
    'gob'         => 'GOB',
    'pod'         => 'POD',
    'rtm'         => 'RTM',
    'bovalon'     => 'BOVALON',
    'phoenix'     => 'PHOENIX',
    'p_catering'  => 'P_CATERING',
    'helvetia'    => 'HELVETIA',
    'home_leisure'=> 'HOME_LEISURE',
];

/**
 * Get a PDO connection for the given database key.
 * Connections are cached per-request (singleton per key).
 *
 * @param  string $key  One of: admin, factory, gob, pod, rtm, bovalon, phoenix,
 *                              p_catering, helvetia, home_leisure
 * @return PDO
 */
function getDB(string $key = 'admin'): PDO {
    if (!isset($GLOBALS['_ees_pdo_pool'])) {
        $GLOBALS['_ees_pdo_pool'] = [];
    }

    if (isset($GLOBALS['_ees_pdo_pool'][$key])) {
        return $GLOBALS['_ees_pdo_pool'][$key];
    }

    $map    = $GLOBALS['_ees_db_map'];
    $prefix = $map[$key] ?? null;

    if ($prefix === null) {
        throw new InvalidArgumentException("Unknown database key: '$key'. Valid keys: " . implode(', ', array_keys($map)));
    }

    $server   = $_ENV["{$prefix}_DB_SERVER"]   ?? 'localhost';
    $username = $_ENV["{$prefix}_DB_USERNAME"]  ?? '';
    $password = $_ENV["{$prefix}_DB_PASSWORD"]  ?? '';
    $dbname   = $_ENV["{$prefix}_DB_NAME"]      ?? '';

    try {
        $dsn = "mysql:host={$server};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];
        $pdo = new PDO($dsn, $username, $password, $options);
        $pdo->exec("SET SESSION wait_timeout = 300");
        $pdo->exec("SET SESSION interactive_timeout = 300");
        $GLOBALS['_ees_pdo_pool'][$key] = $pdo;
        return $pdo;
    } catch (PDOException $e) {
        error_log("EES DB connection failed [$key]: " . $e->getMessage());
        http_response_code(500);
        die('Database connection error. Please contact the administrator.');
    }
}

/**
 * Like getDB() but returns null instead of dying when the connection fails.
 * Use this for optional/per-site connections where a failure should be
 * handled gracefully (e.g. dashboard loops over all sites).
 */
function tryGetDB(string $key): ?PDO {
    if (isset($GLOBALS['_ees_pdo_pool'][$key])) {
        return $GLOBALS['_ees_pdo_pool'][$key];
    }
    $map    = $GLOBALS['_ees_db_map'];
    $prefix = $map[$key] ?? null;
    if ($prefix === null) return null;

    $server   = $_ENV["{$prefix}_DB_SERVER"]   ?? 'localhost';
    $username = $_ENV["{$prefix}_DB_USERNAME"]  ?? '';
    $password = $_ENV["{$prefix}_DB_PASSWORD"]  ?? '';
    $dbname   = $_ENV["{$prefix}_DB_NAME"]      ?? '';

    if ($username === '' || $dbname === '') return null;

    try {
        $dsn = "mysql:host={$server};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];
        $pdo = new PDO($dsn, $username, $password, $options);
        $pdo->exec("SET SESSION wait_timeout = 300");
        $pdo->exec("SET SESSION interactive_timeout = 300");
        $GLOBALS['_ees_pdo_pool'][$key] = $pdo;
        return $pdo;
    } catch (PDOException $e) {
        error_log("EES DB connection failed [$key]: " . $e->getMessage());
        return null;
    }
}

// =====================================================
// Default (admin) connection — available as $pdo and
// legacy aliases for pages not yet migrated to PDO.
// =====================================================
if (!isset($GLOBALS['_ees_pdo_pool']['admin'])) {
    getDB('admin');
}

$pdo        = $GLOBALS['_ees_pdo_pool']['admin'];
$admin_link = $pdo;   // legacy alias used by existing core/ pages
$link       = $pdo;   // legacy alias
$conn       = $pdo;   // legacy alias
