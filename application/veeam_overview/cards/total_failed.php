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

//store itemid array
$itemid = array();
$count2 = 0;
$series = array();
$color = array();

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

    //last run time job
    $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("name" => "last run time job"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    if (!empty($result)) {
        foreach ($result as $item) {

            //get host name in veeam and item run id
            $gethost_veeam = substr($item["name"], 20);

            //last end time job
            $params = array(
            "output" => array("itemid", "name", "error", "lastvalue"),
            "hostids" => $hostID,
            "search" => array("name" => "$gethost_veeam - Result"), //seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get', $params);

            foreach ($result as $item) {
                //get item end id
                $item_id = $item["itemid"];
                $item_name = $item["name"];
            }

            $itemid[] = array(
                "itemid" => $item_id,
                "name" => $item_name
            );
        }
    }
}

// print "<pre>";
// print json_encode($itemid, JSON_PRETTY_PRINT);
// print "</pre>";

$itemdata = array();
$itemseries = "";

$count_failed = 0;

foreach ($itemid as $item) {

    $params = array(
        "output" => "extend",
        "itemids" => $item["itemid"],
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill
    );
    //call api history.get with params
    $result = $zbx->call('history.get', $params);

    // print "<pre>";
    // print json_encode($result, JSON_PRETTY_PRINT);
    // print "</pre>";

    if (!empty($result)) {
        foreach (array_reverse($result) as $history) {

            if ($history["value"] == 0) {
                $count_failed++;
            }
        }
    }
}

print $count_failed;
?>