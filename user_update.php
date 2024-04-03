<?php

//Author: Adiputra

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

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
 $userid = $_GET['userid'];
 $useralias = $_GET['username'];
 $userpassword = $_GET['userpassword'];
 $userrole = $_GET['userrole'];
 $usrgrpid = $_GET['usrgrpid'];
 $usermedia_email = $_GET["usermedia_email"];
 $usermedia_active = $_GET["usermedia_active"];
 $thisisuser = $_GET["thisisuser"];


 //set media object array 
 $media_obj = array();
if (!empty($usermedia_email) || !empty($usermedia_active)) {

   for ($i=0; $i < count($usermedia_email); $i++) { 
      $media_obj[] = array(
          "mediatypeid" => 1,
          "sendto" => $usermedia_email[$i],
          "active" => $usermedia_active[$i]
      );
  }
}

 if ($userpassword != "") {
    $params = array(
    "userid" => $userid,
    "username" => $useralias,
    "roleid" => $userrole,
    "usrgrps" => [array("usrgrpid" => $usrgrpid)],
    "passwd" => $userpassword,
    "user_medias" => $media_obj
    );
 }
 else {
    $params = array(
    "userid" => $userid,
    "username" => $useralias,
    "roleid" => $userrole,
    "usrgrps" => [array("usrgrpid" => $usrgrpid)],
    "user_medias" => $media_obj
    );
 }

 //$usertype = $_GET['usertype'];

//  print "<pre>";
//  print json_encode($params, JSON_PRETTY_PRINT);
//  print "</pre>";

//  die();

$zbx->call('user.update',$params);

if ($thisisuser == 1) {
   $_SESSION["login_user"] = $useralias;
   $_SESSION["password_user"] = $userpassword;
}

//display message box Record Been Added
print '<script>alert("Successfully update user.");</script>';

//go to user.php page
print '<script>window.location.assign("user.php");</script>';
}

?>
