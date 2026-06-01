<?php
/**
 * Authentication Security Helper
 * Account lockout, rate-limiting, and login attempt tracking.
 * Requires admin PDO connection (getDB('admin')).
 */

function _getAdminPDO(): PDO {
    if (isset($GLOBALS['_ees_pdo_pool']['admin'])) {
        return $GLOBALS['_ees_pdo_pool']['admin'];
    }
    // Fallback: load config if not already loaded
    $config = __DIR__ . '/../../config.php';
    if (file_exists($config)) {
        require_once $config;
    }
    return getDB('admin');
}

function checkAccountLockout(string $identifier): array {
    $pdo              = _getAdminPDO();
    $lockout_attempts = defined('ACCOUNT_LOCKOUT_ATTEMPTS') ? ACCOUNT_LOCKOUT_ATTEMPTS : 5;
    $lockout_duration = defined('ACCOUNT_LOCKOUT_DURATION') ? ACCOUNT_LOCKOUT_DURATION : 1800;

    try {
        $check = $pdo->query("SHOW TABLES LIKE 'tbl_login_attempts'");
        if ($check->rowCount() === 0) {
            return ['locked' => false, 'unlock_time' => null, 'attempts' => 0];
        }

        $stmt = $pdo->prepare(
            "SELECT attempts, last_attempt, locked_until
             FROM tbl_login_attempts
             WHERE email = :identifier LIMIT 1"
        );
        $stmt->execute([':identifier' => $identifier]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['locked' => false, 'unlock_time' => null, 'attempts' => 0];
        }

        $attempts     = intval($row['attempts']);
        $locked_until = $row['locked_until'] ? strtotime($row['locked_until']) : null;

        if ($locked_until && time() < $locked_until) {
            return ['locked' => true, 'unlock_time' => $locked_until, 'attempts' => $attempts];
        }

        if ($locked_until && time() >= $locked_until) {
            resetLoginAttempts($identifier);
            return ['locked' => false, 'unlock_time' => null, 'attempts' => 0];
        }

        return [
            'locked'      => $attempts >= $lockout_attempts,
            'unlock_time' => null,
            'attempts'    => $attempts,
        ];
    } catch (PDOException $e) {
        error_log("checkAccountLockout error: " . $e->getMessage());
        return ['locked' => false, 'unlock_time' => null, 'attempts' => 0];
    }
}

function recordFailedLoginAttempt(string $identifier, string $ip_address): void {
    $pdo              = _getAdminPDO();
    $lockout_attempts = defined('ACCOUNT_LOCKOUT_ATTEMPTS') ? ACCOUNT_LOCKOUT_ATTEMPTS : 5;
    $lockout_duration = defined('ACCOUNT_LOCKOUT_DURATION') ? ACCOUNT_LOCKOUT_DURATION : 1800;

    try {
        $check = $pdo->query("SHOW TABLES LIKE 'tbl_login_attempts'");
        if ($check->rowCount() === 0) return;

        $stmt = $pdo->prepare("SELECT attempts FROM tbl_login_attempts WHERE email = :identifier LIMIT 1");
        $stmt->execute([':identifier' => $identifier]);
        $row = $stmt->fetch();

        $attempts     = $row ? intval($row['attempts']) + 1 : 1;
        $locked_until = $attempts >= $lockout_attempts
            ? date('Y-m-d H:i:s', time() + $lockout_duration)
            : null;

        if ($row) {
            $stmt = $pdo->prepare(
                "UPDATE tbl_login_attempts
                 SET attempts = :attempts, last_attempt = NOW(), ip_address = :ip, locked_until = :locked
                 WHERE email = :identifier"
            );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO tbl_login_attempts (email, attempts, last_attempt, ip_address, locked_until)
                 VALUES (:identifier, :attempts, NOW(), :ip, :locked)"
            );
        }
        $stmt->execute([':identifier' => $identifier, ':attempts' => $attempts, ':ip' => $ip_address, ':locked' => $locked_until]);
    } catch (PDOException $e) {
        error_log("recordFailedLoginAttempt error: " . $e->getMessage());
    }
}

function resetLoginAttempts(string $identifier): void {
    $pdo = _getAdminPDO();
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'tbl_login_attempts'");
        if ($check->rowCount() === 0) return;
        $stmt = $pdo->prepare("UPDATE tbl_login_attempts SET attempts = 0, locked_until = NULL WHERE email = :identifier");
        $stmt->execute([':identifier' => $identifier]);
    } catch (PDOException $e) {
        error_log("resetLoginAttempts error: " . $e->getMessage());
    }
}

