<?php
$_cur       = basename($_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['PHP_SELF'] ?? '');
$_is_admin  = isset($_SESSION['group_id']) && (int)$_SESSION['group_id'] === (int)ADMIN_USERGROUP_ID;
$_username  = htmlspecialchars(
    trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '')) ?: ($_SESSION['username'] ?? 'User'),
    ENT_QUOTES, 'UTF-8'
);

// Determine which groups should be open by default
$_reports_open  = in_array($_cur, ['plant.php', 'query.php']);
$_sites_open    = in_array($_cur, ['site.php', 'devices.php', 'add-site.php', 'edit-site.php', 'add-energy-meter.php', 'edit-energy-meter.php']);
$_users_open    = in_array($_cur, ['user-management.php', 'admin-settings.php', 'add-user.php', 'edit-user.php']);
?>

<!-- Mobile sidebar overlay -->
<div class="ees-sidebar-overlay" id="ees-sidebar-overlay"></div>

<aside class="ees-sidebar" id="ees-sidebar">

    <!-- Logo -->
    <div class="ees-sidebar-logo">
        <a href="dashboard">
            <?php if (defined('APP_LOGO') && APP_LOGO): ?>
            <img src="<?= htmlspecialchars(APP_LOGO, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'EES', ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <span><?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'EES', ENT_QUOTES, 'UTF-8') ?></span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="ees-nav">

        <div class="ees-nav-section-label">Main</div>

        <!-- Dashboard -->
        <a href="dashboard"
           class="ees-nav-item<?= $_cur === 'dashboard.php' ? ' active' : '' ?>">
            <i class="fa fa-tachometer nav-icon"></i>
            <span class="nav-label">Dashboard</span>
        </a>

        <!-- Reports -->
        <div class="ees-nav-group<?= $_reports_open ? ' open' : '' ?>">
            <button class="ees-nav-item">
                <i class="fa fa-bar-chart nav-icon"></i>
                <span class="nav-label">Reports</span>
                <i class="fa fa-chevron-right ees-nav-arrow"></i>
            </button>
            <div class="ees-nav-sub">
                <a href="plant"
                   class="ees-nav-subitem<?= $_cur === 'plant.php' ? ' active' : '' ?>">
                   <i class="fa fa-line-chart ees-nav-subicon" aria-hidden="true"></i>
                   <span>Plant Report</span>
                </a>
                <a href="query"
                   class="ees-nav-subitem<?= $_cur === 'query.php' ? ' active' : '' ?>">
                   <i class="fa fa-search ees-nav-subicon" aria-hidden="true"></i>
                   <span>Query</span>
                </a>
            </div>
        </div>

        <div class="ees-nav-section-label">Management</div>

        <!-- Site Management -->
        <div class="ees-nav-group<?= $_sites_open ? ' open' : '' ?>">
            <button class="ees-nav-item">
                <i class="fa fa-sitemap nav-icon"></i>
                <span class="nav-label">Sites</span>
                <i class="fa fa-chevron-right ees-nav-arrow"></i>
            </button>
            <div class="ees-nav-sub">
                <a href="site"
                   class="ees-nav-subitem<?= $_cur === 'site.php' ? ' active' : '' ?>">
                   <i class="fa fa-building-o ees-nav-subicon" aria-hidden="true"></i>
                   <span>All Sites</span>
                </a>
            </div>
        </div>

        <?php if ($_is_admin): ?>
        <!-- User Management (admin only; API scripts require admin) -->
        <div class="ees-nav-group<?= $_users_open ? ' open' : '' ?>">
            <button class="ees-nav-item">
                <i class="fa fa-users nav-icon"></i>
                <span class="nav-label">Users</span>
                <i class="fa fa-chevron-right ees-nav-arrow"></i>
            </button>
            <div class="ees-nav-sub">
                <a href="user-management"
                   class="ees-nav-subitem<?= $_cur === 'user-management.php' ? ' active' : '' ?>">
                   <i class="fa fa-user ees-nav-subicon" aria-hidden="true"></i>
                   <span>All Users</span>
                </a>
                <a href="admin-settings"
                   class="ees-nav-subitem<?= $_cur === 'admin-settings.php' ? ' active' : '' ?>">
                   <i class="fa fa-cog ees-nav-subicon" aria-hidden="true"></i>
                   <span>Admin Settings</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="ees-nav-section-label">Other</div>

        <!-- Archive -->
        <a href="archive"
           class="ees-nav-item<?= $_cur === 'archive.php' ? ' active' : '' ?>">
            <i class="fa fa-archive nav-icon"></i>
            <span class="nav-label">Archive</span>
        </a>

        <!-- Notifications -->
        <a href="notifications"
           class="ees-nav-item<?= $_cur === 'notifications.php' ? ' active' : '' ?>">
            <i class="fa fa-bell nav-icon"></i>
            <span class="nav-label">Notifications</span>
        </a>

        <!-- Profile -->
        <a href="profile"
           class="ees-nav-item<?= $_cur === 'profile.php' ? ' active' : '' ?>">
            <i class="fa fa-user-circle-o nav-icon"></i>
            <span class="nav-label">My Profile</span>
        </a>

        <?php if ($_is_admin): ?>
        <!-- Security -->
        <a href="security_dashboard"
           class="ees-nav-item<?= $_cur === 'security_dashboard.php' ? ' active' : '' ?>">
            <i class="fa fa-shield nav-icon"></i>
            <span class="nav-label">Security</span>
        </a>
        <?php endif; ?>

    </nav>

    <!-- Sidebar footer -->
    <div class="ees-sidebar-footer">
        <div class="ees-sidebar-footer-avatar">
            <i class="fa fa-user"></i>
        </div>
        <span class="ees-sidebar-footer-name"><?= $_username ?></span>
    </div>

</aside>

<script>
(function () {
    // Submenu toggle
    document.querySelectorAll('.ees-nav-group > .ees-nav-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = this.closest('.ees-nav-group');
            if (group) group.classList.toggle('open');
        });
    });

    // Mobile sidebar toggle
    var sidebar  = document.getElementById('ees-sidebar');
    var overlay  = document.getElementById('ees-sidebar-overlay');
    var toggleBtn = document.getElementById('ees-sidebar-toggle');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay && sidebar) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }
}());
</script>
