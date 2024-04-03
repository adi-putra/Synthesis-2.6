<?php
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include "session.php";

$userid = $_POST["userid"];

$usermedia_email = $_POST["usermedia_email"];
$usermedia_active = $_POST["usermedia_active"];

//set media object array 
$media_obj = array();
for ($i=0; $i < count($usermedia_email); $i++) { 
    $media_obj[] = array(
        "mediatypeid" => 1,
        "sendto" => array($usermedia_email[$i]),
        "active" => $usermedia_active[$i]
    );
}

//call api to update user media with media obj
$params = array(
	"userid" => $userid,
	"user_medias" => $media_obj
);

$zbx->call('user.update',$params);

//print json_encode($media_obj);

//display message box Record Been Added
print '<script>alert("Succesfully updated!");</script>';

//go to user.php page
print '<script>window.location.assign("user.php");</script>';
?>