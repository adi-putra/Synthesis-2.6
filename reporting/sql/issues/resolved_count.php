<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/adminlte/db/db_conn.php");

$hostid = $_GET["hostid"];
$groupid = $_GET["groupid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//echo $timetill;

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

    $resolved_count++;
}	

//echo $json_resolved;

?>
<html>
	<head>
    </head>
    <body>
    <table class="table table-bordered table-striped">
        <tbody>
            <tr>
            <td>Resolved Problems</td>
            <td><?php echo $resolved_count; ?></td>
            </tr>
        </tbody>
    </table>
    </body>
</html>