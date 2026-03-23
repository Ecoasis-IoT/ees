<?php

session_start();

if(!isset($_SESSION['id']) or (time() - $_SESSION['created']) >= 3600){
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header("Location: ../../login.php");
    die();
}


?>