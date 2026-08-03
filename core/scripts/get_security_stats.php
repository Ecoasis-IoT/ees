<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/authorization.php';

if ((int)($_SESSION['group_id'] ?? 0) !== ADMIN_USERGROUP_ID) {
    ob_clean(); http_response_code(403);
    echo json_encode(['statusCode' => 'error', 'message' => 'Forbidden']); exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrf)) {
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => 'Invalid security token']); exit;
}

try {
    $pdo   = getDB('admin');
    $stats = [];

    // Check table existence
    $t = $pdo->query("SHOW TABLES LIKE 'tbl_security_logs'");
    $has_logs = $t->rowCount() > 0;

    if ($has_logs) {
        $stats['failed_logins_24h']   = (int)$pdo->query("SELECT COUNT(*) FROM tbl_security_logs WHERE event_type = 'login_failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        $prev_failed                  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_security_logs WHERE event_type = 'login_failed' AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 48 HOUR) AND DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        $stats['failed_logins_trend'] = $prev_failed > 0 ? round((($stats['failed_logins_24h'] - $prev_failed) / $prev_failed) * 100, 1) : 0;

        // Login attempts last 7 days
        $stmt = $pdo->query("
            SELECT DATE(created_at) as date,
                   SUM(CASE WHEN event_type='login_success' THEN 1 ELSE 0 END) as successful,
                   SUM(CASE WHEN event_type='login_failed'  THEN 1 ELSE 0 END) as failed
            FROM tbl_security_logs
            WHERE event_type IN ('login_success','login_failed')
              AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC");
        $stats['login_attempts_chart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top event types
        $stmt = $pdo->query("
            SELECT event_type, COUNT(*) as count
            FROM tbl_security_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY event_type ORDER BY count DESC LIMIT 10");
        $stats['event_types_chart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent events (cap for security dashboard DataTable client-side paging)
        $stmt = $pdo->query("
            SELECT event_type, severity, ip_address, user_id, created_at
            FROM tbl_security_logs
            ORDER BY created_at DESC LIMIT 100");
        $stats['recent_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stats['failed_logins_24h']   = 0;
        $stats['failed_logins_trend'] = 0;
        $stats['login_attempts_chart'] = [];
        $stats['event_types_chart']    = [];
        $stats['recent_events']        = [];
    }

    // Locked / inactive accounts
    $t2 = $pdo->query("SHOW COLUMNS FROM tbl_user LIKE 'account_locked'");
    if ($t2->rowCount() > 0) {
        $stats['locked_accounts'] = (int)$pdo->query("SELECT COUNT(*) FROM tbl_user WHERE account_locked = 1")->fetchColumn();
    } else {
        $stats['locked_accounts'] = 0;
    }

    ob_clean();
    echo json_encode(['statusCode' => 'success', 'stats' => $stats], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("get_security_stats PDO: " . $e->getMessage());
    ob_clean();
    echo json_encode(['statusCode' => 'error', 'message' => 'Database error']);
}
