<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include "session.php";

$main_id = $_GET["main_id"];

$params = array(
    $main_id
);

$zbx->call('maintenance.delete',$params);
    
//display success popup
echo '<script language="javascript">';
echo 'alert("Successfully Deleted!");';
echo 'location.assign("maintenance.php");';
echo '</script>';
?>