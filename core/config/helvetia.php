<?php

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u889201362_helvetia_admin');
// define('DB_PASSWORD', 'DZ]CVm66s/');
// define('DB_NAME', 'u889201362_helvetia');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect('localhost', 'u889201362_helvetia_admin', 'DZ]CVm66s/', 'u889201362_helvetia');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}


?>