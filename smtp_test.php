<?php

//Author: Adiputra

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

$smtp_server = $_POST["smtp_server"];
$smtp_auth = $_POST["smtp_auth"];
//$smtp_username = $_POST["smtp_username"];
$smtp_password = $_POST["smtp_password"];
$smtp_security = $_POST["smtp_security"];
$smtp_port = $_POST["smtp_port"];
$smtp_email = $_POST["smtp_email"];
$smtp_sendto = $_POST["smtp_sendto"];
$smtp_subject = $_POST["smtp_subject"];
$smtp_message = $_POST["smtp_message"];

//config values
//smtp auth
if ($smtp_auth == 1) {
    $smtp_auth_status = true;
}
else {
    $smtp_auth_status = false;
}

//smtp security
if ($smtp_security == 0) {
    $smtp_security_status = "";
}
else if ($smtp_security == 1) {
    $smtp_security_status = "tls";
}
else if ($smtp_security == 2) {
    $smtp_security_status = "ssl";
}

//set up name appear in email
$smtp_fromname = substr($smtp_email, 0, strpos($smtp_email, '@'));

$mail = new PHPMailer(true);

//Enable SMTP debugging.
$mail->SMTPDebug = 0;                               
//Set PHPMailer to use SMTP.
$mail->isSMTP();            
//Set SMTP host name                          
$mail->Host = $smtp_server;
//Set this to true if SMTP host requires authentication to send email
$mail->SMTPAuth = $smtp_auth_status;                          
//Provide username and password     
$mail->Username = $smtp_email;                
$mail->Password = $smtp_password;                           
//If SMTP requires TLS encryption then set it
$mail->SMTPAutoTLS = false;
$mail->SMTPSecure = $smtp_security_status;                           
//Set TCP port to connect to
$mail->Port = $smtp_port;                                   

$mail->From = $smtp_email;
$mail->FromName = $smtp_fromname;

$mail->addAddress($smtp_sendto);

$mail->isHTML(false);

$mail->Subject = $smtp_subject;
$mail->Body = $smtp_message;
$mail->AltBody = $smtp_message;

try {
    $mail->send();
    echo "Message has been sent successfully!";
} catch (Exception $e) {
    echo "Failed to send message! Check all setup fields; smtp server, port, authentication, etc...<br>";
    echo $mail->ErrorInfo;
}

?>