/**
 * Register page — AJAX account creation
 */
(function () {
    'use strict';

    function getCSRFToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    window.auth = function () {
        var username = document.getElementById('uname').value.trim();
        var fname    = document.getElementById('fname').value.trim();
        var lname    = document.getElementById('lname').value.trim();
        var email    = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var btn      = document.querySelector('input[type="button"]');

        if (!username || !fname || !lname || !email || !password) {
            EES.alert('Please fill in all fields.', 'warning');
            return;
        }

        EES.btnLoad(btn, 'Registering…');

        $.ajax({
            type:     'POST',
            url:      'scripts/userregister.php',
            dataType: 'json',
            data: {
                username:   username,
                fname:      fname,
                lname:      lname,
                email:      email,
                password:   password,
                csrf_token: getCSRFToken()
            },
            success: function (data) {
                if (data.statusCode === 'auth') {
                    window.location.replace('login.php');
                } else if (data.statusCode === 'duplicate') {
                    EES.btnReset(btn);
                    EES.alert('Username or email already exists. Please choose a different one.', 'warning');
                } else {
                    EES.btnReset(btn);
                    EES.alert(data.message || 'Error registering account. Please try again.', 'error');
                }
            },
            error: function () {
                EES.btnReset(btn);
                EES.alert('A network error occurred. Please try again.', 'error');
            }
        });
    };
}());
