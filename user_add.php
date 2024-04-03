<?php
include "session.php";

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

//only allow admin to see user list
if ($zabUtype !== "3") {
	//display error message box
	print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';
 }

else {
    
    // assign data from customer form into variable
    $username = $_GET['username'];
    $userpassword = $_GET['userpassword'];
    $userrole = $_GET['userrole'];
    $usrgrpid = $_GET['usrgrpid'];
    //$usertype = $_GET['usertype'];

    $params = array(
    "username" => $username,
    "passwd" => $userpassword,
    "roleid" => $userrole,
    "usrgrps" => [array("usrgrpid" => $usrgrpid)],
    );

    $zbx->call('user.create',$params);

    //display message box Record Been Added
    print '<script>alert("Successfully add user.");</script>';

    //go to user.php page
    print '<script>window.location.assign("user.php");</script>';
}

?>