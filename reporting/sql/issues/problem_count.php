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

$problem_count++;
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
            <td>Unresolved Problems</td>
            <td><?php echo $problem_count; ?></td>
            </tr>
        </tbody>
    </table>
    </body>
</html>