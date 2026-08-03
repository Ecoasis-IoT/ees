<?php
/**
 * Authorization Helper
 * Role and ownership checks for protected resources.
 * Uses ADMIN_USERGROUP_ID from config.php/.env (defaults to 1).
 */

/**
 * Check if the current user may access a resource owned by $resource_user_id.
 */
function isAuthorized(int $user_id, int $resource_user_id, bool $allow_admin = false): bool {
    if ($user_id === $resource_user_id) {
        return true;
    }

    if ($allow_admin) {
        $admin_can_view = defined('ADMIN_CAN_VIEW_ALL_PROFILES') ? ADMIN_CAN_VIEW_ALL_PROFILES : true;
        $admin_gid      = defined('ADMIN_USERGROUP_ID')          ? ADMIN_USERGROUP_ID          : 1;
        if ($admin_can_view && isset($_SESSION['group_id']) && (int)$_SESSION['group_id'] === $admin_gid) {
            return true;
        }
    }

    return false;
}

/**
 * Return the current user's ID from session, or false if not logged in or session expired.
 * Enforces the same idle-timeout logic as auth.php so AJAX scripts cannot bypass it.
 */
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['id'])) {
        return false;
    }

    $session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 14400;
    if ((time() - ($_SESSION['last_activity'] ?? 0)) >= $session_lifetime) {
        $_SESSION = [];
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return intval($_SESSION['id']);
}

/**
 * Abort with 401 JSON if the current session is invalid or expired.
 * Use this at the top of AJAX-only endpoints instead of auth.php (which redirects).
 */
function requireAuthAjax(): int {
    $user_id = getCurrentUserId();
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['status' => 'Err', 'message' => 'Session expired. Please log in again.']);
        exit;
    }
    return $user_id;
}

/**
 * Check if the current user is an admin.
 */
function isAdmin(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $admin_gid = defined('ADMIN_USERGROUP_ID') ? ADMIN_USERGROUP_ID : 1;
    return isset($_SESSION['group_id']) && (int)$_SESSION['group_id'] === $admin_gid;
}

/**
 * Abort with 403 JSON if the current user is not an admin.
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['status' => 'Err', 'message' => 'Forbidden: admin access required']);
        exit;
    }
}

/**
 * Verify ownership and abort with 401/403 JSON if not authorized.
 */
function requireAuthorization(int $resource_user_id, bool $allow_admin = false): bool {
    $current_user_id = getCurrentUserId();

    if ($current_user_id === false) {
        http_response_code(401);
        echo json_encode(['status' => 'Err', 'message' => 'Unauthorized']);
        exit;
    }

    if (!isAuthorized($current_user_id, $resource_user_id, $allow_admin)) {
        http_response_code(403);
        echo json_encode(['status' => 'Err', 'message' => 'Forbidden']);
        exit;
    }

    return true;
}
