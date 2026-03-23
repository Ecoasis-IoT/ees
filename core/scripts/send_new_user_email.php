<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$user = $_POST["email"];
$old_user = $_POST["old_user"];
// $user = "y.baboolull@ecoasis.mu";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


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

$client = $user;


$mail->ClearAddresses();
$mail->addAddress($client);
$mail->Subject = 'Register - Ecoasis PV Monitoring Platform';
$mail->Body = 
'<b><h2>Welcome to Ecoasis PV Monitoring Platform</h2></b>

<p>'. $old_user . ' wants to add you as a new user</p>

<p>If you received this message by mistake, please ignore this email.</p>

<p>If you are the concerned person, please go to the link below to register: <a href= "https://ees.ecoasisenergy.com/register">Click Here to Register</a></p>';

$mail->IsHTML(true); 

if($mail->send()){
    echo json_encode(array("statusCode"=>"ok"));
}
else{
    echo json_encode(array("statusCode"=>"Err"));
}

?>