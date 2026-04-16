/**
 * Reset password page — submits new password via AJAX
 */
(function () {
    'use strict';

    function getCSRFToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showAlert(type, message) {
        var el = document.getElementById('reset-alert');
        if (!el) return;
        el.className = 'alert alert-' + type;
        el.textContent = message;
        el.style.display = 'block';
    }

    window.submitReset = function () {
        var pass1  = document.getElementById('password').value;
        var pass2  = document.getElementById('password_con').value;
        var token  = document.getElementById('reset-token').value;

        if (!pass1 || !pass2) {
            showAlert('danger', 'Please fill in both password fields.');
            return;
        }
        if (pass1 !== pass2) {
            showAlert('danger', 'Passwords do not match.');
            return;
        }
        if (pass1.length < 8) {
            showAlert('danger', 'Password must be at least 8 characters.');
            return;
        }

        $.ajax({
            type:     'POST',
            url:      'scripts/password_reset',
            dataType: 'json',
            data: {
                token:      token,
                password1:  pass1,
                password2:  pass2,
                csrf_token: getCSRFToken()
            },
            success: function (data) {

                if (data.statusCode === 'ok') {
                    showAlert('success', 'Password reset successfully! Redirecting to login...');
                    setTimeout(function () { window.location.replace('login'); }, 2000);
                } else if (data.statusCode === 'Err1') {
                    showAlert('danger', 'Passwords do not match.');
                } else if (data.statusCode === 'expired') {
                    showAlert('danger', 'Reset link has expired. Please request a new one.');
                    setTimeout(function () { window.location.replace('forgot-password'); }, 3000);
                } else {
                    showAlert('danger', data.message || 'Error resetting password. Please contact the administrator.');
                }
            },
            error: function () {
                showAlert('danger', 'A network error occurred. Please try again.');
            }
        });
    };
}());
