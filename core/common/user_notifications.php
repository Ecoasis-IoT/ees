<?php
/**
 * User-facing notification helpers (password expiry, plant PDF, unread counts).
 */

require_once __DIR__ . '/../scripts/create_notification.php';

/** Add password_changed_at to tbl_user when missing. */
function ees_ensure_user_password_columns(?PDO $pdo = null): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo = $pdo ?? getDB('admin');
    $chk = $pdo->query("SHOW COLUMNS FROM tbl_user LIKE 'password_changed_at'");
    if ($chk && $chk->rowCount() === 0) {
        $pdo->exec(
            "ALTER TABLE tbl_user
             ADD COLUMN password_changed_at DATETIME NULL DEFAULT NULL AFTER password"
        );
        $pdo->exec(
            "UPDATE tbl_user
             SET password_changed_at = COALESCE(date_added, NOW())
             WHERE password_changed_at IS NULL"
        );
    }
    $done = true;
}

/** Record when a user's password was last set/changed. */
function ees_set_password_changed_at(int $user_id, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? getDB('admin');
    ees_ensure_user_password_columns($pdo);
    try {
        $stmt = $pdo->prepare('UPDATE tbl_user SET password_changed_at = NOW() WHERE id = ? LIMIT 1');
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log('ees_set_password_changed_at: ' . $e->getMessage());
    }
}

/** Mark password-expiry notifications read after a successful password change. */
function ees_clear_password_expiry_notifications(int $user_id, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? getDB('admin');
    ees_ensure_notifications_table($pdo);
    ees_ensure_notification_reference_key($pdo);
    try {
        $stmt = $pdo->prepare(
            "UPDATE tbl_notifications
             SET is_read = 1
             WHERE user_id = ? AND is_read = 0
               AND reference_key IN ('password_expiry_warning', 'password_expired')"
        );
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log('ees_clear_password_expiry_notifications: ' . $e->getMessage());
    }
}

/**
 * Create or refresh a notification keyed by reference_key (one unread per key).
 */
function upsertUserNotification(
    int $user_id,
    string $reference_key,
    string $message,
    string $type = 'info',
    string $action_url = '',
    string $action_label = ''
): bool {
    try {
        $pdo = getDB('admin');
        ees_ensure_notifications_table($pdo);
        ees_ensure_notification_reference_key($pdo);

        $reference_key = substr(preg_replace('/[^a-z0-9_.-]/i', '_', $reference_key) ?: 'notice', 0, 64);

        $find = $pdo->prepare(
            "SELECT id, message FROM tbl_notifications
             WHERE user_id = ? AND reference_key = ? AND is_read = 0
             LIMIT 1"
        );
        $find->execute([$user_id, $reference_key]);
        $existing = $find->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['message'] === $message) {
                return true;
            }
            $upd = $pdo->prepare(
                "UPDATE tbl_notifications
                 SET message = ?, type = ?, action_url = ?, action_label = ?, created_at = NOW()
                 WHERE id = ? AND user_id = ?"
            );
            $upd->execute([
                $message,
                $type,
                $action_url ?: null,
                $action_label ?: null,
                $existing['id'],
                $user_id,
            ]);
            return true;
        }

        $ins = $pdo->prepare(
            "INSERT INTO tbl_notifications
                (user_id, type, message, action_url, action_label, reference_key)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([
            $user_id,
            $type,
            $message,
            $action_url ?: null,
            $action_label ?: null,
            $reference_key,
        ]);
        return true;
    } catch (Exception $e) {
        error_log('upsertUserNotification: ' . $e->getMessage());
        return false;
    }
}

/** Warn when password expires within 7 days (uses PASSWORD_EXPIRATION_DAYS). */
function ees_sync_password_expiry_notification(int $user_id, ?PDO $pdo = null): void
{
    $expiry_days = defined('PASSWORD_EXPIRATION_DAYS') ? (int)PASSWORD_EXPIRATION_DAYS : 0;
    if ($expiry_days <= 0 || $user_id <= 0) {
        return;
    }

    $pdo = $pdo ?? getDB('admin');
    ees_ensure_user_password_columns($pdo);

    $cols = 'id, date_added, password_changed_at';
    $stmt = $pdo->prepare("SELECT $cols FROM tbl_user WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return;
    }

    $changed_raw = $user['password_changed_at'] ?? $user['date_added'] ?? null;
    if (!$changed_raw) {
        return;
    }

    $expires_ts = strtotime($changed_raw . " +{$expiry_days} days");
    if ($expires_ts === false) {
        return;
    }

    $days_left = (int)ceil(($expires_ts - time()) / 86400);
    $profile_url = function_exists('ees_url_path') ? ees_url_path('profile.php') : 'profile';

    if ($days_left <= 0) {
        upsertUserNotification(
            $user_id,
            'password_expired',
            'Your password has expired. Please change it now to keep your account secure.',
            'danger',
            $profile_url,
            'Change password'
        );
        return;
    }

    if ($days_left <= 7) {
        $day_word = $days_left === 1 ? 'day' : 'days';
        upsertUserNotification(
            $user_id,
            'password_expiry_warning',
            "Your password will expire in {$days_left} {$day_word}. Please update it before it expires.",
            'warning',
            $profile_url,
            'Update password'
        );
    }
}

/** Notify user that a plant PDF report was generated. */
function ees_notify_plant_pdf_generated(int $user_id, array $context = []): bool
{
    $site_name  = trim((string)($context['site_name'] ?? 'Plant'));
    $start_date = trim((string)($context['start_date'] ?? ''));
    $end_date   = trim((string)($context['end_date'] ?? ''));
    $range      = ($start_date && $end_date) ? " ({$start_date} to {$end_date})" : '';

    $message = "Plant report PDF generated for {$site_name}{$range}.";
    $plant_url = function_exists('ees_url_path') ? ees_url_path('plant.php') : 'plant';

    return createNotification(
        $user_id,
        $message,
        'success',
        $plant_url,
        'View plant report'
    );
}

/** Unread notification count for nav badges. */
function ees_get_unread_notification_count(int $user_id, ?PDO $pdo = null): int
{
    if ($user_id <= 0) {
        return 0;
    }
    try {
        $pdo = $pdo ?? getDB('admin');
        ees_ensure_notifications_table($pdo);
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM tbl_notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
