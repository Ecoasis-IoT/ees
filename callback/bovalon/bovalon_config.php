<?php

date_default_timezone_set('Indian/Mauritius');
$link = mysqli_connect('localhost', 'u889201362_bv_admin', 'Qs;3c7B3?', 'u889201362_bovalon_mall');
 
// Check connection
if($link === false){
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}


?>