<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

$hostid = $_GET["hostid"] ?? array("10729");
$groupid = $_GET["groupid"];

//time variables
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

//display time format
$diff = $timetill - $timefrom;

//count total number jobs
$jobs_total = 0;

foreach ($hostid as $hostID) {

    $params = array(
    "output" => array("name"),
    "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    $params = array(
    "output" => array("itemid", "name", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("key_" => 'vbr[JobsCount]'),//seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get',$params);
    if (empty($result)) {
        continue;
    }
    foreach ($result as $item) {
        $jobs_total = $jobs_total + $item["lastvalue"];
    }

}

print $jobs_total;
?>