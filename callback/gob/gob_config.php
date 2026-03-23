<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u889201362_gob_admin');
define('DB_PASSWORD', 'jBx$O62#');
define('DB_NAME', 'u889201362_gob');
date_default_timezone_set('Indian/Mauritius');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}



?>