<?php
/**
 * Two-Factor Authentication (2FA) Helper
 * TOTP, backup codes, emailed codes, and “must set up” checks.
 */

if (defined('EES_TWO_FACTOR_AUTH_LOADED')) {
    return;
}
define('EES_TWO_FACTOR_AUTH_LOADED', true);

if (!function_exists('getDB')) {
    require_once __DIR__ . '/../../config.php';
}

$two_factor_enabled = defined('TWO_FACTOR_ENABLED') ? TWO_FACTOR_ENABLED : filter_var($_ENV['TWO_FACTOR_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$two_factor_issuer = defined('TWO_FACTOR_ISSUER') ? TWO_FACTOR_ISSUER : ($_ENV['TWO_FACTOR_ISSUER'] ?? 'EES System');
$two_factor_required_for_admin = defined('TWO_FACTOR_REQUIRED_FOR_ADMIN') ? TWO_FACTOR_REQUIRED_FOR_ADMIN : filter_var($_ENV['TWO_FACTOR_REQUIRED_FOR_ADMIN'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$two_factor_required_for_all = defined('TWO_FACTOR_REQUIRED_FOR_ALL') ? TWO_FACTOR_REQUIRED_FOR_ALL : filter_var($_ENV['TWO_FACTOR_REQUIRED_FOR_ALL'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$two_factor_backup_codes_count = defined('TWO_FACTOR_BACKUP_CODES_COUNT') ? TWO_FACTOR_BACKUP_CODES_COUNT : intval($_ENV['TWO_FACTOR_BACKUP_CODES_COUNT'] ?? 10);
$two_factor_window = defined('TWO_FACTOR_WINDOW') ? TWO_FACTOR_WINDOW : intval($_ENV['TWO_FACTOR_WINDOW'] ?? 1);

function is2FAEnabled() {
    global $two_factor_enabled;
    return $two_factor_enabled;
}

function userHas2FAEnabled($pdo, $user_id) {
    try {
        $query = "SELECT enabled FROM tbl_user_2fa WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['enabled'] == 1;
    } catch (PDOException $e) {
        error_log("2FA check error: " . $e->getMessage());
        return false;
    }
}

function hexToBase32($hex) {
    $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $hex = strtoupper($hex);
    $bin = '';
    for ($i = 0; $i < strlen($hex); $i += 2) {
        $bin .= str_pad(decbin(hexdec(substr($hex, $i, 2))), 8, '0', STR_PAD_LEFT);
    }
    $base32 = '';
    $binLength = strlen($bin);
    for ($i = 0; $i < $binLength; $i += 5) {
        $chunk = substr($bin, $i, 5);
        $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
        $base32 .= $base32Chars[bindec($chunk)];
    }
    return $base32;
}

function generate2FASecret() {
    return bin2hex(random_bytes(16));
}

function generateTOTPCode($secret, $time_step = null) {
    if ($time_step === null) {
        $time_step = floor(time() / 30);
    }
    $key = hex2bin($secret);
    $time = pack('N*', 0) . pack('N*', $time_step);
    $hash = hash_hmac('sha1', $time, $key, true);
    $offset = ord($hash[19]) & 0x0f;
    $code = (
        ((ord($hash[$offset + 0]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff)
    ) % 1000000;
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

function verifyTOTPCode($secret, $code, $window = null) {
    global $two_factor_window;
    if ($window === null) {
        $window = $two_factor_window;
    }
    $time_step = floor(time() / 30);
    for ($i = -$window; $i <= $window; $i++) {
        $expected_code = generateTOTPCode($secret, $time_step + $i);
        if (hash_equals($expected_code, $code)) {
            return true;
        }
    }
    return false;
}

function generate2FAQRCodeData($email, $secret) {
    global $two_factor_issuer;
    $base32Secret = hexToBase32($secret);
    $issuer = rawurlencode($two_factor_issuer);
    $label = rawurlencode($two_factor_issuer . ':' . $email);
    $otpauth_url = "otpauth://totp/{$label}?secret={$base32Secret}&issuer={$issuer}";
    return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth_url);
}

function generateBackupCodes($count = null) {
    global $two_factor_backup_codes_count;
    if ($count === null) {
        $count = $two_factor_backup_codes_count;
    }
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
        $codes[] = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }
    return $codes;
}

function hashBackupCode($code) {
    return hash('sha256', $code);
}

function ees_ensure_tbl_user_2fa(PDO $pdo): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `tbl_user_2fa` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `secret` varchar(128) DEFAULT NULL,
            `backup_codes` text DEFAULT NULL,
            `backup_codes_saved` text DEFAULT NULL,
            `enabled` tinyint(1) NOT NULL DEFAULT 0,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_user_2fa_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    ensure2FABackupSavedColumn($pdo);
}

function ensure2FABackupSavedColumn($pdo) {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;
    try {
        $check = $pdo->query("SHOW COLUMNS FROM tbl_user_2fa LIKE 'backup_codes_saved'");
        if ($check && !$check->fetch()) {
            $pdo->exec("ALTER TABLE tbl_user_2fa ADD COLUMN backup_codes_saved TEXT NULL");
        }
    } catch (PDOException $e) {
        $ready = false;
        error_log("2FA backup_codes_saved column check: " . $e->getMessage());
    }
}

function backupCodesKey() {
    $name = $_ENV['ADMIN_DB_NAME'] ?? (defined('ADMIN_DB_NAME') ? ADMIN_DB_NAME : 'ees');
    $pass = $_ENV['ADMIN_DB_PASSWORD'] ?? (defined('ADMIN_DB_PASSWORD') ? ADMIN_DB_PASSWORD : 'ees');
    return hash('sha256', $name . '|' . $pass, true);
}

function encryptBackupCodes(array $codes) {
    $iv = random_bytes(16);
    $cipher = openssl_encrypt(json_encode(array_values($codes)), 'AES-256-CBC', backupCodesKey(), OPENSSL_RAW_DATA, $iv);
    if ($cipher === false) {
        return null;
    }
    return base64_encode($iv . $cipher);
}

function decryptBackupCodes($stored) {
    if (empty($stored)) {
        return [];
    }
    $raw = base64_decode($stored, true);
    if ($raw === false || strlen($raw) < 17) {
        return [];
    }
    $plain = openssl_decrypt(substr($raw, 16), 'AES-256-CBC', backupCodesKey(), OPENSSL_RAW_DATA, substr($raw, 0, 16));
    $codes = json_decode($plain ?: '', true);
    return is_array($codes) ? array_values($codes) : [];
}

function persistBackupCodes($pdo, $user_id, array $codes) {
    ees_ensure_tbl_user_2fa($pdo);
    $hashes = json_encode(array_map('hashBackupCode', $codes));
    $saved = encryptBackupCodes($codes);
    $stmt = $pdo->prepare("UPDATE tbl_user_2fa SET backup_codes = ?, backup_codes_saved = ? WHERE user_id = ?");
    return $stmt->execute([$hashes, $saved, $user_id]);
}

function getSavedBackupCodes($pdo, $user_id) {
    ees_ensure_tbl_user_2fa($pdo);
    $stmt = $pdo->prepare("SELECT backup_codes_saved FROM tbl_user_2fa WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? decryptBackupCodes($row['backup_codes_saved'] ?? '') : [];
}

function verifyBackupCode($stored_hashes, $code) {
    $code_hash = hashBackupCode($code);
    if (empty($stored_hashes)) {
        return false;
    }
    $hashes = json_decode($stored_hashes, true);
    if (!is_array($hashes)) {
        return false;
    }
    $index = array_search($code_hash, $hashes);
    if ($index !== false) {
        unset($hashes[$index]);
        return ['valid' => true, 'remaining_hashes' => $hashes];
    }
    return ['valid' => false, 'remaining_hashes' => $hashes];
}

function store2FASecret($pdo, $user_id, $secret, $backup_codes = null) {
    try {
        ees_ensure_tbl_user_2fa($pdo);
        $backup_codes_hashed = null;
        $backup_codes_saved = null;
        if ($backup_codes !== null && is_array($backup_codes)) {
            $backup_codes_hashed = json_encode(array_map('hashBackupCode', $backup_codes));
            $backup_codes_saved = encryptBackupCodes($backup_codes);
        }
        $check_stmt = $pdo->prepare("SELECT id FROM tbl_user_2fa WHERE user_id = ?");
        $check_stmt->execute([$user_id]);
        if ($check_stmt->fetch()) {
            $stmt = $pdo->prepare(
                "UPDATE tbl_user_2fa SET secret = ?, backup_codes = ?, backup_codes_saved = ?, enabled = 0, updated_at = NOW() WHERE user_id = ?"
            );
            $stmt->execute([$secret, $backup_codes_hashed, $backup_codes_saved, $user_id]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO tbl_user_2fa (user_id, secret, backup_codes, backup_codes_saved, enabled) VALUES (?, ?, ?, ?, 0)"
            );
            $stmt->execute([$user_id, $secret, $backup_codes_hashed, $backup_codes_saved]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("2FA secret storage error: " . $e->getMessage());
        return false;
    }
}

function enable2FA($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("UPDATE tbl_user_2fa SET enabled = 1, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("2FA enable error: " . $e->getMessage());
        return false;
    }
}

function disable2FA($pdo, $user_id) {
    try {
        ees_ensure_tbl_user_2fa($pdo);
        $stmt = $pdo->prepare("UPDATE tbl_user_2fa SET enabled = 0, backup_codes = NULL, backup_codes_saved = NULL, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("2FA disable error: " . $e->getMessage());
        return false;
    }
}

function get2FAStatus($pdo, $user_id) {
    try {
        ees_ensure_tbl_user_2fa($pdo);
        $stmt = $pdo->prepare("SELECT enabled, secret, backup_codes FROM tbl_user_2fa WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $backup_codes_count = 0;
            if (!empty($result['backup_codes'])) {
                $hashes = json_decode($result['backup_codes'], true);
                if (is_array($hashes)) {
                    $backup_codes_count = count($hashes);
                }
            }
            return [
                'enabled' => $result['enabled'] == 1,
                'has_secret' => !empty($result['secret']),
                'backup_codes_count' => $backup_codes_count,
            ];
        }
        return ['enabled' => false, 'has_secret' => false, 'backup_codes_count' => 0];
    } catch (PDOException $e) {
        error_log("2FA status check error: " . $e->getMessage());
        return ['enabled' => false, 'has_secret' => false, 'backup_codes_count' => 0];
    }
}

function verifyUserTOTPCode($pdo, $user_id, $code) {
    try {
        $stmt = $pdo->prepare("SELECT secret FROM tbl_user_2fa WHERE user_id = ? AND enabled = 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result || empty($result['secret'])) {
            return false;
        }
        return verifyTOTPCode($result['secret'], $code);
    } catch (PDOException $e) {
        error_log("2FA verification error: " . $e->getMessage());
        return false;
    }
}

function verifyUserBackupCode($pdo, $user_id, $code) {
    try {
        $stmt = $pdo->prepare("SELECT backup_codes FROM tbl_user_2fa WHERE user_id = ? AND enabled = 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result || empty($result['backup_codes'])) {
            return false;
        }
        $verification = verifyBackupCode($result['backup_codes'], $code);
        if (!empty($verification['valid'])) {
            $remaining_hashes = $verification['remaining_hashes'];
            $updated_backup_codes = !empty($remaining_hashes) ? json_encode(array_values($remaining_hashes)) : null;
            ees_ensure_tbl_user_2fa($pdo);
            $saved = array_values(array_filter(getSavedBackupCodes($pdo, $user_id), function ($item) use ($code) {
                return (string) $item !== (string) $code;
            }));
            $updated_saved = !empty($saved) ? encryptBackupCodes($saved) : null;
            $update_stmt = $pdo->prepare("UPDATE tbl_user_2fa SET backup_codes = ?, backup_codes_saved = ? WHERE user_id = ?");
            $update_stmt->execute([$updated_backup_codes, $updated_saved, $user_id]);
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("2FA backup code verification error: " . $e->getMessage());
        return false;
    }
}

function mask2FAEmail($email) {
    $email = trim((string) $email);
    $parts = explode('@', $email);
    if (count($parts) !== 2 || $parts[0] === '') {
        return 'your email address';
    }
    $name = $parts[0];
    $hidden = max(1, strlen($name) - 1);
    return substr($name, 0, 1) . str_repeat('*', $hidden) . '@' . $parts[1];
}

function store2FAEmailCode($code) {
    $salt = bin2hex(random_bytes(16));
    $_SESSION['2fa_email_code_hash'] = hash_hmac('sha256', $code, $salt);
    $_SESSION['2fa_email_code_salt'] = $salt;
    $_SESSION['2fa_email_code_expires'] = time() + 900;
    $_SESSION['2fa_email_code_attempts'] = 0;
    $_SESSION['2fa_created'] = time();
}

function verify2FAEmailCode($code) {
    if (empty($_SESSION['2fa_email_code_hash']) || empty($_SESSION['2fa_email_code_salt'])) {
        return false;
    }
    if (time() > intval($_SESSION['2fa_email_code_expires'] ?? 0)) {
        return false;
    }
    $attempts = intval($_SESSION['2fa_email_code_attempts'] ?? 0);
    if ($attempts >= 6) {
        unset(
            $_SESSION['2fa_email_code_hash'],
            $_SESSION['2fa_email_code_salt'],
            $_SESSION['2fa_email_code_expires'],
            $_SESSION['2fa_email_code_attempts']
        );
        return false;
    }
    $hash = hash_hmac('sha256', $code, $_SESSION['2fa_email_code_salt']);
    if (!hash_equals($_SESSION['2fa_email_code_hash'], $hash)) {
        $_SESSION['2fa_email_code_attempts'] = $attempts + 1;
        return false;
    }
    unset(
        $_SESSION['2fa_email_code_hash'],
        $_SESSION['2fa_email_code_salt'],
        $_SESSION['2fa_email_code_expires'],
        $_SESSION['2fa_email_code_attempts']
    );
    return true;
}

function is2FARequiredForUser($pdo, $user_id) {
    global $two_factor_required_for_admin;
    if (!$two_factor_required_for_admin) {
        return false;
    }
    try {
        $admin_usergroup_id = defined('ADMIN_USERGROUP_ID') ? ADMIN_USERGROUP_ID : 1;
        $stmt = $pdo->prepare("SELECT group_id FROM tbl_user WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && (int)$result['group_id'] === (int)$admin_usergroup_id;
    } catch (PDOException $e) {
        error_log("2FA requirement check error: " . $e->getMessage());
        return false;
    }
}

function is2FAMandatoryForUser($pdo, $user_id) {
    global $two_factor_required_for_all;
    if (!is2FAEnabled()) {
        return false;
    }
    if ($two_factor_required_for_all) {
        return true;
    }
    return is2FARequiredForUser($pdo, $user_id);
}

function userMustSetup2FA($pdo, $user_id) {
    if (!$pdo || !is2FAMandatoryForUser($pdo, $user_id)) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("SELECT enabled FROM tbl_user_2fa WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !$result || intval($result['enabled']) !== 1;
    } catch (PDOException $e) {
        error_log("2FA setup requirement error: " . $e->getMessage());
        return false;
    }
}
