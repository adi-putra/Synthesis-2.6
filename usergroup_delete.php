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
// assign data from customer form into variable
 $usrgrpid = $_GET['usrgrpid'];

//declare a variable to store enable to delete status
 $enable_to_delete = true;

 //array to store user names if it assigned to the selected user group
 $assigned_users = array();

 //check if the selected user group is assigned to any user
 $params = array(
 	"output" => array("userid", "alias"),
 	"selectUsrgrps" => array("usrgrpid", "name", "users_status")
 );

$result = $zbx->call('user.get',$params);
foreach ($result as $user) {
	$username = $user["alias"];
	foreach ($user["usrgrps"] as $usrgrp) {
		if ($usrgrp["usrgrpid"] == $usrgrpid) {
			$assigned_users[] = $username;
			$enable_to_delete = false;
		}
	}
}

//to sort user names if detected
$usernames = "";
$length = count($assigned_users);
for ($i=0; $i < $length; $i++) { 
	$usernames .= $assigned_users[$i].", ";
}
$usernames = substr($usernames, 0, -2);

//check enable to delete status and give alerts
if ($enable_to_delete == false) {
	print '<script>alert("Error! There are users assigned to this group:\n '.$usernames.'");</script>';

	//back to previous page
	print '<script>window.location.assign("usergroup_settings.php?usrgrpid='.$usrgrpid.'");</script>';
}

else if ($enable_to_delete == true) {
	$params = array($usrgrpid);

	$zbx->call('usergroup.delete',$params);

	//display message box Record Been Added
	print '<script>alert("User Group Had Been Deleted");</script>';

	//go to user.php page
	print '<script>window.location.assign("usergroup.php");</script>';
}

}

?>