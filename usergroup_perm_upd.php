<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include "session.php";

//only allow admin to see user list
if ($zabUtype !== "3") {
	//display error message box
	print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';
}

else {
// get requested permission
$get_permission = $_POST["permission"];

//print_r($permission);
$usrgrpid = $_POST['usrgrpid'];

//add counter 
$count = 0;

//add new array
$new_rights = array();

//call api to get user group permissions
$params = array(
	"output" => array("name"),
    "usrgrpids" => $usrgrpid,
    "selectRights" => "extend"
);

$result = $zbx->call('usergroup.get',$params);
foreach ($result as $usrgrp) {
	//store old rights in an array
	$old_rights = $usrgrp["rights"];
	foreach ($usrgrp["rights"] as $hostgroup) {
		//if permission is set to none, let it empty and continue
		if ($get_permission[$count] == "None") {
			$count++;
			continue;
		}
		else {
			//store requested permission n host group id in new array 
			$new_rights[$count] = array("permission" => $get_permission[$count], "id" => $hostgroup["id"]);
			$count++;
		}
	}
}

//check if new rights is same as old rights
if ($new_rights === $old_rights) {
	//display message box 
	print '<script>alert("Inputs same as old permissions. No changes made");</script>';

	//go to user.php page
	print '<script>window.location.assign("usergroup_settings.php?usrgrpid='.$usrgrpid.'");</script>';
}
else {
	$params = array(
	"usrgrpid" => $usrgrpid,
	"rights" => $new_rights
	);

	$zbx->call('usergroup.update',$params);

	 //display message box Record Been Added
	print '<script>alert("Permission Had Been Updated");</script>';

	//go to user.php page
	print '<script>window.location.assign("usergroup_settings.php?usrgrpid='.$usrgrpid.'");</script>';
}

}
?>