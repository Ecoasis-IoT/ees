<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

// Remove the session cookie from the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Logging Out | EES</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2;url=login.php">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <style>
        * { box-sizing: border-box; }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #0F172A;
            margin: 0;
            font-family: 'Nunito Sans', sans-serif;
            gap: 0;
        }
        .logout-logo {
            width: 52px;
            height: auto;
            margin-bottom: 24px;
            opacity: 0.85;
            filter: brightness(0) invert(1);
        }
        .logout-text {
            font-size: 15px;
            color: rgba(255,255,255,.5);
            margin-bottom: 28px;
            font-weight: 500;
        }
        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(112,173,71,.2);
            border-top-color: #70AD47;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <img src="assets/images/logo_icon.png" class="logout-logo" alt="Ecoasis">
    <p class="logout-text">Signing you out…</p>
    <div class="spinner"></div>
</body>
</html>
