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

        if (!email) {
            alert('Please enter your email address.');
            return;
        }

        $.ajax({
            type:     'POST',
            url:      'scripts/password_reset_email.php',
            dataType: 'json',
            data: { email: email, csrf_token: getCSRFToken() },
            success: function (data) {
                if (data.statusCode === 'ok') {
                    alert('If this email exists in our system, a reset link has been sent.');
                } else {
                    alert('An error occurred. Please contact the administrator.');
                }
            },
            error: function () {
                alert('A network error occurred. Please try again.');
            }
        });
    };
}());
