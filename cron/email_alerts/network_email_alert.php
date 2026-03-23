<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = SMTP::DEBUG_OFF;
$mail->Host = 'smtp.hostinger.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'no-reply@ecoasisenergy.com';
//Password to use for SMTP authentication
$mail->Password = '5N7uG@GhxHCVzW6';
//Set who the message is to be sent from
$mail->setFrom('no-reply@ecoasisenergy.com', 'Ecoasis');
//Set an alternative reply-to address
$mail->addReplyTo('no-reply@ecoasisenergy.com', 'Ecoasis');
$mail->SMTPOptions = array( 
'ssl' => array( 
'verify_peer' => false, 
'verify_peer_name' => false, 
'allow_self_signed' => true)
);


$clients = ["y.baboolull@ecoasis.mu", "d.bhagooli@ecoasis.mu"];

if($status == "1"){
    
    foreach($clients as $client){
        $mail->ClearAddresses();
        $mail->addAddress($client);
        $mail->Subject = '[PHOENIX MALL]: IoT GATEWAY ONLINE';
        $mail->Body = '<h2>Last Seen at ' . $str_timenow . '</h2>';
        $mail->IsHTML(true); 
        $mail->send();
    
    }
    
}
else if($status == "0"){
    
    foreach($clients as $client){
        $mail->ClearAddresses();
        $mail->addAddress($client);
        $mail->Subject = '[PHOENIX MALL]: IoT GATEWAY OFFLINE';
        $mail->Body = '<h2>Last Seen at ' . $last_seen . '</h2>';
        $mail->IsHTML(true); 
        $mail->send();
    
    }
    
}



?>