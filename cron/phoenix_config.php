<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u889201362_phoenix');
define('DB_PASSWORD', 'YCVOy[yC4');
define('DB_NAME', 'u889201362_phoenix_mall');
date_default_timezone_set('Indian/Mauritius');

$phoenix_link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($phoenix_link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}

?>