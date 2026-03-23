<?php

require "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require '../core/PHPMailer/src/Exception.php';
require '../core/PHPMailer/src/PHPMailer.php';
require '../core/PHPMailer/src/SMTP.php';

$email = $_POST['email'];

$query = "SELECT * FROM `tbl_user` WHERE `email` = '$email';";
$result = mysqli_query($admin_link, $query);
$num_rows = mysqli_num_rows($result);

if($num_rows == 0){
    
    //Do not Send Email
    
}
else if($num_rows == 1){

    
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
    
    
    $mail->ClearAddresses();
    $mail->addAddress($email);
    $mail->Subject = 'Reset Password - Ecoasis PV Monitoring Platform';
    $mail->Body = 
    '<b><h2>Ecoasis PV Monitoring Platform - Password Reset</h2></b>
    
    <p>You have requested to reset your platform password</p>
    
    <p>If you received this message by mistake, please ignore this email.</p>
    
    <p>If you are the concerned person, please go to the link below to reset your password: <a href= "https://ees.ecoasisenergy.com/reset-password.php?Q91Sx6YvS17KZdeS7ypKJEHSASwfbF='. $email .'>Click Here to reset</a></p>';
    
    $mail->IsHTML(true); 
    
    if($mail->send()){
        echo json_encode(array("statusCode"=>"ok"));
    }
    else{
        echo json_encode(array("statusCode"=>"Err"));
    }


}

?>