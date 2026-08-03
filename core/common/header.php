<?php
$_current_user_name = htmlspecialchars(
    (trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '')) ?: ($_SESSION['username'] ?? 'User')),
    ENT_QUOTES, 'UTF-8'
);
?>
<header class="ees-topbar" id="ees-topbar">

    <!-- Sidebar hide/show toggle (same pattern as omnipv) -->
    <button class="ees-topbar-toggle" id="ees-sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="true">
        <span class="ees-sidenav-toggler-inner" aria-hidden="true">
            <i class="ees-sidenav-toggler-line"></i>
            <i class="ees-sidenav-toggler-line"></i>
            <i class="ees-sidenav-toggler-line"></i>
        </span>
    </button>

    <div class="ees-topbar-spacer"></div>

    <div class="ees-topbar-right">

        <!-- Notifications -->
        <div class="ees-notif-wrapper" id="notif-dropdown">
            <button class="ees-topbar-btn" id="notif-bell" title="Notifications">
                <i class="fa fa-bell-o"></i>
                <span id="notif-count" class="ees-notif-badge" style="display:none;"></span>
            </button>
            <div id="notif-panel" class="ees-notif-panel" style="display:none;">
                <div class="ees-notif-panel-header">Notifications</div>
                <div id="notif-list" style="padding:6px 0;"></div>
                <div class="ees-notif-panel-footer">
                    <a href="notifications">View all notifications</a>
                    <span style="color:#CBD5E1;"> · </span>
                    <a href="#" onclick="clearAllNotifications();return false;">Clear all</a>
                </div>
            </div>
        </div>

        <!-- Current user -->
        <span class="ees-topbar-username"><?= $_current_user_name ?></span>

        <!-- Sign out -->
        <a href="logout" class="ees-topbar-btn" title="Sign Out">
            <i class="fa fa-sign-out"></i>
        </a>

    </div>
</header>

<script>
(function () {
    'use strict';

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
        jQuery.post('scripts/get_notifications', {}, function (r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (!d.success) return;
            var items = d.notifications || [];
            notifIds = items.map(function (n) { return n.id; });
            if (items.length > 0) {
                $count.textContent = items.length > 9 ? '9+' : items.length;
                $count.style.display = 'block';
                var html = '';
                items.forEach(function (n) {
                    var icon  = n.type === 'warning' ? 'fa-exclamation-triangle'
                        : n.type === 'error' || n.type === 'danger' ? 'fa-times-circle'
                        : n.type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
                    var color = n.type === 'warning' ? '#F59E0B'
                        : n.type === 'error' || n.type === 'danger' ? '#EF4444'
                        : n.type === 'success' ? '#70AD47' : '#3B82F6';
                    var actionHtml = '';
                    if (n.actionUrl && n.actionLabel) {
                        actionHtml = '<a href="' + esc(n.actionUrl) + '" style="font-size:12px;color:#26a69a;display:inline-block;margin-top:4px;">'
                            + esc(n.actionLabel) + '</a>';
                    }
                    html += '<div style="padding:10px 16px;border-bottom:1px solid #F1F5F9;display:flex;align-items:flex-start;gap:10px;" data-id="' + esc(n.id) + '">' +
                            '<i class="fa ' + esc(icon) + '" style="color:' + esc(color) + ';margin-top:2px;font-size:14px;flex-shrink:0;"></i>' +
                            '<div style="flex:1;font-size:13px;color:#0F172A;">' + esc(n.message) +
                            '<div style="font-size:11px;color:#94A3B8;margin-top:3px;">' + esc(n.timestamp || '') + '</div>' +
                            actionHtml + '</div>' +
                            '<span onclick="ackNotif(' + parseInt(n.id, 10) + ',this)" style="cursor:pointer;color:#CBD5E1;font-size:18px;line-height:1;" title="Dismiss">&times;</span>' +
                            '</div>';
                });
                $list.innerHTML = html;
            } else {
                $count.style.display = 'none';
                $list.innerHTML = '<div style="padding:24px 16px;text-align:center;color:#94A3B8;font-size:13px;"><i class="fa fa-bell-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>No new notifications</div>';
            }
        });
    }

    window.ackNotif = function (id, el) {
        jQuery.post('scripts/acknowledge_notification', { id: id, csrf_token: _csrfToken }, function (r) {
            var d = typeof r === 'string' ? JSON.parse(r) : r;
            if (d.success) {
                var row = el.closest ? el.closest('[data-id]') : jQuery(el).closest('[data-id]')[0];
                if (row) row.parentNode.removeChild(row);
                notifIds = notifIds.filter(function (i) { return i !== id; });
                if (notifIds.length === 0) {
                    $count.style.display = 'none';
                    $list.innerHTML = '<div style="padding:24px 16px;text-align:center;color:#94A3B8;font-size:13px;"><i class="fa fa-bell-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>No new notifications</div>';
                } else {
                    $count.textContent = notifIds.length > 9 ? '9+' : notifIds.length;
                }
            }
        });
    };

    window.clearAllNotifications = function () {
        jQuery.post('scripts/acknowledge_notification', { mark_all: 1, csrf_token: _csrfToken });
        notifIds.forEach(function (id) {
            jQuery.post('scripts/acknowledge_notification', { id: id, csrf_token: _csrfToken });
        });
        notifIds = [];
        $count.style.display = 'none';
        $list.innerHTML = '<div style="padding:24px 16px;text-align:center;color:#94A3B8;font-size:13px;"><i class="fa fa-bell-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>No new notifications</div>';
        $panel.style.display = 'none';
    };

    // Bell toggle
    if ($bell) {
        $bell.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $panel.style.display = $panel.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', function (e) {
            var wrapper = document.getElementById('notif-dropdown');
            if (wrapper && !wrapper.contains(e.target)) {
                $panel.style.display = 'none';
            }
        });
    }

    // Poll every 60 s; initial load on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        loadNotifications();
        setInterval(loadNotifications, 60000);
    });

    window.EES_loadNotifications = loadNotifications;
}());
</script>
