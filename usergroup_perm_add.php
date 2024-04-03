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
 $usrgrpid = $_POST['usrgrpid'];
 $groupid = $_POST['groupid'];
 $permission = $_POST['permission'];

 //check if there are same host group id entered by user
 $enable_to_add = true;
 $groupid_count_values = array_count_values($groupid);
 //$groupid_count = count($groupid_count_values);
 //print_r($groupid_count);

 foreach ($groupid_count_values as $groupid_value) {
 	if ($groupid_value > 1) {
 		// change to false if there is duplicate values
 		$enable_to_add = false;
 	}
 }

 if ($enable_to_add == false) {
 	print '<script>alert("Error! User input contain duplicate host group");</script>';

	//back to previous page
	print '<script>window.location.assign("usergroup_settings.php?usrgrpid='.$usrgrpid.'");</script>';
 }

 else if ($enable_to_add == true) {
 	//get original rights from user group
	$rightsArr = "";
	 $params = array(
	 	"output" => array("name"),
	    "usrgrpids" => $usrgrpid,
	    "selectRights" => "extend"
	);

	$result = $zbx->call('usergroup.get',$params);
	foreach ($result as $usrgrp) {
		$rightsArr = $usrgrp["rights"];
	}

	 //count the old rights array
	 $countOld = count($rightsArr);	

	 //push requested variables in array
	 $length = count($groupid);
	 for ($i=0; $i < $length; $i++) { 
	 	 $rightsArr[$countOld] = array("permission" => $permission[$i], "id" => $groupid[$i]);
	 	 $countOld++;
	 }

	 //echo json_encode($rightsArr);

	 $params = array(
	"usrgrpid" => $usrgrpid,
	"rights" => $rightsArr
	);

	$zbx->call('usergroup.update',$params);

	 //display message box Record Been Added
	print '<script>alert("Permission Had Been Added");</script>';

	//go to user.php page
	print '<script>window.location.assign("usergroup_settings.php?usrgrpid='.$usrgrpid.'");</script>';
 }
}

?>