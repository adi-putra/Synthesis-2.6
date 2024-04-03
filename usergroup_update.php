<?php
include "session.php";

//only allow admin to see user list
if ($zabUtype !== "3") {
	//display error message box
	print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';
}

else {

//get variables
$usrgrpid = $_POST["usrgrpid"];
$usrgrpName = $_POST["usrgrpName"];
// $usrgrpFA = $_POST["usrgrpFA"];
$usrgrpFA = 0;

// if status is not check to enable, changed to 1
// else, get from GET request
if (!isset($_POST["usrgrpStatus"])) {
	$usrgrpStatus = 1;
}
else {
	$usrgrpStatus = $_POST["usrgrpStatus"];
}

// if debug mode is not checked, changed to 0
// else, get from GET request
if (!isset($_POST["usrgrpDebug"])) {
	$usrgrpDebug = 0;
}
else {
	$usrgrpDebug = $_POST["usrgrpDebug"];
}

//debug purpose
//echo $usrgrpName." ".$usrgrpFA." ".$usrgrpStatus." ".$usrgrpStatus;

//call api to create user group
$params = array(
	"usrgrpid" => $usrgrpid,
	"name" => $usrgrpName,
	"gui_access" => $usrgrpFA,
	"users_status" => $usrgrpStatus,
	"debug_mode" => $usrgrpDebug
);

$zbx->call('usergroup.update',$params);

//display message box Record Been Added
print '<script>alert("User Group Successfully Updated");</script>';

//go to user.php page
print '<script>window.location.assign("usergroup_settings.php?usrgrpid='.$usrgrpid.'");</script>';

}
?>