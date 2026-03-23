<?php

require('config.php');

$pass1 = $_POST['password1'];
$pass2 = $_POST['password2'];
$email = $_POST['email'];

if(strcmp($pass1, $pass2) != 0){
    echo json_encode(array("statusCode"=>"Err1"));
}
else{
    
    $query = "SELECT * FROM `tbl_user` WHERE `email` = '$email';";
    $result = mysqli_query($admin_link, $query);
    $num_rows = mysqli_num_rows($result);
    
    if($num_rows == 0){
        echo json_encode(array("statusCode"=>"Err2"));
    }
    
    else if($num_rows == 1){
        
        $hashed_pass = password_hash($pass1, PASSWORD_DEFAULT);
        
        $query = "UPDATE `tbl_user` SET `password`='$hashed_pass' WHERE `email` = '$email';";
        if(mysqli_query($admin_link, $query)){
            echo json_encode(array("statusCode"=>"ok"));
        }
        else{
            echo json_encode(array("statusCode"=>"Err2"));
        }
        
    }
}


?>