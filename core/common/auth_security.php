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
