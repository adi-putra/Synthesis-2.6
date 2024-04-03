<?php
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include "session.php";

//only allow admin to see user list
if ($zabUtype !== "3") {
	//display error message box
	print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';

    die();
}

// assign data from customer form into variable
$userid = $_GET['userid'];
$enableDelete = false;

//check if user is under alerting
$params = array(
"output" => "extend",
"userids" => $userid
);
$checkAlert = $zbx->call('action.get',$params);

if (empty($checkAlert)) {
    $enableDelete = true;
}

if ($enableDelete == true) {
    $params = array($userid);

    $zbx->call('user.delete',$params);

    //display message box Record Been Added
    print '<script>alert("User has been deleted.");</script>';
}
else {
    //display alert
    print '<script>alert("Process aborted. \nThis user is used under alert setup.");</script>';
}

//go to user.php page
print '<script>window.location.assign("user.php");</script>';


?>