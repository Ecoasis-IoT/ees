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
    $pdo = getDB('admin');

    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'info',
        message TEXT NOT NULL,
        action_url VARCHAR(500) NULL,
        action_label VARCHAR(100) NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_unread (user_id, is_read),
        INDEX idx_created (created_at)
    )");

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
