<?php
/**
 * Security Logging
 * Centralized logging for security events.
 * Writes to logs/security/ and optionally to tbl_security_logs in the admin DB.
 */

function getClientIP(): string {
    $ip_keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Log a security event to file (and optionally the database).
 *
 * @param string $event_type  e.g. 'login_failed', 'csrf_failure'
 * @param array  $details     Additional context
 * @param string $severity    INFO | WARNING | ERROR | CRITICAL
 */
function logSecurityEvent(string $event_type, array $details = [], string $severity = 'INFO'): void {
    // Resolve the admin PDO connection if available
    $pdo = null;
    if (isset($GLOBALS['_ees_pdo_pool']['admin'])) {
        $pdo = $GLOBALS['_ees_pdo_pool']['admin'];
    } elseif (isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
    }

    $log_dir = __DIR__ . '/../logs/security';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/security_' . date('Y-m-d') . '.log';

    $entry = [
        'timestamp'      => date('Y-m-d H:i:s'),
        'event_type'     => $event_type,
        'severity'       => strtoupper($severity),
        'ip'             => getClientIP(),
        'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'request_uri'    => $_SERVER['REQUEST_URI']     ?? '',
        'request_method' => $_SERVER['REQUEST_METHOD']  ?? '',
        'user_id'        => $_SESSION['id']             ?? null,
        'email'          => $_SESSION['email']          ?? ($details['email'] ?? null),
        'details'        => $details,
    ];

    file_put_contents($log_file, json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

    // Optionally persist to database
    if ($pdo) {
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'tbl_security_logs'");
            if ($check->rowCount() > 0) {
                $stmt = $pdo->prepare(
                    "INSERT INTO tbl_security_logs
                        (event_type, severity, ip_address, email, user_id, details, created_at)
                     VALUES
                        (:event_type, :severity, :ip_address, :email, :user_id, :details, NOW())"
                );
                $stmt->execute([
                    ':event_type' => $event_type,
                    ':severity'   => strtoupper($severity),
                    ':ip_address' => $entry['ip'],
                    ':email'      => $entry['email'],
                    ':user_id'    => $entry['user_id'],
                    ':details'    => json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);
            }
        } catch (PDOException $e) {
            error_log("logSecurityEvent DB error: " . $e->getMessage());
        }
    }

    if ($severity === 'CRITICAL' || $severity === 'ERROR') {
        error_log("Security [{$severity}] {$event_type}: " . json_encode($details));
    }

    // Rotate log if > 10 MB
    if (file_exists($log_file) && filesize($log_file) > 10 * 1024 * 1024) {
        _rotateSecurityLog($log_file);
    }
}

function _rotateSecurityLog(string $log_file): void {
    $log_dir      = dirname($log_file);
    $log_basename = basename($log_file, '.log');
    $rotation     = 1;

    while (file_exists("{$log_dir}/{$log_basename}.{$rotation}.log")) {
        $rotation++;
    }

    rename($log_file, "{$log_dir}/{$log_basename}.{$rotation}.log");

    if (function_exists('gzencode')) {
        $old  = "{$log_dir}/{$log_basename}.{$rotation}.log";
        $data = gzencode(file_get_contents($old), 9);
        file_put_contents($old . '.gz', $data);
        unlink($old);
    }

    // Keep only last 10 rotated files
    $rotated = glob("{$log_dir}/{$log_basename}.*.log*");
    if (count($rotated) > 10) {
        usort($rotated, fn($a, $b) => filemtime($a) - filemtime($b));
        foreach (array_slice($rotated, 0, count($rotated) - 10) as $old) {
            unlink($old);
        }
    }
}

function getSecurityLogStats(int $days = 7): array {
    $log_dir = __DIR__ . '/../logs/security';
    $stats   = [
        'total_events'        => 0,
        'failed_logins'       => 0,
        'csrf_failures'       => 0,
        'account_lockouts'    => 0,
        'rate_limit_violations' => 0,
        'password_resets'     => 0,
        'account_deletions'   => 0,
        'by_severity'         => [],
        'by_event'            => [],
        'by_ip'               => [],
    ];

    if (!is_dir($log_dir)) {
        return $stats;
    }

    $start_date = date('Y-m-d', strtotime("-{$days} days"));
    $end_date   = date('Y-m-d');
    $log_files  = glob("{$log_dir}/security_*.log*") ?: [];

    foreach ($log_files as $log_file) {
        preg_match('/security_(\d{4}-\d{2}-\d{2})/', basename($log_file), $matches);
        if (isset($matches[1]) && ($matches[1] < $start_date || $matches[1] > $end_date)) {
            continue;
        }

        $content = (substr($log_file, -3) === '.gz')
            ? gzdecode(file_get_contents($log_file))
            : file_get_contents($log_file);

        foreach (explode("\n", trim($content)) as $line) {
            if (empty($line)) continue;
            $e = json_decode($line, true);
            if (!$e) continue;

            $stats['total_events']++;

            $stats['by_event'][$e['event_type']]  = ($stats['by_event'][$e['event_type']]  ?? 0) + 1;
            $stats['by_severity'][$e['severity']] = ($stats['by_severity'][$e['severity']] ?? 0) + 1;
            $stats['by_ip'][$e['ip']]             = ($stats['by_ip'][$e['ip']]             ?? 0) + 1;

            switch ($e['event_type']) {
                case 'login_failed':           $stats['failed_logins']++;         break;
                case 'csrf_failure':            $stats['csrf_failures']++;         break;
                case 'account_locked':          $stats['account_lockouts']++;      break;
                case 'rate_limit_exceeded':     $stats['rate_limit_violations']++; break;
                case 'password_reset_completed': $stats['password_resets']++;      break;
                case 'user_deleted':            $stats['account_deletions']++;     break;
            }
        }
    }

    arsort($stats['by_event']);
    arsort($stats['by_ip']);
    arsort($stats['by_severity']);

    return $stats;
}

function cleanOldSecurityLogs(int $days_to_keep = 30): int {
    $log_dir = __DIR__ . '/../logs/security';
    if (!is_dir($log_dir)) return 0;

    $cutoff  = strtotime("-{$days_to_keep} days");
    $deleted = 0;

    foreach (glob("{$log_dir}/security_*.log*") ?: [] as $file) {
        if (filemtime($file) < $cutoff) {
            unlink($file);
            $deleted++;
        }
    }

    return $deleted;
}
