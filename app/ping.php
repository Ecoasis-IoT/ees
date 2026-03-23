<?php

$postdata = json_decode(file_get_contents('php://input'), true);
$token = $postdata['token'];


// file_put_contents('log1.txt', $token);

if(strcmp($token, "ecoasis2024") == 0){
    
    echo json_encode(array("status" => "auth"));
    
}
else{
    
    echo json_encode(array("status" => "reject"));
    
}


?>