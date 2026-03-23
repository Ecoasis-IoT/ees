<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u889201362_pod_admin');
define('DB_PASSWORD', '5?hFEj@fO');
define('DB_NAME', 'u889201362_the_pod');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}



?>