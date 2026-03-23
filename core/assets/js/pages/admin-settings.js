/**
 * Admin Settings — tabbed CRUD interface
 * Tabs: Users | Sites | Devices
 */
(function ($) {
    'use strict';

    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // =====================================================
    // Utility
    // =====================================================
    function showAlert(type, message) {
        var el = document.getElementById('admin-alert');
        if (!el) return;
        el.className = 'alert alert-' + type;
        el.textContent = message;
        el.style.display = 'block';
        setTimeout(function () { el.style.display = 'none'; }, 5000);
    }

    function closeModal() {
        document.getElementById('modal-overlay').classList.remove('active');
        document.getElementById('modal-body').innerHTML = '';
    }

    function openModal(html) {
        document.getElementById('modal-body').innerHTML = html;
        document.getElementById('modal-overlay').classList.add('active');
    }

    // =====================================================
    // Users Tab
    // =====================================================
    function loadUsers() {
        $.get('scripts/admin/get_users.php', function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            var tbody = $('#tbl-users tbody').empty();
            if (r.status !== 'ok') { showAlert('danger', r.message); return; }
            r.data.forEach(function (u) {
                var role = parseInt(u.group_id) === 1
                    ? '<span class="badge-admin">Admin</span>'
                    : '<span class="badge-user">User</span>';
                var row = '<tr>' +
                    '<td>' + esc(u.fullname) + '</td>' +
                    '<td>' + esc(u.username) + '</td>' +
                    '<td>' + esc(u.email) + '</td>' +
                    '<td>' + role + '</td>' +
                    '<td>' + esc(u.date_added || '') + '</td>' +
                    '<td class="action-btn-group">' +
                        '<button class="btn btn-sm btn-warning edit-user" data-id="' + u.id + '" data-fname="' + esc(u.firstname) + '" data-lname="' + esc(u.lastname) + '" data-email="' + esc(u.email) + '" data-gid="' + u.group_id + '"><i class="fa fa-edit"></i></button>' +
                        '<button class="btn btn-sm btn-danger delete-user" data-id="' + u.id + '" data-name="' + esc(u.fullname) + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
                tbody.append(row);
            });
        });
    }

    function showAddUserModal() {
        openModal(
            '<h4>Add User</h4>' +
            '<button class="close-modal" onclick="closeAdminModal()">&times;</button>' +
            '<form id="user-form">' +
                '<div class="form-group mb-2"><input class="form-control" name="firstname" placeholder="First Name" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" name="lastname" placeholder="Last Name" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" name="username" placeholder="Username" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" type="email" name="email" placeholder="Email" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" type="password" name="password" placeholder="Password" required></div>' +
                '<div class="form-group mb-2"><select class="form-control" name="group_id"><option value="0">User</option><option value="1">Admin</option></select></div>' +
                '<button type="button" class="btn btn-primary" onclick="submitUserCreate()">Create</button>' +
                '<button type="button" class="btn btn-secondary ml-2" onclick="closeAdminModal()">Cancel</button>' +
            '</form>'
        );
    }

    window.submitUserCreate = function () {
        var data = $('#user-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.csrf_token = csrfToken;
        $.post('scripts/admin/user_create.php', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadUsers(); }
            else showAlert('danger', r.message);
        });
    };

    window.submitUserUpdate = function (id) {
        var data = $('#user-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.id = id;
        data.csrf_token = csrfToken;
        $.post('scripts/admin/user_update.php', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadUsers(); }
            else showAlert('danger', r.message);
        });
    };

    $(document).on('click', '.edit-user', function () {
        var d = $(this).data();
        openModal(
            '<h4>Edit User</h4>' +
            '<button class="close-modal" onclick="closeAdminModal()">&times;</button>' +
            '<form id="user-form">' +
                '<div class="form-group mb-2"><input class="form-control" name="firstname" value="' + esc(d.fname) + '" placeholder="First Name" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" name="lastname" value="' + esc(d.lname) + '" placeholder="Last Name" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" type="email" name="email" value="' + esc(d.email) + '" placeholder="Email" required></div>' +
                '<div class="form-group mb-2"><input class="form-control" type="password" name="password" placeholder="New Password (leave blank to keep)"></div>' +
                '<div class="form-group mb-2"><select class="form-control" name="group_id"><option value="0"' + (parseInt(d.gid) !== 1 ? ' selected' : '') + '>User</option><option value="1"' + (parseInt(d.gid) === 1 ? ' selected' : '') + '>Admin</option></select></div>' +
                '<button type="button" class="btn btn-primary" onclick="submitUserUpdate(' + d.id + ')">Save</button>' +
                '<button type="button" class="btn btn-secondary ml-2" onclick="closeAdminModal()">Cancel</button>' +
            '</form>'
        );
    });

    $(document).on('click', '.delete-user', function () {
        var id = $(this).data('id'), name = $(this).data('name');
        if (!confirm('Delete user "' + name + '"? This cannot be undone.')) return;
        $.post('scripts/admin/user_delete.php', { id: id, csrf_token: csrfToken }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') { showAlert('success', 'User deleted'); loadUsers(); }
            else showAlert('danger', r.message);
        });
    });

    // =====================================================
    // Sites Tab
    // =====================================================
    function loadSites() {
        $.get('scripts/admin/get_sites.php', function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            var tbody = $('#tbl-sites tbody').empty();
            if (r.status !== 'ok') { showAlert('danger', r.message); return; }
            r.data.forEach(function (s) {
                var gw = parseInt(s.gateway_status) === 1
                    ? '<span class="status-online">ONLINE</span>'
                    : '<span class="status-offline">OFFLINE</span>';
                var row = '<tr>' +
                    '<td>' + esc(s.site_name) + '</td>' +
                    '<td>' + esc(s.db_name) + '</td>' +
                    '<td>' + (s.capacity || '-') + ' kWp</td>' +
                    '<td>' + gw + '</td>' +
                    '<td class="action-btn-group">' +
                        '<button class="btn btn-sm btn-info view-devices" data-id="' + s.id + '" data-name="' + esc(s.site_name) + '"><i class="icon-energy"></i> Devices</button>' +
                        '<button class="btn btn-sm btn-warning edit-site" data-id="' + s.id + '" data-name="' + esc(s.site_name) + '" data-db="' + esc(s.db_name) + '" data-cap="' + (s.capacity || 0) + '"><i class="fa fa-edit"></i></button>' +
                        '<button class="btn btn-sm btn-danger delete-site" data-id="' + s.id + '" data-name="' + esc(s.site_name) + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
                tbody.append(row);
            });
        });
    }

    $(document).on('click', '.edit-site', function () {
        var d = $(this).data();
        openModal(
            '<h4>Edit Site</h4>' +
            '<button class="close-modal" onclick="closeAdminModal()">&times;</button>' +
            '<form id="site-form">' +
                '<div class="form-group mb-2"><label>Site Name</label><input class="form-control" name="site_name" value="' + esc(d.name) + '" required></div>' +
                '<div class="form-group mb-2"><label>DB Name</label><input class="form-control" name="db_name" value="' + esc(d.db) + '"></div>' +
                '<div class="form-group mb-2"><label>Capacity (kWp)</label><input class="form-control" type="number" step="0.01" name="capacity" value="' + d.cap + '"></div>' +
                '<button type="button" class="btn btn-primary" onclick="submitSiteUpdate(' + d.id + ')">Save</button>' +
                '<button type="button" class="btn btn-secondary ml-2" onclick="closeAdminModal()">Cancel</button>' +
            '</form>'
        );
    });

    window.submitSiteUpdate = function (id) {
        var data = $('#site-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.id = id;
        data.csrf_token = csrfToken;
        $.post('scripts/admin/site_update.php', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadSites(); }
            else showAlert('danger', r.message);
        });
    };

    $(document).on('click', '.delete-site', function () {
        var id = $(this).data('id'), name = $(this).data('name');
        if (!confirm('Delete site "' + name + '"?')) return;
        $.post('scripts/admin/site_delete.php', { id: id, csrf_token: csrfToken }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') { showAlert('success', 'Site deleted'); loadSites(); }
            else showAlert('danger', r.message);
        });
    });

    // =====================================================
    // Devices Tab
    // =====================================================
    var currentSiteId = null;

    $(document).on('click', '.view-devices', function () {
        currentSiteId = $(this).data('id');
        var siteName  = $(this).data('name');
        $('#devices-site-name').text(siteName);
        loadDevices(currentSiteId);
        $('#tab-devices-link').tab('show');
    });

    function loadDevices(siteId) {
        if (!siteId) return;
        currentSiteId = siteId;
        $.get('scripts/admin/get_devices.php', { site_id: siteId }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            var tbody = $('#tbl-devices tbody').empty();
            if (r.status !== 'ok') { showAlert('danger', r.message); return; }
            r.data.forEach(function (d) {
                var row = '<tr>' +
                    '<td>' + esc(d.meter_name) + '</td>' +
                    '<td>' + esc(d.device_type || '') + '</td>' +
                    '<td class="action-btn-group">' +
                        '<button class="btn btn-sm btn-warning edit-device" data-id="' + d.id + '" data-name="' + esc(d.meter_name) + '" data-type="' + esc(d.device_type || '') + '"><i class="fa fa-edit"></i></button>' +
                        '<button class="btn btn-sm btn-danger delete-device" data-id="' + d.id + '" data-name="' + esc(d.meter_name) + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
                tbody.append(row);
            });
        });
    }

    $(document).on('click', '.edit-device', function () {
        var d = $(this).data();
        openModal(
            '<h4>Edit Device</h4>' +
            '<button class="close-modal" onclick="closeAdminModal()">&times;</button>' +
            '<form id="device-form">' +
                '<div class="form-group mb-2"><label>Device Name</label><input class="form-control" name="meter_name" value="' + esc(d.name) + '" required></div>' +
                '<div class="form-group mb-2"><label>Device Type</label><input class="form-control" name="device_type" value="' + esc(d.type) + '"></div>' +
                '<button type="button" class="btn btn-primary" onclick="submitDeviceUpdate(' + d.id + ')">Save</button>' +
                '<button type="button" class="btn btn-secondary ml-2" onclick="closeAdminModal()">Cancel</button>' +
            '</form>'
        );
    });

    window.submitDeviceUpdate = function (deviceId) {
        var data = $('#device-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.site_id   = currentSiteId;
        data.device_id = deviceId;
        data.csrf_token = csrfToken;
        $.post('scripts/admin/device_update.php', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadDevices(currentSiteId); }
            else showAlert('danger', r.message);
        });
    };

    $(document).on('click', '.delete-device', function () {
        var devId = $(this).data('id'), devName = $(this).data('name');
        if (!confirm('Delete device "' + devName + '"?')) return;
        $.post('scripts/admin/device_delete.php', { site_id: currentSiteId, device_id: devId, csrf_token: csrfToken }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') { showAlert('success', 'Device deleted'); loadDevices(currentSiteId); }
            else showAlert('danger', r.message);
        });
    });

    // =====================================================
    // Global helpers
    // =====================================================
    window.closeAdminModal = closeModal;
    window.showAddUserModal = showAddUserModal;

    function esc(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // =====================================================
    // Init
    // =====================================================
    $(document).ready(function () {
        loadUsers();
        loadSites();

        // Close modal on overlay click
        document.getElementById('modal-overlay').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });
    });

}(jQuery));
