<?php
/**
 * 2FA profile setup: generate, verify, status, regenerate backup codes, disable.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/two_factor_auth.php';
require_once __DIR__ . '/../common/security_logging.php';

function ees_setup_2fa_json(array $payload): void {
    ob_end_clean();
    echo json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Unauthorized']);
}

if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('2fa_setup_csrf_failure', ['user_id' => $_SESSION['id'] ?? 0], 'ERROR');
    ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Invalid security token']);
}

$user_id = (int)$_SESSION['id'];
$action = $_POST['action'] ?? '';
$pdo = getDB('admin');

try {
    if ($action === 'generate') {
        $secret = generate2FASecret();
        $backup_codes = generateBackupCodes();
        if (!store2FASecret($pdo, $user_id, $secret, $backup_codes)) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Failed to generate 2FA secret']);
        }
        $stmt = $pdo->prepare('SELECT email FROM tbl_user WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        logSecurityEvent('2fa_secret_generated', ['user_id' => $user_id], 'INFO');
        ees_setup_2fa_json([
            'statusCode' => 'success',
            'secret' => hexToBase32($secret),
            'qr_url' => generate2FAQRCodeData($user['email'] ?? '', $secret),
            'backup_codes' => $backup_codes,
        ]);
    }

    if ($action === 'verify') {
        $code = trim($_POST['code'] ?? '');
        if ($code === '') {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Verification code is required']);
        }
        $stmt = $pdo->prepare('SELECT secret FROM tbl_user_2fa WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result || empty($result['secret'])) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => '2FA secret not found. Please generate a new one.']);
        }
        if (!verifyTOTPCode($result['secret'], $code)) {
            logSecurityEvent('2fa_setup_verification_failed', ['user_id' => $user_id], 'WARNING');
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Invalid verification code']);
        }
        if (!enable2FA($pdo, $user_id)) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Failed to enable 2FA']);
        }
        logSecurityEvent('2fa_enabled', ['user_id' => $user_id], 'INFO');
        ees_setup_2fa_json(['statusCode' => 'success', 'message' => '2FA enabled successfully']);
    }

    if ($action === 'disable') {
        if (is2FAMandatoryForUser($pdo, $user_id)) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Two-factor authentication is required and cannot be turned off.']);
        }
        $password = $_POST['password'] ?? '';
        if ($password === '') {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Password confirmation required']);
        }
        $stmt = $pdo->prepare('SELECT password FROM tbl_user WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stored = (string)($user['password'] ?? '');
        $ok = $stored !== '' && (password_verify($password, $stored)
            || (strlen($stored) === 32 && ctype_xdigit($stored) && hash_equals(strtolower($stored), md5($password))));
        if (!$ok) {
            logSecurityEvent('2fa_disable_password_failed', ['user_id' => $user_id], 'WARNING');
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Invalid password']);
        }
        if (!disable2FA($pdo, $user_id)) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Failed to disable 2FA']);
        }
        logSecurityEvent('2fa_disabled', ['user_id' => $user_id], 'INFO');
        ees_setup_2fa_json(['statusCode' => 'success', 'message' => '2FA disabled successfully']);
    }

    if ($action === 'regenerate_backup') {
        $status = get2FAStatus($pdo, $user_id);
        if (empty($status['enabled'])) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Turn on two-factor authentication before regenerating backup codes.']);
        }
        $codes = generateBackupCodes();
        if (!persistBackupCodes($pdo, $user_id, $codes)) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Could not save the new backup codes.']);
        }
        logSecurityEvent('2fa_backup_codes_regenerated', ['user_id' => $user_id], 'INFO');
        ees_setup_2fa_json([
            'statusCode' => 'success',
            'backup_codes' => $codes,
            'message' => 'New backup codes saved. The previous codes no longer work.',
        ]);
    }

    if ($action === 'status') {
        $status = get2FAStatus($pdo, $user_id);
        $status['mandatory'] = is2FAMandatoryForUser($pdo, $user_id);
        $status['backup_codes'] = $status['enabled'] ? getSavedBackupCodes($pdo, $user_id) : [];
        ees_setup_2fa_json(['statusCode' => 'success', 'data' => $status]);
    }

    if ($action === 'get_secret') {
        $stmt = $pdo->prepare('SELECT secret FROM tbl_user_2fa WHERE user_id = ? AND enabled = 0');
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result || empty($result['secret'])) {
            ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'No existing secret found']);
        }
        $email_stmt = $pdo->prepare('SELECT email FROM tbl_user WHERE id = ?');
        $email_stmt->execute([$user_id]);
        $user = $email_stmt->fetch(PDO::FETCH_ASSOC);
        ees_setup_2fa_json([
            'statusCode' => 'success',
            'secret' => hexToBase32($result['secret']),
            'qr_url' => generate2FAQRCodeData($user['email'] ?? '', $result['secret']),
            'backup_codes' => getSavedBackupCodes($pdo, $user_id),
        ]);
    }

    ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Invalid action']);
} catch (PDOException $e) {
    error_log('2FA setup error: ' . $e->getMessage());
    ees_setup_2fa_json(['statusCode' => 'Err', 'message' => 'Database error']);
}
