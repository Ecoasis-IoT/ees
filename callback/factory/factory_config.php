<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u889201362_factory_admin');
define('DB_PASSWORD', '#T0nvAG5NLe4');
define('DB_NAME', 'u889201362_factory');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}



?>