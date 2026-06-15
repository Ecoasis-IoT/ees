<?php
/**
 * Get Audit Logs for DataTables (server-side processing)
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/audit_logs_query.php';

if ((int)($_SESSION['group_id'] ?? 0) !== ADMIN_USERGROUP_ID) {
    ob_clean();
    http_response_code(403);
    echo json_encode(['statusCode' => 'error', 'message' => 'Forbidden']);
    exit;
}

if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => 'Invalid security token']);
    exit;
}

try {
    $pdo = getDB('admin');

    $t = $pdo->query("SHOW TABLES LIKE 'tbl_audit_logs'");
    if ($t->rowCount() === 0) {
        ob_clean();
        echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        exit;
    }

    $draw   = isset($_POST['draw'])   ? intval($_POST['draw'])   : 1;
    $start  = isset($_POST['start'])  ? intval($_POST['start'])  : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
    if ($search === '' && !empty($_POST['search']) && is_string($_POST['search'])) {
        $search = trim($_POST['search']);
    }

    $filters = ees_audit_logs_build_filters(array_merge($_POST, ['search' => $search]));
    $where   = $filters['where'];
    $params  = $filters['params'];

    $total_records = (int)$pdo->query("SELECT COUNT(*) FROM tbl_audit_logs")->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_audit_logs WHERE $where");
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $filtered_records = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT id, category, action, severity, user_id, username, email, ip_address,
                request_method, request_uri, resource, message, details, created_at
         FROM tbl_audit_logs
         WHERE $where
         ORDER BY created_at DESC
         LIMIT :start, :length"
    );
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':start',  $start,  PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($logs as $log) {
        $data[] = [
            'id'             => $log['id'],
            'created_at'     => $log['created_at'],
            'category'       => $log['category'],
            'action'         => $log['action'],
            'severity'       => strtoupper($log['severity'] ?? 'INFO'),
            'user_label'     => ees_audit_logs_user_label($log),
            'ip_address'     => $log['ip_address'] ?? '',
            'request_method' => $log['request_method'] ?? '',
            'request_uri'    => $log['request_uri'] ?? '',
            'resource'       => $log['resource'] ?? '',
            'message'        => $log['message'] ?? '',
            'details'        => $log['details'] ?? '',
        ];
    }

    ob_clean();
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $total_records,
        'recordsFiltered' => $filtered_records,
        'data'            => $data,
    ]);
} catch (Throwable $e) {
    ob_clean();
    error_log('get_audit_logs error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['statusCode' => 'error', 'message' => 'Failed to load audit logs']);
}
