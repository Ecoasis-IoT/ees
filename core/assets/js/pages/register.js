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

        if (!username || !fname || !lname || !email || !password) {
            alert('Please fill in all fields.');
            return;
        }

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
                    window.location.replace('login.php'); // within core/
                } else if (data.statusCode === 'duplicate') {
                    alert('Username or email already exists. Please choose a different one.');
                } else {
                    alert(data.message || 'Error registering account. Please try again.');
                }
            },
            error: function () {
                alert('A network error occurred. Please try again.');
            }
        });
    };
}());
