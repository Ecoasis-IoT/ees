/**
 * Admin Settings — Gateway, General, Users, Sites, Devices
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
    // DataTables (same pattern as site.php / user-management.php)
    // =====================================================
    function destroyAdminTable(tableSelector) {
        if (typeof $.fn.DataTable !== 'function') return;
        if ($.fn.DataTable.isDataTable(tableSelector)) {
            $(tableSelector).DataTable().destroy();
        }
    }

    function initAdminTable(tableSelector, extraOptions) {
        if ($(tableSelector).length === 0 || typeof $.fn.DataTable !== 'function') return;
        destroyAdminTable(tableSelector);
        var base = {
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
            order: [[0, 'asc']],
            autoWidth: false,
            dom: 'lfrtip',
            language: {
                search: 'Search',
                lengthMenu: 'Show _MENU_',
                infoFiltered: '(filtered from _MAX_ total)',
                zeroRecords: 'No matching records found'
            }
        };
        $(tableSelector).DataTable($.extend(true, {}, base, extraOptions || {}));
    }

    // =====================================================
    // General Tab (global ChirpStack + future settings)
    // =====================================================
    function tokenLabel(site) {
        if (site.token_set) {
            return '<span class="badge bg-primary">DB token</span>';
        }
        if (site.env_token_set && site.env_token_key) {
            return '<span class="badge bg-secondary">' + esc(site.env_token_key) + '</span>';
        }
        return '<span class="text-muted">—</span>';
    }

    function loadGeneralSettings() {
        $.get('scripts/admin/get_settings', function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;

            if (r.status === 'migration_required') {
                var mig = document.getElementById('gateway-migration-alert');
                if (mig) {
                    mig.textContent = r.message;
                    mig.style.display = 'block';
                }
                $('#btn-poll-all, #btn-save-gateway').prop('disabled', true);
                return;
            }

            if (r.status !== 'ok') {
                showAlert('danger', r.message || 'Failed to load settings');
                return;
            }

            var g = (r.gateway || {}).global || {};
            $('#api_url').val(g.api_url || '');
            $('#tenant_id').val(g.tenant_id || '');
            $('#offline_threshold_seconds').val(g.offline_threshold_seconds || 900);
        }).fail(function () {
            showAlert('danger', 'Failed to load general settings');
        });
    }

    $('#global-gateway-form').on('submit', function (e) {
        e.preventDefault();
        var data = $(this).serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.csrf_token = csrfToken;
        $.post('scripts/admin/save_settings', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') showAlert('success', r.message);
            else showAlert('danger', r.message);
        });
    });

    $('#btn-poll-all').on('click', function () {
        var btn = this;
        if (typeof EES !== 'undefined' && EES.btnLoad) EES.btnLoad(btn, 'Polling…');
        else btn.disabled = true;

        $.post('scripts/admin/poll_all_gateways', { csrf_token: csrfToken }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') { showAlert('success', r.message); loadSites(); }
            else showAlert('danger', r.message);
        }).always(function () {
            if (typeof EES !== 'undefined' && EES.btnReset) EES.btnReset(btn);
            else btn.disabled = false;
        });
    });

    window.submitSiteGateway = function (id) {
        var form = document.getElementById('site-form');
        var data = {};
        if (form) {
            data = $('#site-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        }
        data.id = id;
        data.csrf_token = csrfToken;
        if ($('#gateway_poll_enabled').is(':checked')) data.gateway_poll_enabled = 1;
        if ($('#clear_token').is(':checked')) data.clear_token = 1;

        $.post('scripts/admin/save_site_gateway', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') { showAlert('success', r.message); loadSites(); }
            else showAlert('danger', r.message);
        });
    };

    window.testSiteGateway = function (id) {
        var data = { id: id, csrf_token: csrfToken };
        if (document.getElementById('site-form')) {
            $.extend(data, $('#site-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {}));
            if ($('#gateway_poll_enabled').is(':checked')) data.gateway_poll_enabled = 1;
        }
        $.post('scripts/admin/test_site_gateway', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') {
                showAlert('success', r.message + (r.last_seen ? ' — last seen: ' + r.last_seen : ''));
                loadSites();
            } else showAlert('danger', r.message || 'Test failed');
        });
    };

    $(document).on('click', '.test-gateway', function () {
        $.post('scripts/admin/test_site_gateway', { id: $(this).data('id'), csrf_token: csrfToken }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') {
                showAlert('success', r.message + (r.last_seen ? ' — last seen: ' + r.last_seen : ''));
                loadSites();
            } else showAlert('danger', r.message || 'Test failed');
        });
    });

    // =====================================================
    // Users Tab
    // =====================================================
    function loadUsers() {
        $.get('scripts/admin/get_users', function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            destroyAdminTable('#tbl-users');
            var tbody = $('#tbl-users tbody').empty();
            if (r.status !== 'ok') {
                showAlert('danger', r.message);
                initAdminTable('#tbl-users', {
                    language: {
                        info: 'Showing _START_ to _END_ of _TOTAL_ users',
                        infoEmpty: 'No users'
                    },
                    columnDefs: [{ targets: 5, orderable: false, searchable: false }]
                });
                return;
            }
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
            initAdminTable('#tbl-users', {
                language: {
                    info: 'Showing _START_ to _END_ of _TOTAL_ users',
                    infoEmpty: 'No users'
                },
                columnDefs: [{ targets: 5, orderable: false, searchable: false }]
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
        var btn = document.querySelector('#modal-body .btn-primary');
        EES.btnLoad(btn, 'Creating…');
        var data = $('#user-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.csrf_token = csrfToken;
        $.post('scripts/admin/user_create', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadUsers(); }
            else { EES.btnReset(btn); showAlert('danger', r.message); }
        }).fail(function() { EES.btnReset(btn); showAlert('danger', 'Request failed'); });
    };

    window.submitUserUpdate = function (id) {
        var btn = document.querySelector('#modal-body .btn-primary');
        EES.btnLoad(btn, 'Saving…');
        var data = $('#user-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.id = id;
        data.csrf_token = csrfToken;
        $.post('scripts/admin/user_update', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadUsers(); }
            else { EES.btnReset(btn); showAlert('danger', r.message); }
        }).fail(function() { EES.btnReset(btn); showAlert('danger', 'Request failed'); });
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
        $.post('scripts/admin/user_delete', { id: id, csrf_token: csrfToken }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status === 'ok') { showAlert('success', 'User deleted'); loadUsers(); }
            else showAlert('danger', r.message);
        });
    });

    // =====================================================
    // Sites Tab
    // =====================================================
    function loadSites() {
        $.get('scripts/admin/get_sites', function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            destroyAdminTable('#tbl-sites');
            var tbody = $('#tbl-sites tbody').empty();
            var hasGw = !!r.gateway_schema;
            if (r.status !== 'ok') {
                showAlert('danger', r.message);
                initAdminTable('#tbl-sites', {
                    columnDefs: [{ targets: 8, orderable: false, searchable: false }]
                });
                return;
            }
            r.data.forEach(function (s) {
                var gwBadge = parseInt(s.gateway_status, 10) === 1
                    ? '<span class="status-online">ONLINE</span>'
                    : '<span class="status-offline">OFFLINE</span>';
                var poll = hasGw && parseInt(s.gateway_poll_enabled, 10) === 1 ? 'Yes' : 'No';
                var row = '<tr>' +
                    '<td>' + esc(s.site_name) + '</td>' +
                    '<td>' + esc(s.db_name) + '</td>' +
                    '<td>' + (s.capacity || '-') + ' kWp</td>' +
                    '<td>' + (hasGw ? '<code>' + esc(s.gateway_eui || '—') + '</code>' : '—') + '</td>' +
                    '<td>' + (hasGw ? tokenLabel(s) : '—') + '</td>' +
                    '<td>' + (hasGw ? poll : '—') + '</td>' +
                    '<td>' + (hasGw ? esc(s.gateway_last_seen || '—') : '—') + '</td>' +
                    '<td>' + gwBadge + '</td>' +
                    '<td class="action-btn-group">' +
                        '<button class="btn btn-sm btn-info view-devices" data-id="' + s.id + '" data-name="' + esc(s.site_name) + '"><i class="icon-energy"></i></button>' +
                        '<button class="btn btn-sm btn-warning edit-site" data-id="' + s.id + '" data-name="' + esc(s.site_name) + '" data-db="' + esc(s.db_name) + '" data-cap="' + (s.capacity || 0) + '" data-eui="' + esc(s.gateway_eui || '') + '" data-poll="' + (s.gateway_poll_enabled || 0) + '" data-env-key="' + esc(s.env_token_key || '') + '" data-has-gw="' + (hasGw ? '1' : '0') + '"><i class="fa fa-edit"></i></button>' +
                        (hasGw ? '<button class="btn btn-sm btn-secondary test-gateway" data-id="' + s.id + '" title="Test gateway"><i class="fa fa-plug"></i></button>' : '') +
                        '<button class="btn btn-sm btn-danger delete-site" data-id="' + s.id + '" data-name="' + esc(s.site_name) + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
                tbody.append(row);
            });
            initAdminTable('#tbl-sites', {
                pageLength: 10,
                columnDefs: [{ targets: 8, orderable: false, searchable: false }]
            });
        });
    }

    $(document).on('click', '.edit-site', function () {
        var d = $(this).data();
        var gwSection = '';
        if (d.hasGw === 1 || d.hasGw === '1') {
            var envHint = d.envKey
                ? '<small class="text-muted">Leave token blank to use .env: <code>' + esc(d.envKey) + '</code></small>'
                : '<small class="text-muted">No .env mapping — enter token below</small>';
            gwSection =
                '<hr><h5>Gateway</h5>' +
                '<div class="form-group mb-2"><label>Gateway EUI</label>' +
                '<input class="form-control" name="gateway_eui" value="' + esc(d.eui) + '"></div>' +
                '<div class="form-group mb-2"><label>ChirpStack API Token</label>' +
                '<input class="form-control" type="password" name="chirpstack_token" autocomplete="new-password"></div>' +
                envHint +
                '<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="clear_token" id="clear_token">' +
                '<label class="form-check-label" for="clear_token">Clear stored token</label></div>' +
                '<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="gateway_poll_enabled" ' +
                (parseInt(d.poll, 10) === 1 ? 'checked' : '') + '>' +
                '<label class="form-check-label" for="gateway_poll_enabled">Enable gateway polling</label></div>';
        }

        openModal(
            '<h4>Edit Site — ' + esc(d.name) + '</h4>' +
            '<button class="close-modal" onclick="closeAdminModal()">&times;</button>' +
            '<form id="site-form">' +
                '<div class="form-group mb-2"><label>Site Name</label><input class="form-control" name="site_name" value="' + esc(d.name) + '" required></div>' +
                '<div class="form-group mb-2"><label>DB Name</label><input class="form-control" name="db_name" value="' + esc(d.db) + '"></div>' +
                '<div class="form-group mb-2"><label>Capacity (kWp)</label><input class="form-control" type="number" step="0.01" name="capacity" value="' + d.cap + '"></div>' +
                gwSection +
                '<button type="button" class="btn btn-primary" onclick="submitSiteUpdate(' + d.id + ')">Save Site</button>' +
                ((d.hasGw === 1 || d.hasGw === '1') ? '<button type="button" class="btn btn-info ms-2" onclick="submitSiteGateway(' + d.id + ')">Save Gateway</button>' : '') +
                ((d.hasGw === 1 || d.hasGw === '1') ? '<button type="button" class="btn btn-secondary ms-2" onclick="testSiteGateway(' + d.id + ')">Test</button>' : '') +
                '<button type="button" class="btn btn-light ms-2" onclick="closeAdminModal()">Cancel</button>' +
            '</form>'
        );
    });

    window.submitSiteUpdate = function (id) {
        var btn = document.querySelector('#modal-body .btn-primary');
        EES.btnLoad(btn, 'Saving…');
        var data = $('#site-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.id = id;
        data.csrf_token = csrfToken;
        $.post('scripts/admin/site_update', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadSites(); }
            else { EES.btnReset(btn); showAlert('danger', r.message); }
        }).fail(function() { EES.btnReset(btn); showAlert('danger', 'Request failed'); });
    };

    $(document).on('click', '.delete-site', function () {
        var id = $(this).data('id'), name = $(this).data('name');
        if (!confirm('Delete site "' + name + '"?')) return;
        $.post('scripts/admin/site_delete', { id: id, csrf_token: csrfToken }, function (res) {
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
        $.get('scripts/admin/get_devices', { site_id: siteId }, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            destroyAdminTable('#tbl-devices');
            var tbody = $('#tbl-devices tbody').empty();
            if (r.status !== 'ok') {
                showAlert('danger', r.message);
                initAdminTable('#tbl-devices', {
                    language: {
                        info: 'Showing _START_ to _END_ of _TOTAL_ devices',
                        infoEmpty: 'No devices'
                    },
                    columnDefs: [{ targets: 2, orderable: false, searchable: false }]
                });
                return;
            }
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
            initAdminTable('#tbl-devices', {
                language: {
                    info: 'Showing _START_ to _END_ of _TOTAL_ devices',
                    infoEmpty: 'No devices'
                },
                columnDefs: [{ targets: 2, orderable: false, searchable: false }]
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
        var btn = document.querySelector('#modal-body .btn-primary');
        EES.btnLoad(btn, 'Saving…');
        var data = $('#device-form').serializeArray().reduce(function (o, f) { o[f.name] = f.value; return o; }, {});
        data.site_id   = currentSiteId;
        data.device_id = deviceId;
        data.csrf_token = csrfToken;
        $.post('scripts/admin/device_update', data, function (res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            closeModal();
            if (r.status === 'ok') { showAlert('success', r.message); loadDevices(currentSiteId); }
            else { EES.btnReset(btn); showAlert('danger', r.message); }
        }).fail(function() { EES.btnReset(btn); showAlert('danger', 'Request failed'); });
    };

    $(document).on('click', '.delete-device', function () {
        var devId = $(this).data('id'), devName = $(this).data('name');
        if (!confirm('Delete device "' + devName + '"?')) return;
        $.post('scripts/admin/device_delete', { site_id: currentSiteId, device_id: devId, csrf_token: csrfToken }, function (res) {
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

    function activateTabFromHash() {
        var hash = window.location.hash;
        if (!hash) return;
        var link = document.querySelector('#adminTabs a[href="' + hash + '"]');
        if (link && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(link).show();
        } else if (link) {
            $(link).tab('show');
        }
    }

    // =====================================================
    // Init
    // =====================================================
    $(document).ready(function () {
        loadGeneralSettings();
        loadUsers();
        loadSites();
        activateTabFromHash();

        $(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function () {
            if (typeof $.fn.DataTable !== 'function') return;
            ['#tbl-users', '#tbl-sites', '#tbl-devices'].forEach(function (sel) {
                if ($.fn.DataTable.isDataTable(sel)) {
                    $(sel).DataTable().columns.adjust();
                }
            });
        });

        // Close modal on overlay click
        document.getElementById('modal-overlay').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });
    });

}(jQuery));
