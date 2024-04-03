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
	$report_link = "report_closed.php?".$groupArr.$hostArr.$timerange;

	?>

	<a href='<?php echo $report_link; ?>' target="_blank"><button class="btn btn-default" style="float: right;"><i class='fa fa-mail-forward'></i>&nbsp;&nbsp;View Report</button></a>
	<br><br><br>
	<table id="closedprobtable" class="display" cellspacing="0" width="100%">
	<thead>
	        <tr>
	          <th></th>
	          <th>Problem Time</th>
			  <th>Closed Time</th>
			  <th>Username</th>
			  <th>Host</th>
	          <th>Problem</th>
	          <th>Severity</th>
	          <th>Acknow.</th>
			  <th>Status</th>
			  <th class="none">Duration</th>
			  <th class="none">Message</th>
			  <th class="none">Description</th>
			  <th class="none">Action</th>
	        </tr>
	      </thead>
	      <tbody>
	        <?php
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

			//time format
	        $clock = $v["clock"];
	        $clock1 = date("Y-m-d\ H:i:s A\ ", $clock);
			$recovery_time = $recovery_time;
			$recovery_time = date("Y-m-d\ H:i:s A\ ", strtotime($recovery_time));

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

			//print info button
	        print "<tr>
	              <td><button class='btn bg-default' style='width: 100%;'><i class='fa fa-info-circle'></i></button></td>";
	        
	        print "<td>$clock1</td>";

			//print recovery time
			print "<td>$recovery_time</td>";

			//print closed by who
			print "<td style='background-color: dodgerblue; color: white;'>$closedBy</td>";

			//print hostname
			print "<td>$gethostname</td>";

			//get alert message for the problem
			$params = array(
				"output" => array("message"),
				"eventids" => $eventid,
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
	          print "<button type='button' class='btn btn-success margin' data-placement='left' data-toggle='popover' data-content='".$tableTooltip."' style='margin: 0px;'><i class='fas fa-thumbs-up'></i> &nbsp;<span class='label label-success'>".$count_ack."</span></button>";
	        }
	        else {
	          print "<button type='button' class='btn btn-danger margin' data-placement='left' data-toggle='popover' data-content='".$tableTooltip."' style='margin: 0px;'><i class='fas fa-thumbs-down'></i> &nbsp;<span class='label label-danger'>".$count_ack."</span></button>";
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

			//ack button (Save for later)
			//print "<td><!-- Trigger the modal with a button -->
			//<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";

			//ack 2 button
			//button to open form for unacknowledge problems
			if ($acknowledged == 1) {
				print '<td><button onclick="loadModalAckForm('.$eventid.",".$gethostid.')" type="button" class="btn btn-warning margin" data-toggle="modal" data-target="#ackModal" data-toggle="tooltip" data-placement="right" title="Acknowledge Problem"><i class="fa fa-comment"></i></button></button></td>';
			}
			else {
				print '<td><button onclick="loadModalAckForm('.$eventid.",".$gethostid.')" type="button" class="btn btn-warning margin" data-toggle="modal" data-target="#ackModal" data-toggle="tooltip" data-placement="right" title="Acknowledge Problem"><i class="fa fa-comment"></i></button></button></td>';
			}
			
			
			//end table
	        print "</tr>";

			$id = $id + 1;
	        }
	        
	        ?>
	    </tbody>
</table>
	
	<!-- Modal to load form -->
	<div class="modal fade" id="ackModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="example-modal">
			<div class="modal">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" id="closeackModal" class="close" onclick="startIntClosedTable()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">Acknowledge Problem</h4>
						</div>
						<div id="ack_form">
							<!-- Loading Gif -->
							<div class="overlay">
								<i class="fa fa-refresh fa-spin"></i>
							</div>
						</div>
						<script>
						//function to load form w param eventid n hostid
						function loadModalAckForm(eventid, hostid) {
							stopIntClosedTable();
							$("#ack_form").load("/synthesis/problems/ack_form.php?eventid=" + eventid + "&hostid=" + hostid);
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
    var table = $('#closedprobtable').DataTable({
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
        order: [ 2, 'desc' ]
    } );

	//popover enabled
	table.$('[data-toggle="popover"]').popover({
			trigger: 'hover',
			html: true
		})
	
	//for prob count buttons
	function searchTableUnclassified() {
		table.search("Unclassified").draw();
	}
	function searchTableInfo() {
		table.search("info").draw();
	}
	function searchTableWarning() {
		table.search("warning").draw();
	}
	function searchTableAverage() {
		table.search("average").draw();
	}
	function searchTableHigh() {
		table.search("high").draw();
	}
	function searchTableDisaster() {
		table.search("disaster").draw();
	}

	//for ack count buttons
	function searchTableYes() {
		table.search("green").draw();
	}
	function searchTableNo() {
		table.search("red").draw();
	}
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