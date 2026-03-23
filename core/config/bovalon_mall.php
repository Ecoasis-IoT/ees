<?php

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u889201362_bv_admin');
// define('DB_PASSWORD', 'Qs;3c7B3?');
// define('DB_NAME', 'u889201362_bovalon_mall');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect("localhost", "u889201362_bv_admin", "Qs;3c7B3?", "u889201362_bovalon_mall");
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}


?>