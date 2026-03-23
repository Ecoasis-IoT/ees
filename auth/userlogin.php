<?php

    require "config.php";

	//get $user and $pass
	$user=trim($_POST['username']);
	$pass=trim($_POST['pass']);
	
	//sql query to fetch information of registerd user and finds user match.
	
	$query = "SELECT * FROM tbl_user WHERE username LIKE '$user'";
	
	$result = mysqli_query($admin_link, $query);
	
	$cred = mysqli_fetch_assoc($result);
	
	$rows = mysqli_num_rows($result);
	
// 	$myfile = fopen("log.txt", "w") or die("Unable to open file!");
//     fwrite($myfile, $user . "\n");
//     fwrite($myfile, $pass . "\n");
// 	fwrite($myfile, $rows . "\n");
//     fclose($myfile);
	

	if($rows == 1){
	    
	    if(password_verify($pass, $cred['password'])){
            
	        session_start();
            $_SESSION["name"] = $cred['firstname'];
            $_SESSION["last_name"] = $cred['lastname'];
			$_SESSION["id"] = $cred['id'];
            $_SESSION["created"] = time();

			echo json_encode(array("statusCode"=>"auth"));
	    }
	    else{
	        echo json_encode(array("statusCode"=>"Err"));
	    }
	
	}
	else{
	    echo json_encode(array("statusCode"=>"Err"));
	}
	
	mysqli_close($admin_link); // Closing connection
	


?>