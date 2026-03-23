<?php

date_default_timezone_set('Indian/Mauritius');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/gateways/24e124fffef8b910');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


$headers = array();
$headers[] = 'Accept: application/json';
$headers[] = 'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);

$res = json_decode($result, true);

// print_r($res["lastSeenAt"]);
$last_seen = $res["lastSeenAt"];


$last_seen = date( "Y-m-d H:i:s", strtotime($last_seen));
$timenow = strtotime(date('Y-m-d H:i:s'));
$last_timestamp = strtotime($last_seen);

$str_timenow = date( "Y-m-d H:i:s", $timenow);


// echo $last_seen;
$size = filesize(__DIR__ . "/network_status.txt");

$myfile = fopen(__DIR__ . "/network_status.txt", "r");

// or die("Unable to open file!");

// echo $size;

if($size > 0){

    $last_state = fread($myfile, $size);
    // print_r($last_state);
    
    fclose($myfile);
    
    if(trim($last_state) == "ON"){
        $flag = 1;
    }
    else if(trim($last_state) == "OFF"){
        $flag = 0;
    }
    
    // echo $flag . "<br>";
    
    if($timenow - $last_timestamp > 900){
        
        if($flag == 1){
            $status = 0;
            include __DIR__ . 'email_alerts/network_email_alert.php';
            
            $myfile1 = fopen(__DIR__ . "/logs/Network_Log.txt", "a");
                fwrite($myfile1, $last_seen . " | " . "OFFLINE" . "\n");
            fclose($myfile1);
        }
        $flag = 0;
        
        $myfile = fopen(__DIR__ . "/network_status.txt", "w");
            fwrite($myfile, "OFF");
        fclose($myfile);
        
        echo $last_seen . " | OFFLINE";
    }
    else{
        // echo "test1";
        if($flag == 0){
            $status = 1;
            include __DIR__ . 'email_alerts/network_email_alert.php';
            
            // echo "Email sent! <br>";
            
            $myfile1 = fopen(__DIR__ . "/logs/Network_Log.txt", "a");
                fwrite($myfile1, $str_timenow . " | " . "ONLINE" . "\n");
            fclose($myfile1);
        }
        $flag = 1;
        
        $myfile = fopen(__DIR__ . "/network_status.txt", "w");
            fwrite($myfile, "ON");
        fclose($myfile);
        
        echo $last_seen . " | ONLINE";
    }
    
    // echo strtotime($last_seen);

}
else{
    
    fclose($myfile);
    
    if($timenow - $last_timestamp > 900){
        // Gateway is OFF
        
        //Send Email
        $status = 0;
        include __DIR__ . 'email_alerts/network_email_alert.php';
        
        echo "Email sent! <br>";
        
        //Log the time OFF
        $myfile1 = fopen(__DIR__ . "logs/Network_Log.txt", "a");
            fwrite($myfile1, $last_seen . " | " . "OFFLINE" . "\n");
        fclose($myfile1);
        
        //Set Status to OFF
        
        $myfile = fopen(__DIR__ . "/network_status.txt", "w");
            fwrite($myfile, "OFF");
        fclose($myfile);
        
        echo $last_seen . " | OFFLINE";
    }
    else{
        
        // Gateway is ON
        
        //Set Status to ON
        
        $myfile = fopen(__DIR__ . "/network_status.txt", "w");
            fwrite($myfile, "ON");
        fclose($myfile);
    }
    
}


?>