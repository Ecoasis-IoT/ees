<?php
$_current_user_name = htmlspecialchars(
    (isset($_SESSION['fname']) ? $_SESSION['fname'] . ' ' . ($_SESSION['lname'] ?? '') : ($_SESSION['username'] ?? 'User')),
    ENT_QUOTES, 'UTF-8'
);
$_is_admin = isset($_SESSION['group_id']) && (int)$_SESSION['group_id'] === 1;
?>
<nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-btn">
            <button type="button" class="btn-toggle-offcanvas"><i class="lnr lnr-menu fa fa-bars"></i></button>
        </div>

        <div class="navbar-brand">
            <a href="dashboard.php">
                <img class="main-logo" src="assets/images/logo_icon.png" style="width:40px;height:auto;" alt="">
            </a>
        </div>

        <div class="navbar-right">
            <div id="navbar-menu">
                <ul class="nav navbar-nav">

                    <!-- Notifications Bell -->
                    <li class="dropdown" id="notif-dropdown" style="position:relative;">
                        <a href="#" class="icon-menu dropdown-toggle" id="notif-bell" title="Notifications">
                            <i class="fa fa-bell-o"></i>
                            <span id="notif-count" style="display:none;position:absolute;top:6px;right:6px;background:#dc3545;color:#fff;border-radius:50%;width:16px;height:16px;font-size:10px;line-height:16px;text-align:center;"></span>
                        </a>
                        <div id="notif-panel" style="display:none;position:absolute;right:0;top:100%;width:320px;background:#fff;border:1px solid #ddd;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.12);z-index:9999;max-height:400px;overflow-y:auto;">
                            <div style="padding:10px 16px;border-bottom:1px solid #eee;font-weight:600;font-size:14px;">Notifications</div>
                            <div id="notif-list" style="padding:8px 0;"></div>
                            <div style="padding:8px 16px;border-top:1px solid #eee;text-align:center;">
                                <a href="#" onclick="clearAllNotifications();return false;" style="font-size:12px;color:#888;">Clear all</a>
                            </div>
                        </div>
                    </li>

                    <!-- Profile dropdown -->
                    <li class="dropdown">
                        <a href="#" class="icon-menu dropdown-toggle" data-toggle="dropdown" title="<?= $_current_user_name ?>">
                            <i class="fa fa-user-circle-o"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li class="dropdown-header"><?= $_current_user_name ?></li>
                            <li><a href="profile.php"><i class="fa fa-cog"></i>&nbsp; Profile Settings</a></li>
                            <?php if ($_is_admin): ?>
                            <li><a href="security_dashboard.php"><i class="fa fa-shield"></i>&nbsp; Security Dashboard</a></li>
                            <?php endif; ?>
                            <li class="divider"></li>
                            <li><a href="logout.php"><i class="fa fa-sign-out"></i>&nbsp; Sign Out</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
(function() {
    var $bell      = document.getElementById('notif-bell');
    var $panel     = document.getElementById('notif-panel');
    var $count     = document.getElementById('notif-count');
    var $list      = document.getElementById('notif-list');
    var notifIds   = [];

    function loadNotifications() {
        if (typeof jQuery === 'undefined') return;
        jQuery.post('scripts/get_notifications.php', {}, function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (!d.success) return;
            var items = d.notifications || [];
            notifIds = items.map(function(n){ return n.id; });
            if (items.length > 0) {
                $count.textContent = items.length > 9 ? '9+' : items.length;
                $count.style.display = 'block';
                var html = '';
                items.forEach(function(n) {
                    var icon = n.type === 'warning' ? 'fa-exclamation-triangle' : n.type === 'error' ? 'fa-times-circle' : 'fa-info-circle';
                    var color = n.type === 'warning' ? '#ffc107' : n.type === 'error' ? '#dc3545' : '#26a69a';
                    html += '<div style="padding:10px 16px;border-bottom:1px solid #f5f5f5;display:flex;align-items:flex-start;gap:10px;" data-id="' + n.id + '">' +
                            '<i class="fa ' + icon + '" style="color:' + color + ';margin-top:2px;"></i>' +
                            '<div style="flex:1;font-size:13px;">' + n.message + '<div style="font-size:11px;color:#bbb;margin-top:3px;">' + (n.timestamp || '') + '</div></div>' +
                            '<span onclick="ackNotif(' + n.id + ',this)" style="cursor:pointer;color:#ccc;font-size:16px;" title="Dismiss">&times;</span>' +
                            '</div>';
                });
                $list.innerHTML = html;
            } else {
                $count.style.display = 'none';
                $list.innerHTML = '<div style="padding:20px;text-align:center;color:#bbb;font-size:13px;">No new notifications</div>';
            }
        });
    }

    window.ackNotif = function(id, el) {
        jQuery.post('scripts/acknowledge_notification.php', { id: id }, function(r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (d.success) {
                var row = el.closest ? el.closest('[data-id]') : jQuery(el).closest('[data-id]')[0];
                if (row) row.parentNode.removeChild(row);
                notifIds = notifIds.filter(function(i){ return i !== id; });
                if (notifIds.length === 0) {
                    $count.style.display = 'none';
                    $list.innerHTML = '<div style="padding:20px;text-align:center;color:#bbb;font-size:13px;">No new notifications</div>';
                } else {
                    $count.textContent = notifIds.length > 9 ? '9+' : notifIds.length;
                }
            }
        });
    };

    window.clearAllNotifications = function() {
        notifIds.forEach(function(id){ jQuery.post('scripts/acknowledge_notification.php', { id: id }); });
        notifIds = [];
        $count.style.display = 'none';
        $list.innerHTML = '<div style="padding:20px;text-align:center;color:#bbb;font-size:13px;">No new notifications</div>';
        $panel.style.display = 'none';
    };

    if ($bell) {
        $bell.addEventListener('click', function(e) {
            e.preventDefault();
            $panel.style.display = $panel.style.display === 'none' ? 'block' : 'none';
        });
        document.addEventListener('click', function(e) {
            if (!document.getElementById('notif-dropdown').contains(e.target)) {
                $panel.style.display = 'none';
            }
        });
    }

    // Poll every 60 seconds; initial load on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        loadNotifications();
        setInterval(loadNotifications, 60000);
    });
})();
</script>
