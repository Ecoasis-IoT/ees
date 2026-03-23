<?php

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u889201362_pc_admin');
// define('DB_PASSWORD', 'eZibCy5x96#S');
// define('DB_NAME', 'u889201362_p_catering');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect('localhost', 'u889201362_pc_admin', 'eZibCy5x96#S', 'u889201362_p_catering');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}


?>