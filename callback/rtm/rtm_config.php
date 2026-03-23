<?php

date_default_timezone_set('Indian/Mauritius');
$link = mysqli_connect('localhost', 'u889201362_rt_admin', '+cp]oVRqU0r', 'u889201362_r_terre_mall');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}

?>