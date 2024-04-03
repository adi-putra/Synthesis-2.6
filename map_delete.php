<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
// include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include "session.php";

$mapid = $_GET['mapid'];

$params = array(
    $mapid
);
$zbx->call('map.delete', $params);

//display message box Record Been Added
print '<script>alert("Host group map has been deleted.");</script>';

//go to user.php page
print '<script>window.location.assign("map.php");</script>';    

?>