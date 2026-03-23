<?php
ob_start();
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/authorization.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    ob_end_clean(); echo json_encode(['success' => false]); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false]);
    exit;
}

$user_id = getCurrentUserId();
if (!$user_id) {
    ob_clean();
    echo json_encode(['success' => false]);
    exit;
}

try {
    $pdo = getDB('admin');

    // Mark all as read
    if (!empty($_POST['mark_all'])) {
        $stmt = $pdo->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        ob_clean();
        echo json_encode(['success' => true]);
        exit;
    }

    // Mark a single notification as read (accepts both 'id' and 'notification_id')
    $id = intval($_POST['notification_id'] ?? $_POST['id'] ?? 0);
    if ($id <= 0) {
        ob_clean();
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    ob_clean();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("acknowledge_notification error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false]);
}
