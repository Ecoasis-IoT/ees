<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/csrf.php';
require_once __DIR__ . '/common/asset_helper.php';

$csrf_token = generateCSRFToken();
$user_id    = (int)$_SESSION['id'];

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

$filter  = $_GET['filter'] ?? 'all';
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = 'user_id = :uid';
$params = [':uid' => $user_id];
if ($filter === 'unread') {
    $where .= ' AND is_read = 0';
} elseif ($filter === 'read') {
    $where .= ' AND is_read = 1';
}

$total = (int)$pdo->prepare("SELECT COUNT(*) FROM tbl_notifications WHERE $where")
    ->execute($params) ? $pdo->prepare("SELECT COUNT(*) FROM tbl_notifications WHERE $where") : null;

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_notifications WHERE $where");
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();

$stmtList = $pdo->prepare(
    "SELECT id, type, message, action_url, action_label, is_read, created_at
     FROM tbl_notifications
     WHERE $where
     ORDER BY created_at DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmtList->bindValue($k, $v);
$stmtList->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmtList->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmtList->execute();
$notifications = $stmtList->fetchAll(PDO::FETCH_ASSOC);

$sUnread = $pdo->prepare("SELECT COUNT(*) FROM tbl_notifications WHERE user_id = :uid AND is_read = 0");
$sUnread->execute([':uid' => $user_id]);
$unreadCount = (int)$sUnread->fetchColumn();

$totalPages = (int)ceil($total / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Notifications | EES</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/main.css">

    <style>
        .notification-item {
            border-left: 4px solid transparent;
            transition: background 0.15s;
        }
        .notification-item.unread {
            border-left-color: #17a2b8;
            background: rgba(23,162,184,.05);
        }
        .notification-item:hover { background: rgba(0,0,0,.03); }
        .notification-item .notif-icon {
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .notif-icon.info    { background:#d1ecf1; color:#0c5460; }
        .notif-icon.success { background:#d4edda; color:#155724; }
        .notif-icon.warning { background:#fff3cd; color:#856404; }
        .notif-icon.danger  { background:#f8d7da; color:#721c24; }
        .badge-unread { font-size: .75rem; }
        .filter-bar .btn { min-width: 90px; }
        .empty-state { padding: 60px 0; text-align: center; color: #6c757d; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; }
    </style>
</head>
<body data-theme="theme-cyan">

<?php include_once("common/page-loader.php") ?>

<div id="wrapper">
    <?php include_once("common/header.php") ?>
    <?php include_once("common/sidebar.php") ?>

    <div id="content">
        <div id="main-content">
            <div class="container-fluid">

                <div class="block-header">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-6 col-md-8 col-sm-12">
                            <h2>
                                <a class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a>
                                Notifications
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger badge-unread ms-1"><?= $unreadCount ?> unread</span>
                                <?php endif; ?>
                            </h2>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="icon-home"></i></a></li>
                                <li class="breadcrumb-item active">Notifications</li>
                            </ul>
                        </div>
                        <div class="col-lg-6 col-md-4 col-sm-12 text-end">
                            <?php if ($unreadCount > 0): ?>
                            <button class="btn btn-sm btn-outline-secondary" id="btn-mark-all">
                                <i class="fa fa-check-square-o me-1"></i> Mark all as read
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row clearfix g-3">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h2 class="mb-0">
                                    <?= $total ?> notification<?= $total !== 1 ? 's' : '' ?>
                                </h2>
                                <div class="filter-bar d-flex gap-2">
                                    <a href="notifications.php?filter=all"
                                       class="btn btn-sm <?= $filter === 'all'    ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
                                    <a href="notifications.php?filter=unread"
                                       class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                        Unread
                                        <?php if ($unreadCount > 0): ?>
                                            <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <a href="notifications.php?filter=read"
                                       class="btn btn-sm <?= $filter === 'read'   ? 'btn-primary' : 'btn-outline-secondary' ?>">Read</a>
                                </div>
                            </div>

                            <div class="body p-0">
                                <?php if (empty($notifications)): ?>
                                <div class="empty-state">
                                    <i class="fa fa-bell-o d-block"></i>
                                    <p class="mb-0">
                                        <?= $filter === 'unread' ? 'No unread notifications.' : 'No notifications yet.' ?>
                                    </p>
                                </div>
                                <?php else: ?>
                                <ul class="list-unstyled mb-0" id="notif-list">
                                <?php
                                $icons = [
                                    'info'    => 'fa-info',
                                    'success' => 'fa-check',
                                    'warning' => 'fa-exclamation-triangle',
                                    'danger'  => 'fa-times-circle',
                                ];
                                foreach ($notifications as $n):
                                    $type     = htmlspecialchars($n['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                                    $icon     = $icons[$n['type']] ?? 'fa-info';
                                    $unreadCls = $n['is_read'] ? '' : 'unread';
                                    $dt       = date('d M Y, H:i', strtotime($n['created_at']));
                                ?>
                                <li class="notification-item <?= $unreadCls ?> px-4 py-3 d-flex align-items-start gap-3"
                                    data-id="<?= (int)$n['id'] ?>">
                                    <div class="notif-icon <?= $type ?>">
                                        <i class="fa <?= $icon ?>"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-1 text-truncate-2">
                                            <?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <small class="text-muted"><?= $dt ?></small>
                                        <?php if ($n['action_url'] && $n['action_label']): ?>
                                            <a href="<?= htmlspecialchars($n['action_url'], ENT_QUOTES, 'UTF-8') ?>"
                                               class="btn btn-xs btn-outline-primary ms-2">
                                               <?= htmlspecialchars($n['action_label'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                        <?php if (!$n['is_read']): ?>
                                        <button class="btn btn-xs btn-link p-0 text-secondary btn-ack"
                                                title="Mark as read" data-id="<?= (int)$n['id'] ?>">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                                </ul>

                                <?php if ($totalPages > 1): ?>
                                <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?>
                                    </small>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?filter=<?= urlencode($filter) ?>&page=<?= $page - 1 ?>">
                                                    <i class="fa fa-angle-left"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?filter=<?= urlencode($filter) ?>&page=<?= $p ?>"><?= $p ?></a>
                                            </li>
                                            <?php endfor; ?>
                                            <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?filter=<?= urlencode($filter) ?>&page=<?= $page + 1 ?>">
                                                    <i class="fa fa-angle-right"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include_once("common/footer.php") ?>
            </div>
        </div>
    </div>
</div>

<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/bundles/vendorscripts.bundle.js"></script>
<script src="assets/bundles/mainscripts.bundle.js"></script>

<script>
(function () {
    var CSRF = $('meta[name="csrf-token"]').attr('content') || '';

    function acknowledge(id, $item) {
        $.ajax({
            url: 'scripts/acknowledge_notification.php',
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: { notification_id: id, csrf_token: CSRF },
            success: function (r) {
                if (r && r.success) {
                    $item.removeClass('unread');
                    $item.find('.btn-ack').remove();
                }
            }
        });
    }

    $(document).on('click', '.btn-ack', function () {
        var $btn  = $(this);
        var id    = parseInt($btn.data('id'));
        var $item = $btn.closest('.notification-item');
        acknowledge(id, $item);
    });

    $('#btn-mark-all').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Marking...');
        $.ajax({
            url: 'scripts/acknowledge_notification.php',
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: { mark_all: 1, csrf_token: CSRF },
            success: function (r) {
                if (r && r.success) {
                    $('.notification-item.unread').removeClass('unread').find('.btn-ack').remove();
                    $btn.remove();
                    $('.badge-unread').remove();
                    $('.badge.bg-danger.ms-1').remove();
                } else {
                    $btn.prop('disabled', false).html('<i class="fa fa-check-square-o me-1"></i> Mark all as read');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-check-square-o me-1"></i> Mark all as read');
            }
        });
    });
})();
</script>
</body>
</html>
