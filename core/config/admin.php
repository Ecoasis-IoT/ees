<?php

define('admin_DB_SERVER', 'localhost');
define('admin_DB_USERNAME', 'u889201362_ees_admin');
define('admin_DB_PASSWORD', 'C3@YsU/d');
define('admin_DB_NAME', 'u889201362_ees_pv');
date_default_timezone_set('Indian/Mauritius');

$admin_link = mysqli_connect(admin_DB_SERVER, admin_DB_USERNAME, admin_DB_PASSWORD, admin_DB_NAME);
 
// Check connection
if($admin_link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}

?>