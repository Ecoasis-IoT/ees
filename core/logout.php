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
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:100vh; background:#f0f3f4; margin:0; font-family:sans-serif; }
        .text-above { font-size:1.4rem; color:#555; margin-bottom:24px; }
        .spinner { width:56px; height:56px; border:5px solid #d0e8c8; border-top-color:#70AD47; border-radius:50%; animation:spin .9s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
    </style>
</head>
<body>
    <p class="text-above">You are being logged out…</p>
    <div class="spinner"></div>
</body>
</html>
