<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/authorization.php';

header('Content-Type: application/json; charset=utf-8');

$user_id = getCurrentUserId();
if (!$user_id) {
    ob_clean();
    echo json_encode(['success' => false, 'notifications' => []]);
    exit;
}

try {
    require_once __DIR__ . '/create_notification.php';
    require_once __DIR__ . '/../common/user_notifications.php';
    $pdo = getDB('admin');
    ees_ensure_notifications_table($pdo);

    ees_sync_password_expiry_notification($user_id, $pdo);

    $stmt = $pdo->prepare(
        "SELECT id, type, message, action_url, action_label, created_at
         FROM tbl_notifications
         WHERE user_id = ? AND is_read = 0
         ORDER BY created_at DESC
         LIMIT 50"
    );
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $notifications = [];
    foreach ($rows as $r) {
        $notifications[] = [
            'id'          => (int)$r['id'],
            'type'        => $r['type'],
            'message'     => $r['message'],
            'actionUrl'   => $r['action_url'],
            'actionLabel' => $r['action_label'],
            'timestamp'   => $r['created_at'],
        ];
    }

    ob_clean();
    echo json_encode(['success' => true, 'notifications' => $notifications]);

} catch (Exception $e) {
    error_log("get_notifications error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'notifications' => []]);
}
