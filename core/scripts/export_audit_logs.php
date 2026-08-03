<?php
/**
 * Export Audit Logs as CSV (all rows matching current filters, admin only).
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/audit_logs_query.php';
require_once __DIR__ . '/../common/audit_logging.php';

if ((int)($_SESSION['group_id'] ?? 0) !== ADMIN_USERGROUP_ID) {
    ob_clean();
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['statusCode' => 'error', 'message' => 'Forbidden']);
    exit;
}

if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    ob_clean();
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['statusCode' => 'error', 'message' => 'Invalid security token']);
    exit;
}

const EES_AUDIT_EXPORT_MAX_ROWS = 50000;

try {
    $pdo = getDB('admin');

    $t = $pdo->query("SHOW TABLES LIKE 'tbl_audit_logs'");
    if (!$t || $t->rowCount() === 0) {
        ob_clean();
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['statusCode' => 'error', 'message' => 'Audit log table not found']);
        exit;
    }

    $filters = ees_audit_logs_build_filters($_POST);
    $where   = $filters['where'];
    $params  = $filters['params'];

    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_audit_logs WHERE $where");
    foreach ($params as $k => $v) {
        $count_stmt->bindValue($k, $v);
    }
    $count_stmt->execute();
    $total = (int)$count_stmt->fetchColumn();

    if ($total === 0) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['statusCode' => 'error', 'message' => 'No audit logs match the current filters']);
        exit;
    }

    if ($total > EES_AUDIT_EXPORT_MAX_ROWS) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'statusCode' => 'error',
            'message'    => 'Too many rows (' . number_format($total) . '). Narrow your filters (max '
                . number_format(EES_AUDIT_EXPORT_MAX_ROWS) . ' rows per export).',
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id, category, action, severity, user_id, username, email, ip_address,
                request_method, request_uri, resource, message, details, created_at
         FROM tbl_audit_logs
         WHERE $where
         ORDER BY created_at DESC
         LIMIT " . EES_AUDIT_EXPORT_MAX_ROWS
    );
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();

    ees_audit_log_report('audit_logs_export', [
        'rows'   => $total,
        'filters' => array_filter([
            'category'  => $_POST['filter_category'] ?? '',
            'action'    => $_POST['filter_action'] ?? '',
            'severity'  => $_POST['filter_severity'] ?? '',
            'ip'        => $_POST['filter_ip'] ?? '',
            'date_from' => $_POST['filter_date_from'] ?? '',
            'date_to'   => $_POST['filter_date_to'] ?? '',
            'search'    => trim($_POST['search'] ?? ''),
        ]),
    ]);

    ob_clean();

    $filename = 'ees_audit_logs_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    if ($out === false) {
        throw new RuntimeException('Unable to open CSV output stream');
    }

    // UTF-8 BOM for Excel
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, [
        'Timestamp',
        'Category',
        'Action',
        'Severity',
        'User ID',
        'Username',
        'Email',
        'IP Address',
        'Request Method',
        'Request URI',
        'Resource',
        'Message',
        'Details',
    ]);

    while ($log = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $log['created_at'],
            $log['category'],
            $log['action'],
            strtoupper($log['severity'] ?? 'INFO'),
            $log['user_id'] ?? '',
            $log['username'] ?? '',
            $log['email'] ?? '',
            $log['ip_address'] ?? '',
            $log['request_method'] ?? '',
            $log['request_uri'] ?? '',
            $log['resource'] ?? '',
            $log['message'] ?? '',
            $log['details'] ?? '',
        ]);
    }

    fclose($out);
    exit;
} catch (Throwable $e) {
    ob_clean();
    error_log('export_audit_logs error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['statusCode' => 'error', 'message' => 'Failed to export audit logs']);
}
