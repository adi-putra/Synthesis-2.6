<?php
//Author: Adiputra

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
  
include "session.php";

$mediatype_id = $_POST["mediatype_id"];
$mediatype_smtpserver = $_POST["mediatype_smtpserver"];
$mediatype_smtpport = $_POST["mediatype_smtpport"];
$mediatype_smtphelo = $_POST["mediatype_smtphelo"];
$mediatype_smtpemail = $_POST["mediatype_smtpemail"];
$mediatype_smtpauth = $_POST["mediatype_smtpauth"];
$mediatype_smtppassword = $_POST["mediatype_smtppassword"];
$mediatype_status = $_POST["mediatype_status"];
$mediatype_smtp_security = $_POST["mediatype_smtp_security"];
$mediatype_smtp_verify_peer = $_POST["mediatype_smtp_verify_peer"];
$mediatype_smtp_verify_host = $_POST["mediatype_smtp_verify_host"];

$mediatype_smtpusername = $mediatype_smtpemail;

//check status value
if ($mediatype_status == "") {
    $mediatype_status = 1;
}

//check verify peer and host value
if ($mediatype_smtp_security == 1) {
    if ($mediatype_smtp_verify_peer == "") {
        $mediatype_smtp_verify_peer = 0;
    }
    if ($mediatype_smtp_verify_host == "") {
        $mediatype_smtp_verify_host = 0;
    }
}
else {
    $mediatype_smtp_verify_peer = 0;
    $mediatype_smtp_verify_host = 0;
}

//check if smtp not require auth, set user and pass to empty
if ($mediatype_smtpauth == 0) {
    $mediatype_smtpusername = "";
    $mediatype_smtppassword = "";
}

//store all values in param
$params = array(
    "mediatypeid" => $mediatype_id,
    "status" => $mediatype_status,
    "smtp_server" => $mediatype_smtpserver,
    "smtp_helo" => $mediatype_smtphelo,
    "smtp_email" => $mediatype_smtpemail,
    "smtp_port" => $mediatype_smtpport,
    "smtp_authentication" => $mediatype_smtpauth,
    "username" => $mediatype_smtpusername,
    "passwd" => $mediatype_smtppassword,
    "smtp_security" => $mediatype_smtp_security,
    "smtp_verify_peer" => $mediatype_smtp_verify_peer,
    "smtp_verify_host" => $mediatype_smtp_verify_host
);

$zbx->call('mediatype.update',$params);

//print json_encode($params);

//display message box Record Been Added
print '<script>alert("Succesfully updated!");</script>';

//go to user.php page
print '<script>window.location.assign("smtp_setup.php");</script>';
?>