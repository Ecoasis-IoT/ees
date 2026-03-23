<?php

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u889201362_phoenix');
// define('DB_PASSWORD', 'YCVOy[yC4');
// define('DB_NAME', 'u889201362_phoenix_mall');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect('localhost', 'u889201362_phoenix', 'YCVOy[yC4', 'u889201362_phoenix_mall');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}

?>