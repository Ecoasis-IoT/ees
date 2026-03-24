<?php
$_current_user_name = htmlspecialchars(
    (trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '')) ?: ($_SESSION['username'] ?? 'User')),
    ENT_QUOTES, 'UTF-8'
);
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

                    <!-- Sign Out -->
                    <li>
                        <a href="logout.php" class="icon-menu" title="Sign Out">
                            <i class="fa fa-sign-out"></i>
                        </a>
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
    var _csrfToken = <?= json_encode(generateCSRFToken()) ?>;

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

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
                    html += '<div style="padding:10px 16px;border-bottom:1px solid #f5f5f5;display:flex;align-items:flex-start;gap:10px;" data-id="' + esc(n.id) + '">' +
                            '<i class="fa ' + esc(icon) + '" style="color:' + esc(color) + ';margin-top:2px;"></i>' +
                            '<div style="flex:1;font-size:13px;">' + esc(n.message) + '<div style="font-size:11px;color:#bbb;margin-top:3px;">' + esc(n.timestamp || '') + '</div></div>' +
                            '<span onclick="ackNotif(' + parseInt(n.id, 10) + ',this)" style="cursor:pointer;color:#ccc;font-size:16px;" title="Dismiss">&times;</span>' +
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
        jQuery.post('scripts/acknowledge_notification.php', { id: id, csrf_token: _csrfToken }, function(r) {
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
        jQuery.post('scripts/acknowledge_notification.php', { mark_all: 1, csrf_token: _csrfToken });
        notifIds.forEach(function(id){ jQuery.post('scripts/acknowledge_notification.php', { id: id, csrf_token: _csrfToken }); });
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
