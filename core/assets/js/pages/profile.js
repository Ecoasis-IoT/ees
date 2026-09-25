/**
 * Profile page — two-factor setup, backup codes, and required-setup lock.
 */
(function () {
    'use strict';

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            (typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
    }

    function copyText(text, okTitle) {
        if (!text) {
            Swal.fire({ icon: 'info', title: 'Nothing to copy', confirmButtonColor: '#3D8881' });
            return;
        }
        var done = function () {
            Swal.fire({ icon: 'success', title: okTitle || 'Copied', timer: 1400, showConfirmButton: false });
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () {
                window.prompt('Copy:', text);
            });
            return;
        }
        window.prompt('Copy:', text);
    }

    window.get2FAStatus = function () {
        $.ajax({
            type: 'POST',
            url: 'scripts/setup_2fa',
            data: { action: 'status', csrf_token: csrfToken() },
            success: function (dataResult) {
                var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                if (data.statusCode === 'success' && data.data) {
                    update2FAUI(data.data);
                } else {
                    update2FAUI({ enabled: false, has_secret: false, backup_codes_count: 0 });
                }
            },
            error: function () {
                var el = document.getElementById('2fa-status-text');
                if (el) el.textContent = 'Error loading 2FA status';
            }
        });
    };

    function update2FAUI(status) {
        status = status || { enabled: false, has_secret: false, backup_codes_count: 0 };
        if (status.enabled) {
            var statusText = '<span class="text-success"><i class="fa fa-check-circle"></i> Two-Factor Authentication is <strong>enabled</strong></span>';
            if (status.backup_codes_count > 0) {
                statusText += '<br><small class="text-muted">You have ' + status.backup_codes_count + ' backup code(s) remaining.</small>';
            } else {
                statusText += '<br><small class="text-warning">No backup codes remaining. Consider regenerating them.</small>';
            }
            $('#2fa-status-text').html(statusText);
            $('#2fa-setup-section').hide();
            $('#2fa-qr-section').hide();
            renderSavedBackupCodes(status.backup_codes || []);
            if (status.mandatory) {
                $('#2fa-enabled-section').hide();
            } else {
                $('#2fa-enabled-section').show();
            }
        } else if (status.has_secret) {
            $('#2fa-status-text').html('<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> 2FA secret generated but not enabled. Complete setup to enable.</span>');
            $('#2fa-setup-section').show();
            $('[data-action="setup-2fa"]').show();
            $('#2fa-qr-section').show();
            $('#2fa-enabled-section').hide();
            $('#2fa-saved-codes').hide();
            loadExisting2FASecret();
        } else {
            $('#2fa-status-text').html(status.mandatory
                ? '<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Two-factor authentication is required. Set it up before using the rest of the system.</span>'
                : '<span class="text-muted"><i class="fa fa-times-circle"></i> Two-Factor Authentication is <strong>disabled</strong></span>');
            $('#2fa-setup-section').show();
            $('[data-action="setup-2fa"]').show();
            $('#2fa-qr-section').hide();
            $('#2fa-enabled-section').hide();
            $('#2fa-saved-codes').hide();
        }
        arm2FASetupPrompt(!!status.mandatory && !status.enabled);
    }

    var twoFaSetupRequired = false;
    var twoFaPromptOpen = false;

    function arm2FASetupPrompt(required) {
        var wasRequired = twoFaSetupRequired;
        twoFaSetupRequired = required;
        var section = document.getElementById('2fa-section');
        if (section) section.classList.toggle('tfa-required', required);
        if (required && !wasRequired) {
            promptComplete2FA();
        }
    }

    function promptComplete2FA() {
        if (!twoFaSetupRequired || twoFaPromptOpen || (window.Swal && Swal.isVisible())) {
            return;
        }
        twoFaPromptOpen = true;
        var section = document.getElementById('2fa-section');
        if (section) section.scrollIntoView({ behavior: 'smooth', block: 'center' });
        Swal.fire({
            icon: 'info',
            title: 'Finish two-factor setup',
            text: 'Two-factor authentication is required before you can use the rest of the system. Use the Two-Factor Authentication section on this page.',
            confirmButtonColor: '#3D8881',
            confirmButtonText: 'Continue setup'
        }).then(function () {
            twoFaPromptOpen = false;
            var code = document.getElementById('2fa-verify-code');
            var setupBtn = document.querySelector('[data-action="setup-2fa"]');
            if (code && code.offsetParent) code.focus();
            else if (setupBtn) setupBtn.focus();
        });
    }

    document.addEventListener('click', function (event) {
        if (!twoFaSetupRequired) return;
        if (event.target.closest('#2fa-section') || event.target.closest('.swal2-container')) return;
        event.preventDefault();
        event.stopPropagation();
        promptComplete2FA();
    }, true);

    function renderSavedBackupCodes(codes) {
        var box = document.getElementById('2fa-saved-codes');
        var list = document.getElementById('2fa-saved-codes-list');
        var note = document.getElementById('2fa-saved-codes-note');
        if (!box || !list) return;
        box.style.display = 'block';
        box.dataset.codes = (codes || []).join('\n');
        if (!codes || !codes.length) {
            list.innerHTML = '';
            if (note) note.textContent = 'These codes were created before they could be saved here. Regenerate to get a set you can copy. The old codes stop working when you do.';
            return;
        }
        list.innerHTML = codes.map(function (code) {
            return '<span class="tfa-code">' + code + '</span>';
        }).join('');
        if (note) note.textContent = 'Each code works once. Copy them and keep them somewhere safe.';
    }

    function showSetupBackupCodes(codes) {
        var list = document.getElementById('2fa-backup-codes');
        if (!list) return;
        codes = codes || [];
        list.dataset.codes = codes.join('\n');
        if (!codes.length) {
            list.innerHTML = '<p class="tfa-note">No backup codes yet. Choose Setup 2FA again.</p>';
            return;
        }
        list.innerHTML = codes.map(function (code) {
            return '<span class="tfa-code">' + code + '</span>';
        }).join('');
    }

    window.copySetupBackupCodes = function () {
        var list = document.getElementById('2fa-backup-codes');
        copyText(list ? (list.dataset.codes || '') : '', 'Backup codes copied');
    };

    window.copyBackupCodes = function () {
        var box = document.getElementById('2fa-saved-codes');
        copyText(box ? (box.dataset.codes || '') : '', 'Backup codes copied');
    };

    window.copy2FASecret = function () {
        var secret = document.getElementById('2fa-secret-key');
        copyText(secret ? secret.value : '', 'Secret copied');
    };

    window.regenerateBackupCodes = function () {
        Swal.fire({
            icon: 'warning',
            title: 'Regenerate backup codes?',
            text: 'The current codes will stop working. The new codes are saved on this page so you can copy them.',
            showCancelButton: true,
            confirmButtonColor: '#3D8881',
            confirmButtonText: 'Regenerate'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: 'scripts/setup_2fa',
                data: { action: 'regenerate_backup', csrf_token: csrfToken() },
                success: function (dataResult) {
                    var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                    if (data.statusCode === 'success') {
                        renderSavedBackupCodes(data.backup_codes || []);
                        var count = (data.backup_codes || []).length;
                        $('#2fa-status-text').html('<span class="text-success"><i class="fa fa-check-circle"></i> Two-Factor Authentication is <strong>enabled</strong></span><br><small class="text-muted">You have ' + count + ' backup code(s) remaining.</small>');
                        Swal.fire({ icon: 'success', title: 'Saved', text: data.message || 'New backup codes saved.', confirmButtonColor: '#3D8881' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Could not regenerate', text: data.message || 'Try again.', confirmButtonColor: '#FF0000' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Could not regenerate', text: 'Please try again.', confirmButtonColor: '#FF0000' });
                }
            });
        });
    };

    function loadExisting2FASecret() {
        $.ajax({
            type: 'POST',
            url: 'scripts/setup_2fa',
            data: { action: 'get_secret', csrf_token: csrfToken() },
            success: function (dataResult) {
                var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                if (data.statusCode !== 'success') return;
                if (data.qr_url) $('#2fa-qr-code').attr('src', data.qr_url);
                if (data.secret) {
                    var secretKey = document.getElementById('2fa-secret-key');
                    if (secretKey) secretKey.value = data.secret;
                }
                showSetupBackupCodes(data.backup_codes || []);
            }
        });
    }

    window.setup2FA = function () {
        Swal.fire({
            icon: 'info',
            title: 'Generating 2FA Secret...',
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });
        $.ajax({
            type: 'POST',
            url: 'scripts/setup_2fa',
            data: { action: 'generate', csrf_token: csrfToken() },
            success: function (dataResult) {
                Swal.close();
                var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                if (data.statusCode === 'success') {
                    if (data.qr_url) $('#2fa-qr-code').attr('src', data.qr_url);
                    if (data.secret) {
                        var secretKey = document.getElementById('2fa-secret-key');
                        if (secretKey) secretKey.value = data.secret;
                    }
                    showSetupBackupCodes(data.backup_codes || []);
                    $('[data-action="setup-2fa"]').hide();
                    $('#2fa-qr-section').show();
                    setTimeout(function () {
                        var input = document.getElementById('2fa-verify-code');
                        if (input) input.focus();
                    }, 100);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to generate 2FA secret', confirmButtonColor: '#FF0000' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Connection Error', text: 'Unable to connect to the server.', confirmButtonColor: '#FF0000' });
            }
        });
    };

    window.verify2FASetup = function () {
        var code = ($('#2fa-verify-code').val() || '').trim();
        if (!code || code.length !== 6 || !/^\d+$/.test(code)) {
            Swal.fire({ icon: 'error', title: 'Invalid Code', text: 'Please enter a valid 6-digit verification code', confirmButtonColor: '#FF0000' });
            return;
        }
        $.ajax({
            type: 'POST',
            url: 'scripts/setup_2fa',
            data: { action: 'verify', code: code, csrf_token: csrfToken() },
            success: function (dataResult) {
                var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                if (data.statusCode === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '2FA Enabled!',
                        text: 'Two-Factor Authentication has been enabled successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        $('#2fa-verify-code').val('');
                        get2FAStatus();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Verification Failed', text: data.message || 'Invalid verification code.', confirmButtonColor: '#FF0000' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to enable 2FA', confirmButtonColor: '#FF0000' });
            }
        });
    };

    window.disable2FA = function () {
        var password = $('#2fa-disable-password').val();
        if (!password) {
            Swal.fire({ icon: 'error', title: 'Password Required', text: 'Please enter your password to disable 2FA', confirmButtonColor: '#FF0000' });
            return;
        }
        Swal.fire({
            title: 'Disable 2FA?',
            text: 'Are you sure you want to disable two-factor authentication?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, disable it'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: 'scripts/setup_2fa',
                data: { action: 'disable', password: password, csrf_token: csrfToken() },
                success: function (dataResult) {
                    var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                    if (data.statusCode === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '2FA Disabled',
                            text: 'Two-factor authentication has been disabled for your account.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            $('#2fa-disable-password').val('');
                            get2FAStatus();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to disable 2FA.', confirmButtonColor: '#FF0000' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to disable 2FA', confirmButtonColor: '#FF0000' });
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        get2FAStatus();
    });
}());
