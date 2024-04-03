<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

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

?>
<html>
	<head>
		<style>
			.popover{
				width: auto;
				height:auto;   
				max-width:none; 
				max-height:none; 
			}
			.popover-content {
				font-size: 11px;
			}
			.example-modal .modal {
				position: relative;
				top: 250px;
				bottom: auto;
				right: auto;
				left: 150px;
				display: block;
				z-index: 1;
			}
			.example-modal .modal {
				background: transparent!important;
			}
		</style>
	</head>
	<?php	
	$hostArr = "";
	$groupArr = "";

	//if both arrays empty, let it empty
	if (!empty($hostid)) {
		foreach ($hostid as $hostID) {
			$hostArr .= "hostid[]=".$hostID."&";
		}
	}

	if (!empty($groupid)) {
		foreach ($groupid as $groupID) {
			$groupArr .= "groupid[]=".$groupID."&";
		}
	}

	$timerange = "&timefrom=".$timefrom."&timetill=".$timetill;

	//link for report problems page
	$report_link = "report_history.php?".$groupArr.$hostArr.$timerange;

	?>

	<a href='<?php echo $report_link; ?>' target="_blank"><button class="btn btn-default" style="float: right;"><i class='fa fa-mail-forward'></i>&nbsp;&nbsp;View Report</button></a>
	<br><br><br>
<table id="historytable" class="display" cellspacing="0" width="100%">
	<thead>
	        <tr>
	          <th></th>
	          <th>Problem Time</th>
			  <th>Recovery Time</th>
			  <th>Host</th>
			  <th>Status</th>
	          <th>Problem</th>
	          <th>Severity</th>
	          <th>Acknow.</th>
			  <th>Status Ack</th>
			  <th class="none">Duration</th>
			  <th class="none">Message</th>
			  <th class="none">Description</th>
			  <th class="none">Action</th>
	        </tr>
	      </thead>
	      <tbody>
	        <?php
				$params = array(
					"output" => array("eventid", "r_eventid", "severity", "acknowledged", "name", "clock", "value", "userid", "objectid"),
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
					$objectid = $v['objectid'];
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
						$status_value = "<td style='background-color: green; color: white;'>RESOLVED</td>";
					}
					else if ($status_value == 2) {
						continue;
					}
					else {
						$status_value = "<td style='background-color: red; color: white;'>PROBLEM</td>";
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

					//print table
					print "<tr>
						<td><button class='btn bg-default' style='width: 100%;'><i class='fa fa-info-circle'></i></button></td>";
					
					print "<td>$clock1</td>";

					print "<td>$recovery_time</td>";

					print "<td>$gethostname</td>";

					//print event status
					print "$status_value";

					//get alert message for the problem
					$params = array(
						"output" => array("message"),
						"eventids" => $eventid
					);
					//call api problem.get only to get eventid
					$result = $zbx->call('alert.get',$params);

					if (empty($result)) {
						$message = "";
					}
					else {
						foreach ($result as $alert) {
							$message_str = $alert["message"];
							//capture operational data value only
							$startpoint_str = stripos($message_str, "operational data");
							$endpoint_str = stripos($message_str, "original problem");
							$message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
							//$message = substr($message_str, 0, -$endpoint_str);
							//$message = rtrim($message, "Original");
						}
					}

					print "<td>$name</td>";

					//severity
					print "<td style='text-align: center; padding: 0;'>";
					if ($severity == 1) {
					print "<button class='btn btn-info margin' style='width: 100px; margin: 0px;'>Info</button>";
					}
					else if ($severity == 2) {
					print "<button class='btn bg-yellow margin' style='width: 100px; margin: 0px;'>Warning</button>";
					}
					else if ($severity == 3) {
					print "<button class='btn bg-orange margin' style='width: 100px; margin: 0px;'>Average</button>";
					}
					else if ($severity == 4) {
					print "<button class='btn bg-red margin' style='width: 100px; margin: 0px;'>High</button>";
					}
					else if ($severity == 5) {
					print "<button class='btn bg-red margin' style='width: 100px; margin: 0px;'>Danger</button>";
					}
					else {
					print "<button class='btn bg-gray margin' style='width: 100px; margin: 0px;'>Unclassified</button>";
					}
					print "</td>";

					//declare table popover with ack data
					$tableTooltip = "<table>
										<thead>
											<tr>
												<th>Time</th>
												<th>User</th>
												<th>Message</th>
											</tr>
										</thead>
										<tbody>
											$ack_data
										</tbody>
									</table>";
									
					print "<td style='text-align: center; padding: 0;'>";

					//determine acknowledge or not, and place ack data for popover
					if ($acknowledged == 1) {
					print "<button type='button' class='btn btn-success margin' data-placement='left' data-toggle='popover1' data-content='".$tableTooltip."' style='margin: 0px;'><i class='fas fa-thumbs-up'></i> &nbsp;<span class='label label-success'>".$count_ack."</span></button>";
					}
					else {
					print "<button type='button' class='btn btn-danger margin' data-placement='left' data-toggle='popover1' data-content='".$tableTooltip."' style='margin: 0px;'><i class='fas fa-thumbs-down'></i> &nbsp;<span class='label label-danger'>".$count_ack."</span></button>";
					}

					print "</td>";
					
					//for hidden column
					if ($acknowledged == 1) {
						print "<td>green</td>";
					}
					else {
						print "<td>red</td>";
					}

					//problem duration
					$time_diff = time() - $clock;
					$duration = secondsToTime($time_diff);
					print "<td>$duration</td>";

					//problem message
					print "<td>$message</td>";

					//problem description
					print "<td>$description</td>";

					print '<td><button onclick="loadTimelineChart('.$objectid.', '.$gethostid.')" type="button" class="btn bg-green margin" data-toggle="modal" data-target="#timelineModal" data-toggle="tooltip" data-placement="right" title="View Timeline"><i class="fas fa-history"></i></button></button></td>';

					//ack button (Save for later)
					//print "<td><!-- Trigger the modal with a button -->
					//<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";
						
					//end table
					print "</tr>";

					$id = $id + 1;
					}		
			?>
	    </tbody>
</table>


<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="example-modal">
		<div class="modal">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" id="closeackModal" class="close" onclick="loadHistoryTable()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Problem's Timeline&nbsp;&nbsp;
							<i class="fas fa-history"></i> 
						</h4>
					</div>
					<div id="timeline_chart">
						<!-- Loading Gif -->
						<div class="overlay">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
					</div>
					<script>
					//function to load form w param eventid n hostid
					function loadTimelineChart(objectid, hostid) {
						$("#timeline_chart").load("/synthesis/problems/timeline_chart.php?objectid=" + objectid + "&hostid=" + hostid);
					}
					</script>
				</div>
			</div>
		</div>
	</div>
</div>
</html>

<script>
	//table settings
    var historytable = $('#historytable').DataTable({
        responsive: {
            details: {
                type: 'column'
            }
        },
        columnDefs: [ {
            // className: 'dtr-control',
            orderable: false,
            targets:   0
        }, {
			targets: [ 8 ],
            visible: false
		} ],
        order: [ 1, 'desc' ]
    } );

	//popover enabled
	historytable.$('[data-toggle="popover1"]').popover({
			trigger: 'hover',
			html: true
		})
</script>

<!-- Save for later
<script>
	function openForm(eventID) {
		var link = "/synthesis/testPost.php?eventid="+eventID;
		startIntProblemsTable();
		window.open(link, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=700,height=500");
	}
</script>
-->