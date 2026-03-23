<?php

    require "config.php";

    $username=trim($_POST['username']);
    $fname= trim($_POST['fname']);
    $lname= trim($_POST['lname']);
    $email= trim($_POST['email']);
    $pass= trim($_POST['password']);

    // $myfile = fopen("log1.txt", "w") or die("Unable to open file!");
    // fwrite($myfile, $username . "\n");
    // fwrite($myfile, $fname . "\n");
    // fwrite($myfile, $lname . "\n");
    // fwrite($myfile, $email . "\n");
    // fwrite($myfile, $pass . "\n");
    // fclose($myfile);

    $query = "Select * from tbl_user where username = '". $username . "'";
    $result = mysqli_query($admin_link, $query);
    $num_username = mysqli_num_rows($result);
    
    $query2 = "Select * from tbl_user where email = '". $email . "'";
    $result2 = mysqli_query($admin_link, $query2);
    $num_email = mysqli_num_rows($result2);

    If($num_username <> 0 or $num_email <> 0){
    
        echo json_encode(array("statusCode"=> "Err"));
    }
    else{
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);   
        
        date_default_timezone_set('Indian/Mauritius');
        $timenow = date('y-m-d h:i:s');
        $create_query = "INSERT INTO tbl_user(firstname, lastname, username, password, email, date_added) VALUES('$fname', '$lname', '$username', '$hashed_pass', '$email', '$timenow')";
        
        // $myfile = fopen("log1.txt", "w") or die("Unable to open file!");
        // fwrite($myfile, $create_query);
        // fclose($myfile); 

        if(mysqli_query($admin_link, $create_query)){

            echo json_encode(array("statusCode"=>"auth"));
            
        }
        else{
            echo json_encode(array("statusCode"=> "Err"));
        }
        
    }
    
    mysqli_close($admin_link);

?>