/**
 * Login page — password first, then a 2FA code dialog when needed.
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
        }
    }

    function hideError(elId) {
        var el = document.getElementById(elId);
        if (el) el.style.display = 'none';
    }

    function setBtnLoading(btn, loading, idleText) {
        if (!btn) return;
        btn.disabled = loading;
        btn.value = loading ? 'Please wait…' : idleText;
    }

    function send2FAEmailCode() {
        var status = document.getElementById('swal2-2fa-email-status');
        var btn = document.getElementById('swal2-2fa-email');
        if (btn) btn.disabled = true;
        if (status) status.textContent = 'Sending code...';

        $.ajax({
            type: 'POST',
            url: 'scripts/send_2fa_email',
            data: { csrf_token: getCSRFToken() },
            success: function (dataResult) {
                var data = typeof dataResult === 'string' ? JSON.parse(dataResult) : dataResult;
                if (status) status.textContent = data.message || '';
                if (data.statusCode !== 'success' && btn) btn.disabled = false;
                if (data.statusCode === 'success' && btn) {
                    setTimeout(function () { btn.disabled = false; }, 60000);
                }
            },
            error: function () {
                if (status) status.textContent = 'Could not send the email. Try again.';
                if (btn) btn.disabled = false;
            }
        });
    }

    function show2FAModal() {
        if (typeof Swal === 'undefined') {
            showError('login-error', 'Enter your verification code to continue.');
            return;
        }

        Swal.fire({
            title: '<i class="fa fa-shield ees-swal-icon"></i><br><strong>Two-Factor Authentication</strong>',
            width: '24rem',
            html:
                '<div style="text-align:center;">' +
                '<p style="margin:0 0 14px;font-size:0.92rem;line-height:1.45;">' +
                'Enter the 6-digit code from your authenticator app, an emailed code, or an 8-digit backup code.' +
                '</p>' +
                '<input type="text" id="swal2-2fa-code" class="swal2-input" placeholder="000000" maxlength="8" autocomplete="one-time-code">' +
                '<button type="button" id="swal2-2fa-email">Email me a code</button>' +
                '<p id="swal2-2fa-email-status"></p>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'Verify',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#70AD47',
            cancelButtonColor: '#E2E8F0',
            customClass: { popup: 'ees-swal' },
            allowOutsideClick: false,
            focusConfirm: false,
            didOpen: function () {
                var input = document.getElementById('swal2-2fa-code');
                if (input) {
                    setTimeout(function () { input.focus(); }, 200);
                    input.addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            var confirmBtn = document.querySelector('.swal2-confirm');
                            if (confirmBtn) confirmBtn.click();
                        }
                    });
                }
                var emailBtn = document.getElementById('swal2-2fa-email');
                if (emailBtn) {
                    emailBtn.addEventListener('click', function (event) {
                        event.preventDefault();
                        send2FAEmailCode();
                    });
                }
            },
            preConfirm: function () {
                var code = (document.getElementById('swal2-2fa-code').value || '').trim();
                if (!code) {
                    Swal.showValidationMessage('Please enter your verification code');
                    return false;
                }
                if (code.length !== 6 && code.length !== 8) {
                    Swal.showValidationMessage('Enter a 6-digit code or an 8-digit backup code');
                    return false;
                }
                return code;
            }
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                verify2FA(result.value);
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                $.post('scripts/cancel_2fa', { csrf_token: getCSRFToken() });
                var passField = document.getElementById('signin-password');
                if (passField) {
                    passField.value = '';
                    passField.focus();
                }
            }
        });
    }

    function verify2FA(code) {
        Swal.fire({
            title: 'Verifying...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: { popup: 'ees-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: 'scripts/verify_2fa',
            dataType: 'json',
            data: {
                code: code,
                csrf_token: getCSRFToken()
            },
            success: function (data) {
                if (data.statusCode === 'auth') {
                    window.location.replace(data.link || 'dashboard');
                } else if (data.statusCode === 'timeout') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session expired',
                        text: data.message || 'Verification timed out. Please sign in again.',
                        confirmButtonColor: '#70AD47',
                        customClass: { popup: 'ees-swal' }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid code',
                        text: data.message || 'Invalid verification code. Please try again.',
                        confirmButtonColor: '#70AD47',
                        customClass: { popup: 'ees-swal' }
                    }).then(function () { show2FAModal(); });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Unable to connect to the server. Please try again.',
                    confirmButtonColor: '#EF4444',
                    customClass: { popup: 'ees-swal' }
                }).then(function () { show2FAModal(); });
            }
        });
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
            type: 'POST',
            url: 'scripts/userlogin',
            dataType: 'json',
            data: {
                username: username,
                pass: password,
                csrf_token: getCSRFToken()
            },
            success: function (data) {
                if (data.statusCode === 'auth') {
                    window.location.replace(data.link || (data.setup_2fa ? 'profile' : 'dashboard'));
                } else if (data.statusCode === '2fa_required') {
                    setBtnLoading(btn, false, 'Sign In');
                    show2FAModal();
                } else {
                    setBtnLoading(btn, false, 'Sign In');
                    if (data.statusCode === 'locked') {
                        showError('login-error', 'Account locked due to too many failed attempts. Please try again later.');
                    } else if (data.statusCode === 'blocked') {
                        showError('login-error', data.message || 'This account has been disabled. Please contact the administrator.');
                    } else if (data.statusCode === 'rate_limit') {
                        showError('login-error', 'Too many login attempts. Please wait a moment and try again.');
                    } else {
                        showError('login-error', data.message || 'Incorrect username or password.');
                    }
                }
            },
            error: function () {
                setBtnLoading(btn, false, 'Sign In');
                showError('login-error', 'A network error occurred. Please try again.');
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (document.body.getAttribute('data-2fa-pending') === '1') {
            show2FAModal();
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

        var toggleBtn = document.getElementById('toggle-password');
        var toggleIcon = document.getElementById('toggle-password-icon');
        var passField = document.getElementById('signin-password');
        if (toggleBtn && passField) {
            toggleBtn.addEventListener('click', function () {
                var isHidden = passField.type === 'password';
                passField.type = isHidden ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye', !isHidden);
                toggleIcon.classList.toggle('fa-eye-slash', isHidden);
                passField.focus();
            });
        }
    });
}());
