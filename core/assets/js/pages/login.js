/**
 * Login page — AJAX authentication with optional 2FA step
 */
(function () {
    'use strict';

    function getCSRFToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showError(elId, message) {
        var el = document.getElementById(elId);
        if (el) {
            el.textContent = message;
            el.style.display = 'block';
        } else {
            EES.alert(message, 'error');
        }
    }

    function hideError(elId) {
        var el = document.getElementById(elId);
        if (el) el.style.display = 'none';
    }

    function show2FAStep() {
        document.getElementById('login-step-credentials').style.display = 'none';
        document.getElementById('login-step-2fa').style.display = 'block';
        document.getElementById('login-title').textContent = 'Two-factor authentication';
        document.getElementById('login-subtitle').textContent = 'Enter your verification code to continue';
        hideError('login-error');
        hideError('login-2fa-error');
        var codeField = document.getElementById('signin-2fa-code');
        if (codeField) codeField.focus();
    }

    function hide2FAStep() {
        document.getElementById('login-step-2fa').style.display = 'none';
        document.getElementById('login-step-credentials').style.display = 'block';
        document.getElementById('login-title').textContent = 'Welcome back';
        document.getElementById('login-subtitle').textContent = 'Sign in to your account to continue';
        hideError('login-2fa-error');
        var codeField = document.getElementById('signin-2fa-code');
        if (codeField) codeField.value = '';
    }

    function setBtnLoading(btn, loading, idleText) {
        if (!btn) return;
        btn.disabled = loading;
        btn.value = loading ? 'Please wait…' : idleText;
    }

    window.auth = function () {
        hideError('login-error');

        var username = document.getElementById('signin-user').value.trim();
        var password = document.getElementById('signin-password').value;

        if (!username || !password) {
            showError('login-error', 'Please enter your username and password.');
            return;
        }

        var btn = document.getElementById('login-submit-btn');
        setBtnLoading(btn, true, 'Sign In');

        $.ajax({
            type:     'POST',
            url:      'scripts/userlogin',
            dataType: 'json',
            data: {
                username:   username,
                pass:       password,
                csrf_token: getCSRFToken()
            },
            success: function (data) {
                if (data.statusCode === 'auth') {
                    window.location.replace('dashboard');
                } else if (data.statusCode === '2fa_required') {
                    setBtnLoading(btn, false, 'Sign In');
                    show2FAStep();
                } else {
                    setBtnLoading(btn, false, 'Sign In');
                    if (data.statusCode === 'locked') {
                        showError('login-error', 'Account locked due to too many failed attempts. Please try again later.');
                    } else if (data.statusCode === 'blocked') {
                        showError('login-error', data.message || 'This account has been disabled. Please contact the administrator.');
                    } else if (data.statusCode === 'rate_limit') {
                        showError('login-error', 'Too many login attempts. Please wait a moment and try again.');
                    } else if (data.message) {
                        showError('login-error', data.message);
                    } else {
                        showError('login-error', 'Incorrect username or password.');
                    }
                }
            },
            error: function () {
                setBtnLoading(btn, false, 'Sign In');
                showError('login-error', 'A network error occurred. Please try again.');
            }
        });
    };

    window.verify2FA = function () {
        hideError('login-2fa-error');

        var code = document.getElementById('signin-2fa-code').value.trim();
        var isBackup = document.getElementById('signin-2fa-backup').checked;

        if (!code) {
            showError('login-2fa-error', 'Please enter your verification code.');
            return;
        }

        var btn = document.getElementById('login-2fa-submit-btn');
        setBtnLoading(btn, true, 'Verify');

        $.ajax({
            type:     'POST',
            url:      'scripts/verify_2fa',
            dataType: 'json',
            data: {
                code:       code,
                is_backup:  isBackup ? 'true' : 'false',
                csrf_token: getCSRFToken()
            },
            success: function (data) {
                if (data.statusCode === 'auth') {
                    window.location.replace('dashboard');
                } else if (data.statusCode === 'timeout') {
                    setBtnLoading(btn, false, 'Verify');
                    hide2FAStep();
                    showError('login-error', data.message || 'Verification timed out. Please sign in again.');
                } else {
                    setBtnLoading(btn, false, 'Verify');
                    showError('login-2fa-error', data.message || 'Invalid verification code. Please try again.');
                    document.getElementById('signin-2fa-code').focus();
                }
            },
            error: function () {
                setBtnLoading(btn, false, 'Verify');
                showError('login-2fa-error', 'A network error occurred. Please try again.');
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function () {

        if (document.body.getAttribute('data-2fa-pending') === '1') {
            show2FAStep();
        }

        ['signin-user', 'signin-password'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        window.auth();
                    }
                });
            }
        });

        var codeField = document.getElementById('signin-2fa-code');
        if (codeField) {
            codeField.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.verify2FA();
                }
            });
            codeField.addEventListener('input', function () {
                var backup = document.getElementById('signin-2fa-backup').checked;
                var maxLen = backup ? 8 : 6;
                var value = this.value.replace(/[^0-9A-Za-z-]/g, '');
                if (value.length > maxLen) {
                    value = value.substring(0, maxLen);
                }
                this.value = value;
            });
        }

        var backupToggle = document.getElementById('signin-2fa-backup');
        if (backupToggle && codeField) {
            backupToggle.addEventListener('change', function () {
                codeField.placeholder = this.checked ? 'Backup code' : '000000';
                codeField.maxLength = this.checked ? 8 : 6;
                codeField.value = '';
                codeField.focus();
            });
        }

        var backLink = document.getElementById('login-2fa-back-link');
        if (backLink) {
            backLink.addEventListener('click', function (e) {
                e.preventDefault();
                $.post('scripts/cancel_2fa', { csrf_token: getCSRFToken() }, function () {
                    hide2FAStep();
                    document.getElementById('signin-password').value = '';
                });
            });
        }

        var toggleBtn  = document.getElementById('toggle-password');
        var toggleIcon = document.getElementById('toggle-password-icon');
        var passField  = document.getElementById('signin-password');

        if (toggleBtn && passField) {
            toggleBtn.addEventListener('click', function () {
                var isHidden = passField.type === 'password';
                passField.type = isHidden ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye',       !isHidden);
                toggleIcon.classList.toggle('fa-eye-slash',  isHidden);
                passField.focus();
            });
        }
    });
}());
