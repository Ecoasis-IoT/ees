/**
 * Forgot password page — request reset email
 */
(function () {
    'use strict';

    function getCSRFToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    window.auth = function () {
        var email = document.getElementById('email').value.trim();
        var btn   = document.querySelector('.btn-primary');

        if (!email) {
            EES.alert('Please enter your email address.', 'warning');
            return;
        }

        EES.btnLoad(btn, 'Sending…');

        $.ajax({
            type:     'POST',
            url:      'scripts/password_reset_email',
            dataType: 'json',
            data: { email: email, csrf_token: getCSRFToken() },
            success: function (data) {
                EES.btnReset(btn);
                if (data.statusCode === 'ok') {
                    EES.alert('If this email exists in our system, a reset link has been sent.', 'success');
                } else {
                    EES.alert('An error occurred. Please contact the administrator.', 'error');
                }
            },
            error: function () {
                EES.btnReset(btn);
                EES.alert('A network error occurred. Please try again.', 'error');
            }
        });
    };
}());
