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

	function hostToLink($h_id, $g_id, $zbx) {
		$params = array(
			"output" => array("name"),
			"hostids" => $h_id,
			"groupids" => $g_id,
			"selectGroups" => array("name"),
		);
		//call api problem.get only to get eventid
		$hosttolink = $zbx->call('host.get',$params);
		foreach ($hosttolink as $htl) {
			$h_name = $htl["name"];
			foreach ($htl["groups"] as $htl_g) {
				if (stripos($htl_g["name"], 'linux') !== false || stripos($htl_g["name"], 'zabbix') !== false || stripos($htl_g["name"], 'sql') !== false || stripos($htl_g["name"], 'windows') !== false
				|| stripos($htl_g["name"], 'esx') !== false || stripos($htl_g["name"], 'ilo') !== false || stripos($htl_g["name"], 'imm') !== false
				|| stripos($htl_g["name"], 'switch') !== false || stripos($htl_g["name"], 'vm') !== false || stripos($htl_g["name"], '192.168') !== false
				|| stripos($htl_g["name"], 'templates') !== false) {
					$g_name = $htl_g["name"];
				}
			}

			if (stripos($h_name, 'linux') !== false || stripos($g_name, 'linux') !== false || stripos($h_name, 'zabbix') !== false || stripos($g_name, 'zabbix') !== false) {
				$link = "hostdetails_linux.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'firewall') !== false || stripos($g_name, 'firewall') !== false || stripos($h_name, 'fw') !== false || stripos($g_name, 'fw') !== false) {
				$link = "hostdetails_firewall.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'esx') !== false || stripos($g_name, 'esx') !== false) {
				$link = "hostdetails_esx.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'ups') !== false || stripos($g_name, 'ups') !== false) {
				$link = "hostdetails_ups.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'switches') !== false || stripos($g_name, 'switches') !== false) {
				$link = "hostdetails_switches.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'ilo') !== false || stripos($g_name, 'ilo') !== false) {
				$link = "hostdetails_ilo.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'imm') !== false || stripos($g_name, 'imm') !== false) {
				$link = "hostdetails_imm.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'vcenter') !== false || stripos($g_name, '192.168') !== false || stripos($h_name, 'vm') !== false || stripos($g_name, 'templates') !== false) {
				$link = "hostdetails_vm.php?hostid=$h_id";
			}
			else if (stripos($h_name, 'windows') !== false || stripos($g_name, 'windows') !== false || stripos($h_name, 'sql') !== false || stripos($g_name, 'sql') !== false){
				$link = "hostdetails_windows.php?hostid=$h_id";
			}
			else {
				$link = "#$g_name";
			}

			return $link;
		}
	} 
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
	$report_link = "report_problems.php?".$groupArr.$hostArr.$timerange;

	?>

	<a href='<?php echo $report_link; ?>' target="_blank"><button class="btn btn-default" style="float: right;"><i class='fa fa-mail-forward'></i>&nbsp;&nbsp;View Report</button></a>
	<br><br><br>
	<table id="problemstable" class="display" cellspacing="0" width="100%">
	<thead>
	        <tr>
	          <th></th>
	          <th>Problem Time</th>
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

		//count severity
		$count_all_sev = 0;
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
			$objectid = $v['objectid'];

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

			//get hostid and hostname for the problem
			$params = array(
				"eventids" => $eventid,
				"selectHosts" => array("hostid", "name"),
				"selectGroups" => array("groupid, name")
			  );
			//call api problem.get only to get eventid
			$event_result = $zbx->call('event.get',$params);
			foreach ($event_result as $event) {
				foreach ($event["hosts"] as $host) {
					$gethostid = $host["hostid"];
					$gethostname = $host["name"];
				}

				foreach ($event["groups"] as $group) {
					$getgroupid = $group["groupid"];
					$getgroupname = $group["name"];	
				}
			}

			//time format
	        $clock = $v["clock"];
	        $clock1 = date("Y-m-d\ H:i:s A\ ", $clock);

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

			//print hostname
			$hostlink = hostToLink($gethostid, $getgroupid, $zbx);
			print "<td><a href='".$hostlink."'>$gethostname</a></td>";
			//print "<td>$gethostname</td>";

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
				//count severity
				$count_info_sev++;
				$count_all_sev++;
				print "<button class='btn btn-info margin' style='width: 100px; margin: 0px;'>Info</button>";
	        }
	        else if ($severity == 2) {
				//count severity
				$count_warning_sev++;
				$count_all_sev++;
				print "<button class='btn bg-yellow margin' style='width: 100px; margin: 0px;'>Warning</button>";
	        }
	        else if ($severity == 3) {
				//count severity
				$count_average_sev++;
				$count_all_sev++;
				print "<button class='btn bg-orange margin' style='width: 100px; margin: 0px;'>Average</button>";
	        }
	        else if ($severity == 4) {
				//count severity
				$count_high_sev++;
				$count_all_sev++;
				print "<button class='btn bg-red margin' style='width: 100px; margin: 0px;'>High</button>";
	        }
	        else if ($severity == 5) {
				//count severity
				$count_disaster_sev++;
				$count_all_sev++;
				print "<button class='btn bg-red margin' style='width: 100px; margin: 0px;'>Danger</button>";
	        }
	        else {
				//count severity
				$count_unclass_sev++;
				$count_all_sev++;
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

			print "<td>";

			if ($acknowledged > 1) {
				print '<button onclick="loadModalAckForm('.$eventid.",".$gethostid.')" type="button" class="btn btn-warning margin" data-toggle="modal" data-target="#ackModal" data-toggle="tooltip" data-placement="right" title="Acknowledge Problem"><i class="fa fa-comment"></i></button></button>';
			}
			else {
				print '<button onclick="loadModalAckForm('.$eventid.",".$gethostid.')" type="button" class="btn btn-warning margin" data-toggle="modal" data-target="#ackModal" data-toggle="tooltip" data-placement="right" title="Acknowledge Problem"><i class="fa fa-comment"></i></button></button>';
			}
			
			print '<button onclick="loadTimelineChart('.$objectid.', '.$gethostid.')" type="button" class="btn bg-green margin" data-toggle="modal" data-target="#timelineModal" data-toggle="tooltip" data-placement="right" title="View Timeline"><i class="fas fa-history"></i></button></button>';

			print "</td>";
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
							<button type="button" id="closeackModal" class="close" onclick="startIntProblemsTable()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">Acknowledge Problem&nbsp;&nbsp;<i class="far fa-clipboard"></i></h4>
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
							stopIntProblemsTable();
							$("#ack_form").load("/synthesis/problems/ack_form.php?eventid=" + eventid + "&hostid=" + hostid);
						}
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Timeline Modal -->
	<div class="modal fade" id="timelineModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="example-modal">
			<div class="modal">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" id="closeackModal" class="close" onclick="startIntProblemsTable()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
							stopIntProblemsTable();
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
	//declare count sevs
	var count_all_sev = '<?php echo $count_all_sev;?>';
	var count_unclass_sev = '<?php echo $count_unclass_sev;?>';
	var count_info_sev = '<?php echo $count_info_sev;?>';
	var count_warning_sev = '<?php echo $count_warning_sev;?>';
	var count_average_sev = '<?php echo $count_average_sev;?>';
	var count_high_sev = '<?php echo $count_high_sev;?>';
	var count_disaster_sev = '<?php echo $count_disaster_sev;?>';

	//write to div
	$('#count_all').html(count_all_sev);
	$('#count_unclass').html(count_unclass_sev);
	$('#count_info').html(count_info_sev);
	$('#count_warning').html(count_warning_sev);
	$('#count_average').html(count_average_sev);
	$('#count_high').html(count_high_sev);
	$('#count_disaster').html(count_disaster_sev);

	//table settings
    var table = $('#problemstable').DataTable({
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
			targets: [ 6 ],
            visible: false
		} ],
        order: [ 1, 'desc' ]
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