<?php

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u889201362_hl_admin');
// define('DB_PASSWORD', 'J&U=OIO]2oZ');
// define('DB_NAME', 'u889201362_home_leisure');
// date_default_timezone_set('Indian/Mauritius');

date_default_timezone_set('Indian/Mauritius');
$link = mysqli_connect('localhost', 'u889201362_hl_admin', 'J&U=OIO]2oZ', 'u889201362_home_leisure');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}


?>