<?php
/**
 * Application audit logging — user actions, page views, system events, errors.
 * Persists to tbl_audit_logs (admin DB) and logs/audit/ daily files.
 */

if (!function_exists('getClientIP')) {
    require_once __DIR__ . '/security_logging.php';
}

/**
 * @param string $category  user | system | error
 * @param string $action    e.g. page_view, report_generated, login_success
 */
function logAuditEvent(
    string $category,
    string $action,
    array $details = [],
    string $severity = 'INFO',
    ?string $resource = null,
    ?string $message = null
): void {
    static $writing = false;
    if ($writing) {
        return;
    }

    $category = in_array($category, ['user', 'system', 'error'], true) ? $category : 'user';
    $severity = strtoupper($severity);
    if (!in_array($severity, ['INFO', 'WARNING', 'ERROR', 'CRITICAL'], true)) {
        $severity = 'INFO';
    }

    $action = substr(preg_replace('/[^a-z0-9_.-]/i', '_', $action) ?: 'unknown', 0, 64);

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $entry = [
        'timestamp'      => date('Y-m-d H:i:s'),
        'category'       => $category,
        'action'         => $action,
        'severity'       => $severity,
        'user_id'        => $_SESSION['id'] ?? null,
        'username'       => $_SESSION['username'] ?? null,
        'email'          => $_SESSION['email'] ?? ($details['email'] ?? null),
        'ip'             => getClientIP(),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'request_uri'    => substr($_SERVER['REQUEST_URI'] ?? '', 0, 500),
        'resource'       => $resource ? substr($resource, 0, 200) : null,
        'message'        => $message ? substr($message, 0, 500) : null,
        'details'        => $details,
    ];

    $log_dir = __DIR__ . '/../logs/audit';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . '/audit_' . date('Y-m-d') . '.log';
    @file_put_contents(
        $log_file,
        json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    $pdo = $GLOBALS['_ees_pdo_pool']['admin'] ?? ($GLOBALS['pdo'] ?? null);
    if (!$pdo instanceof PDO) {
        return;
    }

    $writing = true;
    try {
        static $table_ok = null;
        if ($table_ok === null) {
            $check = $pdo->query("SHOW TABLES LIKE 'tbl_audit_logs'");
            $table_ok = $check && $check->rowCount() > 0;
        }
        if (!$table_ok) {
            $writing = false;
            return;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO tbl_audit_logs
                (category, action, severity, user_id, username, email, ip_address,
                 request_method, request_uri, resource, message, details, created_at)
             VALUES
                (:category, :action, :severity, :user_id, :username, :email, :ip_address,
                 :request_method, :request_uri, :resource, :message, :details, NOW())"
        );
        $stmt->execute([
            ':category'       => $category,
            ':action'         => $action,
            ':severity'       => $severity,
            ':user_id'        => $entry['user_id'],
            ':username'       => $entry['username'],
            ':email'          => $entry['email'],
            ':ip_address'     => $entry['ip'],
            ':request_method' => $entry['request_method'],
            ':request_uri'    => $entry['request_uri'],
            ':resource'       => $entry['resource'],
            ':message'        => $entry['message'],
            ':details'        => json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    } catch (PDOException $e) {
        error_log('logAuditEvent DB error: ' . $e->getMessage());
    } finally {
        $writing = false;
    }
}

/** Log a protected page view once per page per session minute. */
function ees_audit_log_page_view(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        return;
    }

    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === '' || strpos($script, '/scripts/') !== false) {
        return;
    }

    $page = basename($script, '.php');
    if (in_array($page, ['login', 'register', 'reset-password', 'error-404'], true)) {
        return;
    }

    $resource = $page . '.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        $resource .= '?' . substr((string)$_SERVER['QUERY_STRING'], 0, 120);
    }

    $key = 'audit_pv_' . md5($resource);
    $now = time();
    if (isset($_SESSION[$key]) && ($now - (int)$_SESSION[$key]) < 60) {
        return;
    }
    $_SESSION[$key] = $now;

    logAuditEvent('user', 'page_view', ['page' => $page], 'INFO', $resource, 'Viewed page');
}

