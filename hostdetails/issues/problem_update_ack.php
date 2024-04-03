<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

//get variables 
$hostid = $_POST["hostid"];
$eventid = $_POST["eventid"];
$ack_message = $_POST["ack_message"];
$ack_status = $_POST["ack_status"];
$ack_severity = $_POST["ack_severity"];
$old_severity = $_POST["old_severity"];
$ack_close = $_POST["ack_close"];

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
    $ack_close == 0;
}
else {
    $close_bit_value = 1;
    $ack_close == 1;
}

//if ack severity is present, $severity_bit_value = 0, replace new severity with old sev
if ($ack_severity == "") {
    $severity_bit_value = 0;
    $new_severity = $old_severity;
}
else {
    $new_severity = $ack_severity;
    $severity_bit_value = 8;
}

//calculate total actions value
$totalAction = $message_bit_value + $status_bit_value + $close_bit_value + $severity_bit_value;


//execute acknowledgment
/*$params = array(
    "eventids" => $eventid,
    "action" => $totalAction,
    "message" => $ack_message,
    "severity" => $ack_severity
);

$zbx->call('event.acknowledge', $params);*/

//if all values is empty, dont execute this action
if ($totalAction !== 0) {
    //check if the eventid is already exist
    $sql = "SELECT * FROM event WHERE eventid='$eventid'";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        //execute acknowledgment and insert in db table event
        $sql = "INSERT INTO event (eventid, event_status) VALUES ('$eventid', '$ack_close')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        };  
    }
    else {
        //execute update to event table and add ack 
        $sql = "UPDATE event SET event_status='$ack_close' WHERE eventid=$eventid";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        };
    } 
    
    //insert in db table ack
    $sql = "INSERT INTO ack (ack_user, ack_status, ack_close, ack_message, eventid)
    VALUES ('$zabUser', '$ack_status', '$ack_close', '$ack_message', '$eventid')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } 
    else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

//echo $hostid."<br>";

//echo $eventid." ".$ack_message." ".$ack_status." ".$ack_severity." ".$ack_close."<br>";

//echo $message_bit_value." ".$status_bit_value." ".$severity_bit_value." ".$close_bit_value."<br>";

//echo $totalAction."<br>";

//determine the link for the hostid
$params = array(
    "output" => array("hostid", "name"),
    "hostids" => $hostid,
    "selectGroups" => array("name")
  );
  $result = $zbx->call('host.get', $params);
  foreach ($result as $row) {
    //get hostname
    $gethostname = $row["name"];
      
    //get group name
    foreach($row["groups"] as $group) {
        $getgroupname = $group["name"];
    }
    
    if (stripos($gethostname, "zabbix") !== false or stripos($gethostname, "linux") !== false or stripos($getgroupname, "zabbix") !== false or stripos($getgroupname, "linux") !== false) {
        $link = "hostdetails_linux.php?hostid=".$hostid;
    } else if (stripos($gethostname, "FW") !== false or stripos($gethostname, "Firewall") !== false or stripos($getgroupname, "FW") !== false or stripos($getgroupname, "Firewall") !== false) {
        $link = "hostdetails_firewall.php?hostid=".$hostid;
    } else if (stripos($gethostname, "ESX") !== false or stripos($gethostname, "esx") !== false or stripos($getgroupname, "ESX") !== false or stripos($getgroupname, "esx") !== false) {
        $link = "hostdetails_esx.php?hostid=".$hostid;
    } else if (stripos($gethostname, "imm") !== false or stripos($gethostname, "ilo") !== false or stripos($getgroupname, "ILO") !== false or stripos($getgroupname, "IMM") !== false) {
        $link = "hostdetails_ilo.php?hostid=".$hostid;
    } else if (stripos($gethostname, "UPS") !== false or stripos($gethostname, "ups") !== false or stripos($getgroupname, "UPS") !== false or stripos($getgroupname, "ups") !== false) {
        $link = "hostdetails_ups.php?hostid=".$hostid;
    } else if (stripos($gethostname, "switch") !== false or stripos($gethostname, "Switch") !== false or stripos($getgroupname, "switch") !== false or stripos($getgroupname, "Switch") !== false) {
        $link = "hostdetails_switches.php?hostid=".$hostid;
    } else {
        $link = "hostdetails_test.php?hostid=".$hostid;
    }
  }

  //echo $link;


//head back to respective host details page
//header("location: $link");

$conn->close();

?>