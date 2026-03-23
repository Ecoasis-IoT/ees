<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();
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
        body { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:100vh; background:#f0f3f4; }
        .centered-image { max-width:300px; margin-top:20px; }
        .text-above { font-size:1.4rem; color:#555; margin-bottom:10px; }
    </style>
</head>
<body>
    <p class="text-above">You are being logged out!</p>
    <img src="assets/images/logout.gif" alt="Logging out..." class="centered-image">
</body>
</html>