/** Short non-reversible token reference for audit logs (never store raw reset tokens). */
function ees_audit_token_hint(string $token): string
{
    $token = trim($token);
    if ($token === '') {
        return '';
    }
    return substr(hash('sha256', $token), 0, 12);
}

/** Log views of public auth pages (forgot/reset password, login, etc.). */
function ees_audit_log_public_page_view(string $page, array $context = []): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        return;
    }

    $page = preg_replace('/[^a-z0-9_-]/i', '', $page) ?: 'unknown';
    $resource = $page . '.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        $qs = (string)$_SERVER['QUERY_STRING'];
        if ($page === 'forgot-password' && str_contains($qs, 'expired=1')) {
            $resource .= '?expired=1';
        } elseif ($page !== 'reset-password') {
            $resource .= '?' . substr($qs, 0, 80);
        }
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $key = 'audit_ppv_' . md5($resource);
    $now = time();
    if (isset($_SESSION[$key]) && ($now - (int)$_SESSION[$key]) < 60) {
        return;
    }
    $_SESSION[$key] = $now;

    logAuditEvent(
        'user',
        'page_view',
        array_merge(['page' => $page, 'public' => true], $context),
        'INFO',
        $resource,
        'Viewed public page'
    );
}

/** Log password-reset flow events without revealing account existence to end users. */
function ees_audit_log_password_reset(string $action, array $context = [], string $severity = 'INFO'): void
{
    if (!empty($context['token'])) {
        $context['token_hint'] = ees_audit_token_hint((string)$context['token']);
        unset($context['token']);
    }
    logAuditEvent('user', $action, $context, $severity, 'password_reset', null);
}

/** Log report / query generation from API scripts. */
function ees_audit_log_report(string $report_type, array $context = []): void {
    logAuditEvent(
        'user',
        'report_generated',
        array_merge(['report_type' => $report_type], $context),
        'INFO',
        $report_type,
        'Generated report'
    );
}

/** Log admin CRUD-style updates. */
function ees_audit_log_data_change(string $action, string $resource, array $context = []): void {
    logAuditEvent('user', $action, $context, 'INFO', $resource, ucfirst(str_replace('_', ' ', $action)));
}

function ees_register_audit_error_handlers(): void {
    static $registered = false;
    if ($registered) {
        return;
    }
    $registered = true;

    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        $severity = in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)
            ? 'ERROR' : 'WARNING';
        logAuditEvent('error', 'php_error', [
            'errno'    => $errno,
            'message'  => $errstr,
            'file'     => $errfile,
            'line'     => $errline,
        ], $severity, basename($errfile), $errstr);
        return false;
    });

    set_exception_handler(function (Throwable $e): void {
        logAuditEvent('error', 'uncaught_exception', [
            'class'   => get_class($e),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ], 'CRITICAL', basename($e->getFile()), $e->getMessage());

        $is_ajax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
        if ($is_ajax) {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['status' => 'Err', 'message' => 'An internal error occurred.']);
            exit;
        }

        if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
            http_response_code(500);
            echo '<h1>Application Error</h1><pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            http_response_code(500);
            echo 'An error occurred. Please contact the administrator.';
        }
        exit;
    });

    register_shutdown_function(function (): void {
        $err = error_get_last();
        if (!$err) {
            return;
        }
        $fatal = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];
        if (!in_array($err['type'], $fatal, true)) {
            return;
        }
        logAuditEvent('error', 'php_fatal', [
            'type'    => $err['type'],
            'message' => $err['message'],
            'file'    => $err['file'],
            'line'    => $err['line'],
        ], 'CRITICAL', basename($err['file']), $err['message']);
    });
}
