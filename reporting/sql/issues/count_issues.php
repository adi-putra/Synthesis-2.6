<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/adminlte/db/db_conn.php");

$hostid = $_GET["hostid"];
$groupid = $_GET["groupid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  //return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
  if ($seconds < 86400) {
	return $dtF->diff($dtT)->format('%h hours %i minutes');
  }
  else if ($seconds >= 86400) {
	return $dtF->diff($dtT)->format('%a days');
  }
}

// PROBLEMS COUNT
$problem_data = array(); //store pivot JSON
$problem_count = 0;

$params = array(
"output" => array("eventid", "severity", "acknowledged", "name", "clock"),
"hostids" => $hostid,
"groupids" => $groupid,
"time_from" => $timefrom,
"time_till" => $timetill
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get',$params);

//count severity
$count_unclass_sev = 0;
$count_info_sev = 0;
$count_warning_sev = 0;
$count_average_sev = 0;
$count_high_sev = 0;
$count_disaster_sev = 0;


foreach ($result as $v) {

//get values
$name = $v['name'];
$acknowledged = $v['acknowledged'];
$eventid = $v['eventid'];
$severity = $v['severity'];

//call db to check ack records
$ack = array();
$sql = "SELECT * FROM ack WHERE eventid='$eventid' ORDER BY ack_date DESC";
$db_result = $conn->query($sql);
$ack_data = "";
$count_ack = 0;
if ($db_result->num_rows > 0) {
    while($db_row = $db_result->fetch_assoc()) {
        //get values
        $ack_user = $db_row["ack_user"];
        $ack_date = date("Y-m-d\\TH:i:s", strtotime($db_row["ack_date"]));
        $ack_message = $db_row["ack_message"];

        //if problem is closed, skip
        if ($count_ack == 0) {
            if ($db_row["ack_close"] == 1) {
                continue 2;
            }
            
            $acknowledged = $db_row["ack_status"];
        }

        //check if db message is too long
        if (strlen($db_row["ack_message"]) > 50) {
            $db_row["ack_message"] = substr($db_row["ack_message"], 0, 50)."...";
        }
        $count_ack++;
    }
}
else {
    //if no ack records, set all by default
    $acknowledged = 0;
    $ack_date = "";
    $ack_data = "";
    $count_ack = 0;
}

//get hostid and hostname for the problem
$params = array(
    "eventids" => $eventid,
    "selectHosts" => array("hostid", "name")
  );
//call api problem.get only to get eventid
$event_result = $zbx->call('event.get',$params);
foreach ($event_result as $event) {
    foreach ($event["hosts"] as $host) {
        $gethostid = $host["hostid"];
        $gethostname = $host["name"];
    }
}

//time config
$datetime = date("Y-m-d", $v["clock"]);
$date = date("Y-m-d", $v["clock"]);
$time = date("H:i:s", $v["clock"]);
$time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
$time = $hours * 3600 + $minutes * 60 + $seconds;
$v["Hour"] = date("H", $v["clock"]);
$hour = $v["Hour"];
$v["Minute"] = date("i", $v["clock"]);
$minute = $v["Minute"];
$v["Second"] = date("s", $v["clock"]);
$second = $v["Second"];

//get description of problem
$params = array(
    "output" => array("comments"),
    "search" => array("description" => $name)
);
//call api problem.get only to get eventid
$trigger_result = $zbx->call('trigger.get',$params);
foreach ($trigger_result as $trigger) {
    $description = $trigger["comments"];
}

//get alert message for the problem
$params = array(
    "output" => array("message"),
    "eventids" => $eventid,
  );
//call api problem.get only to get eventid
$result = $zbx->call('alert.get',$params);

foreach ($result as $alert) {
    $message_str = $alert["message"];
    //capture operational data value only
    $startpoint_str = stripos($message_str, "operational data");
    $endpoint_str = stripos($message_str, "original problem");
    $message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
    //$message = substr($message_str, 0, -$endpoint_str);
    //$message = rtrim($message, "Original");
}

//severity
if ($severity == 1) {
    //count severity
    $count_info_sev++;
    $severity = "Info";
}
else if ($severity == 2) {
    //count severity
    $count_warning_sev++;
    $severity = "Warning";
}
else if ($severity == 3) {
    //count severity
    $count_average_sev++;
    $severity = "Average";
}
else if ($severity == 4) {
    //count severity
    $count_high_sev++;
    $severity = "High";
}
else if ($severity == 5) {
    //count severity
    $count_disaster_sev++;
    $severity = "Disaster";
}
else {
    //count severity
    $count_unclass_sev++;
    $severity = "Unclassified";
}

//acknowledge
/*if (!empty($v["acknowledges"])) {
    //start count_ack
    $count_ack = 0;
    //declare ack_data
    $ack_data = "";
    foreach (array_reverse($v["acknowledges"]) as $ack) {
        //start with tr
        $ack_data .= "<tr>";

        //get ack values
        $ack_user = $ack["userid"];
        $ack_clock = $ack["clock"];
        $ack_message = $ack["message"];
        

        //get user alias
        $params = array(
            "output" => array("alias"),
            "userids" => $ack_user
          );
        $user_result = $zbx->call('user.get',$params);
        if (!empty($user_result)) {
            foreach ($user_result as $user) {
                $user_alias = $user["alias"];
            }
        }
        else {
            $user_alias = "Inaccessible User";
        }

        //get time
        $ack_clock = date("Y-m-d\ H:i:s A\ ", $ack_clock);

        //insert ack data
        $ack_data .= "<td>$ack_clock</td>
                    <td>$user_alias</td>
                    <td>$ack_message</td>";
        
        //ends with /tr
        $ack_data .= "</tr>";

        //add +1 count_ack
        $count_ack++;
    }
}
else {
    $ack_data = "";
    $count_ack= 0;
}*/

//determine acknowledge or not, and place ack data for popover
if ($acknowledged == 1) {
    $acknowledged = "Yes";
}
else {
    $acknowledged = "No";
}

//problem duration
$time_diff = time() - $clock;
$duration = secondsToTime($time_diff);

//ack button (Save for later)
//print "<td><!-- Trigger the modal with a button -->
//<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";

//ack 2 button
//button to open form for unacknowledge problems

$problem_data[$problem_count] = array(
    "Event_ID" => "$eventid",
    "Problem_Date" => "$datetime",
    "Date" => $date,
    "Time" => $time,
    "Hour" => $hour,
    "Minute" => $minute,
    "Second" => $second,
    "Time" => "$time",
    "Host" => "$gethostname",
    "Problem" => "$name",
    "Severity" => "$severity",
    "Acknowledged" => "$acknowledged"
);

$problem_count++;
}

//echo $json_problem;


//RESOLVED PROBLEMS PIVOT
$resolved_data = array(); //store pivot JSON
$resolved_count = 0;

$params = array(
    "output" => array("eventid", "r_eventid", "severity", "acknowledged", "name", "clock", "value", "userid"),
    "hostids" => $hostid,
    "groupids" => $groupid,
    "select_acknowledges" => array("userid", "action", "clock", "message", "old_severity", "new_severity"),
    "time_from" => $timefrom,
    "time_till" => $timetill,
    "selectHosts" => array("hostid", "name")
);
//call api problem.get only to get eventid
$result = $zbx->call('event.get',$params);
//count id
$id = 1;


foreach ($result as $v) {

    //get values
    $name = $v['name'];
    $severity = $v['severity'];
    $acknowledged = $v['acknowledged'];
    $eventid = $v['eventid'];
    $r_eventid = $v["r_eventid"];
    $status_value = $v["value"];
    $userid = $v["userid"];

    //declare indicator for closed problem
    $problem_isClosed = "";

    //get hostid and hostname for the event
    foreach ($v["hosts"] as $host) {
        $gethostid = $host["hostid"];
        $gethostname = $host["name"];
    }

    //time config
    $datetime = date("Y-m-d", $v["clock"]);
    $date = date("Y-m-d", $v["clock"]);
    $time = date("H:i:s", $v["clock"]);
    $time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
    sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
    $time = $hours * 3600 + $minutes * 60 + $seconds;
    $v["Hour"] = date("H", $v["clock"]);
    $hour = $v["Hour"];
    $v["Minute"] = date("i", $v["clock"]);
    $minute = $v["Minute"];
    $v["Second"] = date("s", $v["clock"]);
    $second = $v["Second"];

    //call db to check ack records
    $ack = array();
    $sql = "SELECT * FROM ack WHERE eventid='$eventid' ORDER BY ack_date DESC";
    $db_result = $conn->query($sql);
    $ack_data = "";
    $count_ack = 0;
    if ($db_result->num_rows > 0) {
        while($db_row = $db_result->fetch_assoc()) {
            if ($count_ack == 0) {
                if ($db_row["ack_close"] == 1) {
                    continue 2;
                }
                $acknowledged = $db_row["ack_status"];
            }
            
            //check if db message is too long
            if (strlen($db_row["ack_message"]) > 50) {
                $db_row["ack_message"] = substr($db_row["ack_message"], 0, 50)."...";
            }
            $count_ack++;
        }
    }
    else {
        //if no ack records, set all by default
        $acknowledged = 0;
        $ack_data = "";
        $count_ack = 0;
    }

    //continue if problem is closed by user
    if ($problem_isClosed == 1) {
        continue;
    }

    //time format
    $clock = $v["clock"];
    $clock1 = date("Y-m-d\ H:i:s A\ ", $clock);

    //get recovery time by r_eventid
    //determine status_value
    if ($r_eventid != 0) {
        $params = array(
            "output" => array("clock"),
            "eventids" => $r_eventid
        );
        $recovery_result = $zbx->call('event.get', $params);
        foreach ($recovery_result as $recovery) {
            //$recovery['r_clock'] = $recovery['r_clock'] * 1000;
            $recovery_time = date("Y-m-d\ H:i:s A\ ", $recovery['clock']);
            $status_value = 0;
            //echo $recovery_time;
        }
    }
    else {
        //check if the problem is closed by user
        if ($userid != 0) {
            //get user name
            $params = array(
                "output" => array("alias"),
                "userids" => $userid
            );
            $user_result = $zbx->call('user.get', $params);
            foreach ($user_result as $user) {
                $userAlias = $user["alias"];
                $recovery_time = "";
                $status_value = 2;
            }
        }
        else {
            continue;
        }
    }	

    //determine status
    if ($status_value == 0) {
        $status_value = "RESOLVED";
    }
    else if ($status_value == 2) {
        continue;
    }

    //get description of problem
    $params = array(
        "output" => array("comments"),
        "search" => array("description" => $name)
    );
    //call api problem.get only to get eventid
    $trigger_result = $zbx->call('trigger.get',$params);
    foreach ($trigger_result as $trigger) {
        $description = $trigger["comments"];
    }

    //get alert message for the problem
    $params = array(
        "output" => array("message"),
        "eventids" => $eventid
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('alert.get',$params);

    foreach ($result as $alert) {
        $message_str = $alert["message"];
        //capture operational data value only
        $startpoint_str = stripos($message_str, "operational data");
        $endpoint_str = stripos($message_str, "original problem");
        $message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
        //$message = substr($message_str, 0, -$endpoint_str);
        //$message = rtrim($message, "Original");
    }

    //severity
    if ($severity == 1) {
        //count severity
        $count_info_sev++;
        $severity = "Info";
    }
    else if ($severity == 2) {
        //count severity
        $count_warning_sev++;
        $severity = "Warning";
    }
    else if ($severity == 3) {
        //count severity
        $count_average_sev++;
        $severity = "Average";
    }
    else if ($severity == 4) {
        //count severity
        $count_high_sev++;
        $severity = "High";
    }
    else if ($severity == 5) {
        //count severity
        $count_disaster_sev++;
        $severity = "Disaster";
    }
    else {
        //count severity
        $count_unclass_sev++;
        $severity = "Unclassified";
    }

    //determine acknowledge or not, and place ack data for popover
    if ($acknowledged == 1) {
        $acknowledged = "Yes";
    }
    else {
        $acknowledged = "No";
    }

    //problem duration
    $time_diff = time() - $clock;
    $duration = secondsToTime($time_diff);

    //ack button (Save for later)
    //print "<td><!-- Trigger the modal with a button -->
    //<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";

    $resolved_data[$resolved_count] = array(
        "Event_ID" => "$eventid",
        "Problem_Date" => "$datetime",
        "Date" => $date,
        "Time" => $time,
        "Hour" => $hour,
        "Minute" => $minute,
        "Second" => $second,
        "Time" => "$time",
        "Recovery_Time" => "$recovery_time",
        "Host" => "$gethostname",
        "Problem" => "$name",
        "Severity" => "$severity",
        "Status" => "$status_value",
        "Acknowledged" => "$acknowledged"
    );

    $resolved_count++;
}	

//echo $json_resolved;

//RESOLVED PROBLEMS PIVOT
$closed_data = array(); //store pivot JSON
$closed_count = 0;

//db query to fetch only with status 2
$sql = "SELECT * FROM event WHERE event_status=1";
$db_result = $conn->query($sql);

//get eventid and send in array
$geteventid = array();
while($db_row = $db_result->fetch_assoc()) {
    $geteventid[] = $db_row["eventid"];
}

//api query
$params = array(
"output" => array("eventid", "severity", "acknowledged", "name", "clock", "value"),
"eventids" => $geteventid,
"hostids" => $hostid,
"groupids" => $groupid,
"selectAcknowledges" => array("userid", "action", "clock", "message", "old_severity", "new_severity"),
"time_from" => $timefrom,
"time_till" => $timetill
);
//call api problem.get only to get eventid
$result = $zbx->call('event.get',$params);

//count id
$id = 1;


foreach ($result as $v) {

//get values
$name = $v['name'];
$acknowledged = $v['acknowledged'];
$eventid = $v['eventid'];
$severity = $v['severity'];


//call db to check ack records
$ack = array();
$sql = "SELECT * FROM ack WHERE eventid='$eventid' ORDER BY ack_date DESC";
$db_result = $conn->query($sql);
$ack_data = "";
$count_ack = 0;
if ($db_result->num_rows > 0) {
    while($db_row = $db_result->fetch_assoc()) {
        if ($count_ack == 0) {
            if ($db_row["ack_close"] != 1) {
                continue 2;
            }
            $acknowledged = $db_row["ack_status"];
            $closedBy = $db_row["ack_user"];
            $recovery_time = $db_row["ack_date"];
        }

        //check if db message is too long
        if (strlen($db_row["ack_message"]) > 50) {
            $db_row["ack_message"] = substr($db_row["ack_message"], 0, 50)."...";
        }
        $count_ack++;
    }
}
else {
    //if no ack records, set all by default
    $acknowledged = 0;
    $ack_data = "";
    $count_ack = 0;
}

//get hostid and hostname for the problem
$params = array(
    "eventids" => $eventid,
    "selectHosts" => array("hostid", "name")
    );
//call api problem.get only to get eventid
$event_result = $zbx->call('event.get',$params);
foreach ($event_result as $event) {
    foreach ($event["hosts"] as $host) {
        $gethostid = $host["hostid"];
        $gethostname = $host["name"];
    }
}

//time config
$datetime = date("Y-m-d", $v["clock"]);
$date = date("Y-m-d", $v["clock"]);
$time = date("H:i:s", $v["clock"]);
$time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
$time = $hours * 3600 + $minutes * 60 + $seconds;
$v["Hour"] = date("H", $v["clock"]);
$hour = $v["Hour"];
$v["Minute"] = date("i", $v["clock"]);
$minute = $v["Minute"];
$v["Second"] = date("s", $v["clock"]);
$second = $v["Second"];

//get description of problem
$params = array(
    "output" => array("comments"),
    "search" => array("description" => $name)
);
//call api problem.get only to get eventid
$trigger_result = $zbx->call('trigger.get',$params);
foreach ($trigger_result as $trigger) {
    $description = $trigger["comments"];
}

//get alert message for the problem
$params = array(
    "output" => array("message"),
    "eventids" => $eventid,
    );
//call api problem.get only to get eventid
$result = $zbx->call('alert.get',$params);

foreach ($result as $alert) {
    $message_str = $alert["message"];
    //capture operational data value only
    $startpoint_str = stripos($message_str, "operational data");
    $endpoint_str = stripos($message_str, "original problem");
    $message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
    //$message = substr($message_str, 0, -$endpoint_str);
    //$message = rtrim($message, "Original");
}


//severity
if ($severity == 1) {
    //count severity
    $count_info_sev++;
    $severity = "Info";
}
else if ($severity == 2) {
    //count severity
    $count_warning_sev++;
    $severity = "Warning";
}
else if ($severity == 3) {
    //count severity
    $count_average_sev++;
    $severity = "Average";
}
else if ($severity == 4) {
    //count severity
    $count_high_sev++;
    $severity = "High";
}
else if ($severity == 5) {
    //count severity
    $count_disaster_sev++;
    $severity = "Disaster";
}
else {
    //count severity
    $count_unclass_sev++;
    $severity = "Unclassified";
}

//acknowledge
/*if (!empty($v["acknowledges"])) {
    //start count_ack
    $count_ack = 0;
    //declare ack_data
    $ack_data = "";
    foreach (array_reverse($v["acknowledges"]) as $ack) {
        //start with tr
        $ack_data .= "<tr>";

        //get ack values
        $ack_user = $ack["userid"];
        $ack_clock = $ack["clock"];
        $ack_message = $ack["message"];
        

        //get user alias
        $params = array(
            "output" => array("alias"),
            "userids" => $ack_user
            );
        $user_result = $zbx->call('user.get',$params);
        if (!empty($user_result)) {
            foreach ($user_result as $user) {
                $user_alias = $user["alias"];
            }
        }
        else {
            $user_alias = "Inaccessible User";
        }

        //get time
        $ack_clock = date("Y-m-d\ H:i:s A\ ", $ack_clock);

        //insert ack data
        $ack_data .= "<td>$ack_clock</td>
                    <td>$user_alias</td>
                    <td>$ack_message</td>";
        
        //ends with /tr
        $ack_data .= "</tr>";

        //add +1 count_ack
        $count_ack++;
    }
}
else {
    $ack_data = "";
    $count_ack= 0;
}*/

//determine acknowledge or not, and place ack data for popover
if ($acknowledged == 1) {
    $acknowledged = "Yes";
}
else {
    $acknowledged = "No";
}

//problem duration
$time_diff = time() - $clock;
$duration = secondsToTime($time_diff);

//ack button (Save for later)
//print "<td><!-- Trigger the modal with a button -->
//<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";

//ack 2 button
//button to open form for unacknowledge problems


$closed_data[$closed_count] = array(
    "Event_ID" => "$eventid",
    "Problem_Date" => "$datetime",
    "Date" => $date,
    "Time" => $time,
    "Hour" => $hour,
    "Minute" => $minute,
    "Second" => $second,
    "Time" => "$time",
    "Recovery_Time" => "$recovery_time",
    "Host" => "$gethostname",
    "Problem" => "$name",
    "Severity" => "$severity",
    "Closed_By" => "$closedBy",
    "Acknowledged" => "$acknowledged"
);

$closed_count++;
}	

//echo $json_resolved;

?>
<html>
	<head>
    </head>
    <body>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
            <th>Category</th>
            <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <td>Unresolved Problems</td>
            <td><?php echo $problem_count; ?></td>
            </tr>
            <tr>
            <td>Resolved Problems</td>
            <td><?php echo $resolved_count; ?></td>
            </tr>
            <tr>
            <td>Closed Problems</td>
            <td><?php echo $closed_count; ?></td>
            </tr>
        </tbody>
    </table>
    </body>
</html>