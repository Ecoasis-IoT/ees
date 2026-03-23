<?php

$size = filesize("../../cron/network_status.txt");

$myfile = fopen("../../cron/network_status.txt", "r") or die(json_encode(array("status"=>"noFile")));


if($size > 0){
    $state = fread($myfile, $size);
}
else{
    //No data in text file
    echo json_encode(array("status"=>"emptyFile"));
}

if(trim($state) == "ON"){
    echo json_encode(array("status"=>"connected"));
}
else if(trim($state) == "OFF"){
    echo json_encode(array("status"=>"disconnected"));
}

?>