/**
 * Login page — AJAX authentication
 * Sends credentials + CSRF token to core/scripts/userlogin.php
 */
(function () {
    'use strict';

    function getCSRFToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showError(message) {
        var el = document.getElementById('login-error');
        if (el) {
            el.textContent = message;
            el.style.display = 'block';
        } else {
            EES.alert(message, 'error');
        }
    }

    function hideError() {
        var el = document.getElementById('login-error');
        if (el) el.style.display = 'none';
    }

    window.auth = function () {
        hideError();

        var username = document.getElementById('signin-user').value.trim();
        var password = document.getElementById('signin-password').value;

        if (!username || !password) {
            showError('Please enter your username and password.');
            return;
        }

        var btn = document.querySelector('input[type="submit"]');
        if (btn) { btn.disabled = true; btn.value = 'Signing in…'; }

        $.ajax({
            type:     'POST',
            url:      'scripts/userlogin.php',
            dataType: 'json',
            data: {
                username:   username,
                pass:       password,
                csrf_token: getCSRFToken()
            },
            success: function (data) {
                if (data.statusCode === 'auth') {
                    window.location.replace('dashboard.php');
                } else {
                    if (btn) { btn.disabled = false; btn.value = 'Sign In'; }
                    if (data.statusCode === 'locked') {
                        showError('Account locked due to too many failed attempts. Please try again later.');
                    } else if (data.statusCode === 'blocked') {
                        showError('This account has been disabled. Please contact the administrator.');
                    } else if (data.statusCode === 'rate_limit') {
                        showError('Too many login attempts. Please wait a moment and try again.');
                    } else {
                        showError('Incorrect username or password.');
                    }
                }
            },
            error: function () {
                if (btn) { btn.disabled = false; btn.value = 'Sign In'; }
                showError('A network error occurred. Please try again.');
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function () {

        // Enter key submits from either field
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

        // Password show / hide toggle
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
