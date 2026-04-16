<?php
/**
 * Internal helper — not a public endpoint.
 * Call via require_once and use createNotification() or ees_ensure_notifications_table().
 */

if (!function_exists('ees_ensure_notifications_table')) {

    function ees_ensure_notifications_table(?PDO $pdo = null): void {
        $pdo = $pdo ?? getDB('admin');
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
    }
}

if (!function_exists('createNotification')) {

    function createNotification(int $user_id, string $message, string $type = 'info', string $action_url = '', string $action_label = ''): bool
    {
        try {
            $pdo = getDB('admin');
            ees_ensure_notifications_table($pdo);

            $stmt = $pdo->prepare(
                "INSERT INTO tbl_notifications (user_id, type, message, action_url, action_label)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $user_id,
                $type,
                $message,
                $action_url ?: null,
                $action_label ?: null,
            ]);
            return true;
        } catch (Exception $e) {
            error_log("createNotification error: " . $e->getMessage());
            return false;
        }
    }

}
