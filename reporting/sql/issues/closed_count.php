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
$closed_count++;
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
            <td>Closed Problems</td>
            <td><?php echo $closed_count; ?></td>
            </tr>
        </tbody>
    </table>
    </body>
</html>