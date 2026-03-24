<?php
/**
 * Two-Factor Authentication (2FA) Helper
 * Provides TOTP (Time-based One-Time Password) functionality
 * All configuration from .env file (no hardcoded values)
 */

if (!function_exists('getDB')) {
    require_once __DIR__ . '/../../config.php';
}

// Get 2FA configuration from .env
$two_factor_enabled = filter_var($_ENV['TWO_FACTOR_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$two_factor_issuer = $_ENV['TWO_FACTOR_ISSUER'] ?? 'EES PV Monitor';
$two_factor_required_for_admin = filter_var($_ENV['TWO_FACTOR_REQUIRED_FOR_ADMIN'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
$two_factor_backup_codes_count = intval($_ENV['TWO_FACTOR_BACKUP_CODES_COUNT'] ?? 10);
$two_factor_window = intval($_ENV['TWO_FACTOR_WINDOW'] ?? 1); // Time window for TOTP validation (in 30-second periods)

/**
 * Check if 2FA is enabled globally
 */
function is2FAEnabled() {
    global $two_factor_enabled;
    return $two_factor_enabled;
}

/**
 * Check if user has 2FA enabled
 */
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

/**
 * Convert hex string to Base32
 */
function hexToBase32($hex) {
    $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $hex = strtoupper($hex);
    $bin = '';
    
    // Convert hex to binary
    for ($i = 0; $i < strlen($hex); $i += 2) {
        $bin .= str_pad(decbin(hexdec(substr($hex, $i, 2))), 8, '0', STR_PAD_LEFT);
    }
    
    // Convert binary to Base32
    $base32 = '';
    $binLength = strlen($bin);
    for ($i = 0; $i < $binLength; $i += 5) {
        $chunk = substr($bin, $i, 5);
        $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
        $base32 .= $base32Chars[bindec($chunk)];
    }
    
    return $base32;
}

/**
 * Generate a random secret for TOTP (16 bytes = 32 hex characters)
 * Returns hex string for internal use, but QR codes need Base32
 */
function generate2FASecret() {
    return bin2hex(random_bytes(16));
}

/**
 * Generate TOTP code from secret
 * Uses RFC 6238 standard (compatible with Google Authenticator, Authy, etc.)
 */
function generateTOTPCode($secret, $time_step = null) {
    if ($time_step === null) {
        $time_step = floor(time() / 30);
    }
    
    // Decode secret from hex
    $key = hex2bin($secret);
    
    // Pack time step as 8-byte big-endian integer
    $time = pack('N*', 0) . pack('N*', $time_step);
    
    // Generate HMAC-SHA1 hash
    $hash = hash_hmac('sha1', $time, $key, true);
    
    // Get dynamic truncation offset
    $offset = ord($hash[19]) & 0x0f;
    
    // Extract 4 bytes starting at offset
    $code = (
        ((ord($hash[$offset + 0]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff)
    ) % 1000000;
    
    // Pad to 6 digits
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

/**
 * Verify TOTP code
 * Allows for time drift (checks current time ± window periods)
 */
function verifyTOTPCode($secret, $code, $window = null) {
    global $two_factor_window;
    
    if ($window === null) {
        $window = $two_factor_window;
    }
    
    $time_step = floor(time() / 30);
    
    // Check current time step and adjacent steps (for time drift)
    for ($i = -$window; $i <= $window; $i++) {
        $expected_code = generateTOTPCode($secret, $time_step + $i);
        if (hash_equals($expected_code, $code)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Generate QR code data URI for 2FA setup
 * Format: otpauth://totp/{label}?secret={base32secret}&issuer={issuer}
 * Note: Secret must be in Base32 format for QR codes
 */
function generate2FAQRCodeData($email, $secret) {
    global $two_factor_issuer;
    
    // Convert hex secret to Base32 (required for QR codes)
    $base32Secret = hexToBase32($secret);
    
    // Format: otpauth://totp/{issuer}:{email}?secret={base32secret}&issuer={issuer}
    $issuer = urlencode($two_factor_issuer);
    $label = urlencode($two_factor_issuer . ':' . $email);
    
    $otpauth_url = "otpauth://totp/{$label}?secret={$base32Secret}&issuer={$issuer}";
    
    // Use QR Server API for QR code generation
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth_url);
    
    return $qr_url;
}

/**
 * Generate backup codes for 2FA
 */
function generateBackupCodes($count = null) {
    global $two_factor_backup_codes_count;
    
    if ($count === null) {
        $count = $two_factor_backup_codes_count;
    }
    
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
        // Generate 8-digit backup code
        $code = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $codes[] = $code;
    }
    
    return $codes;
}

/**
 * Hash backup code for storage
 */
function hashBackupCode($code) {
    return hash('sha256', $code);
}

/**
 * Verify backup code
 */
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
        // Remove used backup code
        unset($hashes[$index]);
        return ['valid' => true, 'remaining_hashes' => $hashes];
    }
    
    return ['valid' => false, 'remaining_hashes' => $hashes];
}

/**
 * Store 2FA secret for user
 */
function store2FASecret($pdo, $user_id, $secret, $backup_codes = null) {
    try {
        // Hash backup codes if provided
        $backup_codes_hashed = null;
        if ($backup_codes !== null && is_array($backup_codes)) {
            $hashed_codes = array_map('hashBackupCode', $backup_codes);
            $backup_codes_hashed = json_encode($hashed_codes);
        }
        
        // Check if record exists
        $check_query = "SELECT id FROM tbl_user_2fa WHERE user_id = ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$user_id]);
        
        if ($check_stmt->fetch()) {
            // Update existing record
            $query = "UPDATE tbl_user_2fa 
                     SET secret = ?, backup_codes = ?, enabled = 0, updated_at = NOW() 
                     WHERE user_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$secret, $backup_codes_hashed, $user_id]);
        } else {
            // Insert new record
            $query = "INSERT INTO tbl_user_2fa (user_id, secret, backup_codes, enabled) 
                     VALUES (?, ?, ?, 0)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $secret, $backup_codes_hashed]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("2FA secret storage error: " . $e->getMessage());
        return false;
    }
}

/**
 * Enable 2FA for user
 */
function enable2FA($pdo, $user_id) {
    try {
        $query = "UPDATE tbl_user_2fa SET enabled = 1, updated_at = NOW() WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("2FA enable error: " . $e->getMessage());
        return false;
    }
}

/**
 * Disable 2FA for user
 */
function disable2FA($pdo, $user_id) {
    try {
        $query = "UPDATE tbl_user_2fa SET enabled = 0, backup_codes = NULL, updated_at = NOW() WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("2FA disable error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get 2FA status for user
 */
function get2FAStatus($pdo, $user_id) {
    try {
        $query = "SELECT enabled, secret, backup_codes FROM tbl_user_2fa WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Count remaining backup codes
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
                'backup_codes_count' => $backup_codes_count
            ];
        }
        
        return [
            'enabled' => false,
            'has_secret' => false,
            'backup_codes_count' => 0
        ];
    } catch (PDOException $e) {
        error_log("2FA status check error: " . $e->getMessage());
        return [
            'enabled' => false,
            'has_secret' => false,
            'backup_codes_count' => 0
        ];
    }
}

/**
 * Verify TOTP code for user
 */
function verifyUserTOTPCode($pdo, $user_id, $code) {
    try {
        $query = "SELECT secret FROM tbl_user_2fa WHERE user_id = ? AND enabled = 1";
        $stmt = $pdo->prepare($query);
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

/**
 * Verify backup code for user and remove if valid
 */
function verifyUserBackupCode($pdo, $user_id, $code) {
    try {
        $query = "SELECT backup_codes FROM tbl_user_2fa WHERE user_id = ? AND enabled = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || empty($result['backup_codes'])) {
            return false;
        }
        
        $verification = verifyBackupCode($result['backup_codes'], $code);
        
        if ($verification['valid']) {
            // Update backup codes (remove used one)
            $remaining_hashes = $verification['remaining_hashes'];
            $updated_backup_codes = !empty($remaining_hashes) ? json_encode(array_values($remaining_hashes)) : null;
            
            $update_query = "UPDATE tbl_user_2fa SET backup_codes = ? WHERE user_id = ?";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->execute([$updated_backup_codes, $user_id]);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("2FA backup code verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if 2FA is required for user (admin users)
 */
function is2FARequiredForUser($pdo, $user_id) {
    global $two_factor_required_for_admin;
    
    if (!$two_factor_required_for_admin) {
        return false;
    }
    
    try {
        // Get admin usergroup ID from config
        $admin_usergroup_id = defined('ADMIN_USERGROUP_ID') ? ADMIN_USERGROUP_ID : 1;
        
        $query = "SELECT usergroup FROM tbl_user WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['usergroup'] == $admin_usergroup_id;
    } catch (PDOException $e) {
        error_log("2FA requirement check error: " . $e->getMessage());
        return false;
    }
}

?>

