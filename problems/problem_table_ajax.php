<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
//include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

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
		<link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet" />
	</head>
	<body>
	<table id="example" class="display" style="width:100%">
		<thead>
			<tr>
				<th>Date</th>
				<th>Duration</th>
				<th>Host</th>
				<th>Problem</th>
				<th>Severity</th>
				<th>Acknowledged</th>
				<th>Message</th>
				<th>Description</th>
		</thead>
	</table>
	</body>
</html>


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

$problemsdata_json = array();

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
$id = 0;

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

	//time format
	$clock = $v["clock"];
	$clock1 = date("Y-m-d\ H:i:s A\ ", $clock);

	//problem duration
	$time_diff = time() - $clock;
	$duration = secondsToTime($time_diff);

	//if no ack records, set all by default
	$acknowledged = 0;
	$ack_data = "";
	$count_ack = 0;

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


	//print hostname
	//$hostlink = hostToLink($gethostid, $getgroupid, $zbx);
	//print "<td><a href='".$hostlink."'>$gethostname</a></td>";
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
			$message = str_replace('"', "'", $message);
		}
	}

	//get values
	$problems_param = array(
		"name" => $v["name"],
		"acknowledged" => $v['acknowledged'],
		//$eventid => $v['eventid'];
		"severity" => $v['severity'],
		"datetime" => $clock1,
		"duration" => $duration,
		"message" => htmlentities($message),
		"description" => $description
		//$objectid => $v['objectid']
	);

	//get values
	$hosts_param = array(
		"hostname" => $gethostname,
		//$objectid => $v['objectid']
	);

	$problemsdata_json[$id] = array(
		"Problems" => $problems_param,
		"Hosts" => $hosts_param
	);

	$problems_param = array();
	$hosts_param = array();


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

	$id++;
}

$problemsdata = array(
	"data" => $problemsdata_json
);

//print json_encode($problemsdata);
?>




<!-- Save for later
<script>
	function openForm(eventID) {
		var link = "/synthesis/testPost.php?eventid="+eventID;
		startIntProblemsTable();
		window.open(link, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=700,height=500");
	}
</script>
-->

<!-- jQuery 2.1.3 -->
<!-- DATA TABES SCRIPT -->
<script src="https://code.jquery.com/jquery-3.5.1.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" type="text/javascript"></script>

<script>

$('#example').DataTable( {
	"ajax": "test.php",
	"columns": [
		{ "data": "Problems.datetime" },
		{ "data": "Problems.duration" },
		{ "data": "Hosts.hostname" },
		{ "data": "Problems.name" },
		{ "data": "Problems.severity" },
		{ "data": "Problems.acknowledged" },
		{ "data": "Problems.message" },
		{ "data": "Problems.description" }
	]
} );

</script>