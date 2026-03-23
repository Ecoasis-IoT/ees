<?php

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u889201362_rt_admin');
// define('DB_PASSWORD', '+cp]oVRqU0r');
// define('DB_NAME', 'u889201362_r_terre_mall');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect('localhost', 'u889201362_rt_admin', '+cp]oVRqU0r', 'u889201362_r_terre_mall');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}


?>