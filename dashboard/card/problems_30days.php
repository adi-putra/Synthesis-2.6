<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

$hostid = $_GET["hostid"];
$groupid = $_GET["groupid"];

$timefrom = $_GET['timefrom'] ?? strtotime("-30 days");
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



?>
	        <?php
	        $params = array(
	        "output" => array("eventid", "objectid", "severity", "acknowledged", "name", "clock"),
	        "hostids" => $hostid,
			"groupids" => $groupid,
			"selectAcknowledges" => array("userid", "action", "clock", "message", "old_severity", "new_severity"),
			"time_from" => $timefrom,
			"time_till" => $timetill
	      );
	    //call api problem.get only to get eventid
	    $result = $zbx->call('problem.get',$params);

	    //count id
	    $id = 1;


	      foreach ($result as $v) {

			//call db to check ack records
			$ack = array();
			$sql = "SELECT * FROM ack WHERE eventid='$eventid' ORDER BY ack_date DESC";
			$db_result = $conn->query($sql);
			$ack_data = "";
			$count_ack = 0;
			if ($db_result->num_rows > 0) {
				while($db_row = $db_result->fetch_assoc()) {
					//if problem is closed
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
					
					//start with tr
					$ack_data .= "<tr>
								<td>".$db_row["ack_date"]."</td>
								<td>".$db_row["ack_user"]."</td>
								<td>".$db_row["ack_message"]."</td>
								</tr>";
					
					$count_ack++;
				}
			}
			else {
				//if no ack records, set all by default
				$acknowledged = 0;
				$ack_data = "";
				$count_ack = 0;
			}

			$id = $id + 1;
	        }

            $id = $id - 1;

            echo $id;
	        
	        ?>