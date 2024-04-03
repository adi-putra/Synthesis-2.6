<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get variables 
$hostid = $_POST["hostid"];
$eventid = $_POST["eventid"];
$ack_message = $_POST["ack_message"];
$ack_status = $_POST["ack_status"];
$ack_severity = $_POST["ack_severity"];
$old_severity = $_POST["old_severity"];
$ack_close = $_POST["ack_close"];
$event_range = $_POST["event_range"];
$event_objectid = $_POST["objectid"];

//if ack message is present, message_bit_value = 4
if ($ack_message == "") {
    $message_bit_value = 0;
}
else {
    $message_bit_value = 4;
}

//if ack status is not present, status_bit_value = 0
if ($ack_status == "") {
    $status_bit_value = 0;
    $ack_status = 0;
}
else {
    $status_bit_value = $ack_status;
    $ack_status = 1;
}

//if ack close is not present, close_bit_value = 0
if ($ack_close == "") {
    $close_bit_value = 0;
    $ack_close = 0;
}
else {
    $close_bit_value = 1;
    $ack_close = 1;
}

//if ack severity is present, $severity_bit_value = 0, replace new severity with old sev
// if ($ack_severity == "") {
//     $severity_bit_value = 0;
//     $new_severity = $old_severity;
// }
// else {
//     $new_severity = $ack_severity;
//     $severity_bit_value = 8;
// }

//calculate total actions value
$totalAction = $message_bit_value + $status_bit_value + $close_bit_value + $severity_bit_value;


//if event range is 1, get all related events by objectid
if ($event_range == 1) {
    $params = array(
        "output" => "extend",
        "objectids" => $event_objectid
    );
    
    $result = $zbx->call('event.get', $params);
    foreach ($result as $event) {
        //echo $event["eventid"];
        //execute acknowledgment
        $params = array(
            "eventids" => $event["eventid"],
            "action" => $totalAction,
            "message" => $ack_message,
            // "severity" => $ack_severity
        );

        $zbx->call('event.acknowledge', $params);
    }
}
else {
    //execute acknowledgment
    $params = array(
        "eventids" => $eventid,
        "action" => $totalAction,
        "message" => $ack_message,
        // "severity" => $ack_severity
    );

    $zbx->call('event.acknowledge', $params);
}

//echo $hostid."<br>";

//echo $eventid." ".$ack_message." ".$ack_status." ".$ack_severity." ".$ack_close."<br>";

//echo $message_bit_value." ".$status_bit_value." ".$severity_bit_value." ".$close_bit_value."<br>";

//echo $totalAction."<br>";


//head back to respective host details page
//header("location: $link");

?>