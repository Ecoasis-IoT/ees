<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u889201362_ees_admin');
define('DB_PASSWORD', 'C3@YsU/d');
define('DB_NAME', 'u889201362_ees_pv');
date_default_timezone_set('Indian/Mauritius');

$admin_link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($admin_link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}

?>