function checkRateLimit(string $ip_address): bool {
    $pdo              = _getAdminPDO();
    $rate_limit       = defined('LOGIN_RATE_LIMIT')        ? LOGIN_RATE_LIMIT        : 10;
    $rate_limit_window = defined('LOGIN_RATE_LIMIT_WINDOW') ? LOGIN_RATE_LIMIT_WINDOW : 60;

    try {
        $check = $pdo->query("SHOW TABLES LIKE 'tbl_login_rate_limit'");
        if ($check->rowCount() === 0) return false;

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM tbl_login_rate_limit
             WHERE ip_address = :ip AND attempt_time > DATE_SUB(NOW(), INTERVAL :window SECOND)"
        );
        $stmt->execute([':ip' => $ip_address, ':window' => $rate_limit_window]);
        $count = intval($stmt->fetch()['cnt'] ?? 0);

        if ($count >= $rate_limit) {
            return true;
        }

        $ins = $pdo->prepare("INSERT INTO tbl_login_rate_limit (ip_address, attempt_time) VALUES (:ip, NOW())");
        $ins->execute([':ip' => $ip_address]);
        return false;
    } catch (PDOException $e) {
        error_log("checkRateLimit error: " . $e->getMessage());
        return false;
    }
}

if (!function_exists('getClientIP')) {
    function getClientIP(): string {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

/**
 * Complete an authenticated session after password (and optional 2FA) verification.
 */
function ees_establish_user_session(array $user): void {
    session_regenerate_id(true);
    $_SESSION['id']            = $user['id'];
    $_SESSION['firstname']     = $user['firstname'] ?? '';
    $_SESSION['lastname']      = $user['lastname'] ?? '';
    $_SESSION['name']          = $user['firstname'] ?? '';
    $_SESSION['last_name']     = $user['lastname'] ?? '';
    $_SESSION['email']         = $user['email'] ?? '';
    $_SESSION['username']      = $user['username'] ?? '';
    $_SESSION['group_id']      = $user['group_id'] ?? 0;
    $_SESSION['created']       = time();
    $_SESSION['last_activity'] = time();
}

/**
 * Store pending 2FA state after a successful password check.
 */
function ees_begin_pending_2fa(array $user, string $login_identifier): void {
    session_regenerate_id(true);
    $_SESSION['2fa_pending']      = true;
    $_SESSION['2fa_user_id']      = (int)$user['id'];
    $_SESSION['2fa_login_id']   = $login_identifier;
    $_SESSION['2fa_firstname']  = $user['firstname'] ?? '';
    $_SESSION['2fa_lastname']   = $user['lastname'] ?? '';
    $_SESSION['2fa_email']      = $user['email'] ?? '';
    $_SESSION['2fa_username']   = $user['username'] ?? '';
    $_SESSION['2fa_group_id']   = $user['group_id'] ?? 0;
    $_SESSION['2fa_created']    = time();
}

/**
 * Clear pending 2FA session variables.
 */
function ees_clear_pending_2fa(): void {
    unset(
        $_SESSION['2fa_pending'],
        $_SESSION['2fa_user_id'],
        $_SESSION['2fa_login_id'],
        $_SESSION['2fa_firstname'],
        $_SESSION['2fa_lastname'],
        $_SESSION['2fa_email'],
        $_SESSION['2fa_username'],
        $_SESSION['2fa_group_id'],
        $_SESSION['2fa_created']
    );
}

/**
 * Build user row array from pending 2FA session for ees_establish_user_session().
 */
function ees_pending_2fa_user_from_session(): ?array {
    if (empty($_SESSION['2fa_pending']) || empty($_SESSION['2fa_user_id'])) {
        return null;
    }
    return [
        'id'        => $_SESSION['2fa_user_id'],
        'firstname' => $_SESSION['2fa_firstname'] ?? '',
        'lastname'  => $_SESSION['2fa_lastname'] ?? '',
        'email'     => $_SESSION['2fa_email'] ?? '',
        'username'  => $_SESSION['2fa_username'] ?? '',
        'group_id'  => $_SESSION['2fa_group_id'] ?? 0,
    ];
}
