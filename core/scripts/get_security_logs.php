<?php
/**
 * Get Security Logs for DataTables (server-side processing)
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';

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

    // Check table exists
    $t = $pdo->query("SHOW TABLES LIKE 'tbl_security_logs'");
    if ($t->rowCount() === 0) {
        ob_clean();
        echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        exit;
    }

    $draw   = isset($_POST['draw'])   ? intval($_POST['draw'])   : 1;
    $start  = isset($_POST['start'])  ? intval($_POST['start'])  : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

    // Optional column filters sent from the front-end
    $filter_severity   = trim($_POST['filter_severity']   ?? '');
    $filter_event_type = trim($_POST['filter_event_type'] ?? '');
    $filter_ip         = trim($_POST['filter_ip']         ?? '');
    $filter_date_from  = trim($_POST['filter_date_from']  ?? '');
    $filter_date_to    = trim($_POST['filter_date_to']    ?? '');

    $where  = '1=1';
    $params = [];

    if ($search !== '') {
        $where .= " AND (event_type LIKE :search OR email LIKE :search OR ip_address LIKE :search OR severity LIKE :search OR details LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    if ($filter_severity !== '') {
        $where .= " AND severity = :severity";
        $params[':severity'] = $filter_severity;
    }
    if ($filter_event_type !== '') {
        $where .= " AND event_type LIKE :etype";
        $params[':etype'] = '%' . $filter_event_type . '%';
    }
    if ($filter_ip !== '') {
        $where .= " AND ip_address LIKE :ip";
        $params[':ip'] = '%' . $filter_ip . '%';
    }
    if ($filter_date_from !== '') {
        $where .= " AND created_at >= :dfrom";
        $params[':dfrom'] = $filter_date_from . ' 00:00:00';
    }
    if ($filter_date_to !== '') {
        $where .= " AND created_at <= :dto";
        $params[':dto'] = $filter_date_to . ' 23:59:59';
    }

    // Total records (unfiltered)
    $total_records = (int)$pdo->query("SELECT COUNT(*) FROM tbl_security_logs")->fetchColumn();

    // Filtered count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_security_logs WHERE $where");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    $filtered_records = (int)$stmt->fetchColumn();

    // Data
    $stmt = $pdo->prepare(
        "SELECT id, event_type, email, ip_address, severity, details, created_at
         FROM tbl_security_logs
         WHERE $where
         ORDER BY created_at DESC
         LIMIT :start, :length"
    );
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':start',  $start,  PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($logs as $log) {
        $data[] = [
            'id'         => $log['id'],
            'created_at' => $log['created_at'],
            'event_type' => $log['event_type'],
            'email'      => $log['email'] ?? '',
            'ip_address' => $log['ip_address'] ?? '',
            'severity'   => strtoupper($log['severity'] ?? 'INFO'),
            'details'    => $log['details'] ?? '',
        ];
    }

    ob_clean();
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $total_records,
        'recordsFiltered' => $filtered_records,
        'data'            => $data,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("get_security_logs PDO: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [],
        'error' => 'Database error occurred',
    ]);
}
?>
