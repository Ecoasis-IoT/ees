
<!DOCTYPE html>
<html lang="en">

<head>
<title>Ecoasis - Positive Energies | ECOASIS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="shortcut icon" type="image/x-icon" href="core/assets/images/logo_icon.png">
<!-- MAIN CSS -->
<link rel="stylesheet" href="assets/css/logout.css">
</head>

<body>
    <p class="text-above">You are being logged out!</p>
    <img src="assets/images/logout.gif" alt="Logged Out!" class="centered-image">
</body> 

</html>

<?php

sleep(2);

// remove all session variables
session_unset();

// destroy the session
session_destroy();

header('Location: login.php');

?>