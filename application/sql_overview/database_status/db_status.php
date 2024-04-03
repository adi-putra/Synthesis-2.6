<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
</head>

<body>
	<?php
	//count host
	$countHost = count($hostid);

	//counters
	$count = 1;
	$countdb_online = 0;
	$countdb_offline = 0;
	$countdb_pending = 0;
	$countdb_recovering = 0;
	$countdb_restoring = 0;
	$countdb_suspect = 0;
	$count_others = 0;
	$totaldb = 0;

	foreach ($hostid as $hostID) {
		//get hostname
		$params = array(
		"output" => array("name"),
		"hostids" => $hostID,
		);
		//call api method
		$result = $zbx->call('host.get',$params);
		foreach ($result as $host) {
			$gethostname = $host["name"];
		}

		//get db status
		$params = array(
		"output" => array("itemid", "name", "lastvalue"),
		"hostids" => $hostID,
		"search" => array("key_" => '_state,{$ODBC}]'),//seach id contains specific word
		);
		//call api method
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			if ($item["lastvalue"] == 0) {
				$countdb_online++;
			}
			else if ($item["lastvalue"] == 1) {
				$countdb_restoring++;
			}
			else if ($item["lastvalue"] == 2) {
				$countdb_recovering++;
			}
			else if ($item["lastvalue"] == 3) {
				$countdb_pending++;
			}
			else if ($item["lastvalue"] == 4) {
				$countdb_suspect++;
			}
			else if ($item["lastvalue"] == 6) {
				$countdb_offline++;
			}
			else {
				$countdb_others++;
			}

			$totaldb++;
		}

		//col classes
		$colclass = "col-md-3";
		$divider = 4;

		/*if ($totaldb < 2) {
			$colclass = "col-md-12";
			$divider = 1;
		}
		else if ($totaldb >= 2 and $totaldb < 6) {
			$colclass = "col-md-6";
			$divider = 2;
		}
		else if ($totaldb >= 6 and $totaldb < 8) {
			$colclass = "col-md-4";
			$divider = 3;
		}
		else {
			$colclass = "col-md-3";
			$divider = 4;
		}*/

		//calculate percentages
		$dbonline_percent = ($countdb_online / $totaldb) * 100;
		$dboffline_percent = ($countdb_offline / $totaldb) * 100;
		$dbpending_percent = ($countdb_pending / $totaldb) * 100;
		$dbrecovering_percent = ($countdb_recovering / $totaldb) * 100;
		$dbrestoring_percent = ($countdb_restoring / $totaldb) * 100;
		$dbsuspect_percent = ($countdb_suspect / $totaldb) * 100;
		$dbothers_percent = ($countdb_others / $totaldb) * 100;

		//progress bar classes
		//online
		if ($dbonline_percent < 1) {
			$countdb_online = 0;
			$dbonline_prog = "progress-bar progress-bar-default";
			$dbonline_label = "badge bg-default";
		}
		else if ($dbonline_percent >= 1 and $dbonline_percent < 30) {
			$dbonline_prog = "progress-bar progress-bar-danger";
			$dbonline_label = "badge bg-red";
		}
		else if ($dbonline_percent >= 30 and $dbonline_percent < 70) {
			$dbonline_prog = "progress-bar progress-bar-yellow";
			$dbonline_label = "badge bg-yellow";
		}
		else if ($dbonline_percent > 70) {
			$dbonline_prog = "progress-bar progress-bar-success";
			$dbonline_label = "badge bg-green";
		}

		//offline
		if ($dboffline_percent < 1) {
			$countdb_offline = 0;
			$dboffline_prog = "progress-bar progress-bar-default";
			$dboffline_label = "badge bg-default";
		}
		else if ($dboffline_percent >= 1 and $dboffline_percent < 30) {
			$dboffline_prog = "progress-bar progress-bar-danger";
			$dboffline_label = "badge bg-red";
		}
		else if ($dboffline_percent >= 30 and $dboffline_percent < 70) {
			$dboffline_prog = "progress-bar progress-bar-yellow";
			$dboffline_label = "badge bg-yellow";
		}
		else if ($dboffline_percent > 70) {
			$dboffline_prog = "progress-bar progress-bar-success";
			$dboffline_label = "badge bg-green";
		}

		//restoring
		if ($dbrestoring_percent < 1) {
			$countdb_restoring = 0;
			$dbrestoring_prog = "progress-bar progress-bar-default";
			$dbrestoring_label = "badge bg-default";
		}
		else if ($dbrestoring_percent >= 1 and $dbrestoring_percent < 30) {
			$dbrestoring_prog = "progress-bar progress-bar-danger";
			$dbrestoring_label = "badge bg-red";
		}
		else if ($dbrestoring_percent >= 30 and $dbrestoring_percent < 70) {
			$dbrestoring_prog = "progress-bar progress-bar-yellow";
			$dbrestoring_label = "badge bg-yellow";
		}
		else if ($dbrestoring_percent > 70) {
			$dbrestoring_prog = "progress-bar progress-bar-success";
			$dbrestoring_label = "badge bg-green";
		}

		//recovering
		if ($dbrecovering_percent < 1) {
			$countdb_recovering = 0;
			$dbrecovering_prog = "progress-bar progress-bar-default";
			$dbrecovering_label = "badge bg-default";
		}
		else if ($dbrecovering_percent >= 1 and $dbrecovering_percent < 30) {
			$dbrecovering_prog = "progress-bar progress-bar-danger";
			$dbrecovering_label = "badge bg-red";
		}
		else if ($dbrecovering_percent >= 30 and $dbrecovering_percent < 70) {
			$dbrecovering_prog = "progress-bar progress-bar-yellow";
			$dbrecovering_label = "badge bg-yellow";
		}
		else if ($dbrecovering_percent > 70) {
			$dbrecovering_prog = "progress-bar progress-bar-success";
			$dbrecovering_label = "badge bg-green";
		}

		//pending
		if ($dbpending_percent < 1) {
			$countdb_pending = 0;
			$dbpending_prog = "progress-bar progress-bar-default";
			$dbpending_label = "badge bg-default";
		}
		else if ($dbpending_percent >= 1 and $dbpending_percent < 30) {
			$dbpending_prog = "progress-bar progress-bar-danger";
			$dbpending_label = "badge bg-red";
		}
		else if ($dbpending_percent >= 30 and $dbpending_percent < 70) {
			$dbpending_prog = "progress-bar progress-bar-yellow";
			$dbpending_label = "badge bg-yellow";
		}
		else if ($dbpending_percent > 70) {
			$dbpending_prog = "progress-bar progress-bar-success";
			$dbpending_label = "badge bg-green";
		}

		//suspect
		if ($dbsuspect_percent < 1) {
			$countdb_suspect = 0;
			$dbsuspect_prog = "progress-bar progress-bar-default";
			$dbsuspect_label = "badge bg-default";
		}
		else if ($dbsuspect_percent >= 1 and $dbsuspect_percent < 30) {
			$dbsuspect_prog = "progress-bar progress-bar-danger";
			$dbsuspect_label = "badge bg-red";
		}
		else if ($dbsuspect_percent >= 30 and $dbsuspect_percent < 70) {
			$dbsuspect_prog = "progress-bar progress-bar-yellow";
			$dbsuspect_label = "badge bg-yellow";
		}
		else if ($dbsuspect_percent > 70) {
			$dbsuspect_prog = "progress-bar progress-bar-success";
			$dbsuspect_label = "badge bg-green";
		}

		//others
		if ($dbothers_percent < 1) {
			$countdb_others = 0;
			$dbothers_prog = "progress-bar progress-bar-default";
			$dbothers_label = "badge bg-default";
		}
		else if ($dbothers_percent >= 1 and $dbothers_percent < 30) {
			$dbothers_prog = "progress-bar progress-bar-danger";
			$dbothers_label = "badge bg-red";
		}
		else if ($dbothers_percent >= 30 and $dbothers_percent < 70) {
			$dbothers_prog = "progress-bar progress-bar-yellow";
			$dbothers_label = "badge bg-yellow";
		}
		else if ($dbothers_percent > 70) {
			$dbothers_prog = "progress-bar progress-bar-success";
			$dbothers_label = "badge bg-green";
		}
		
		//start print table
		if ($count % $divider == 0) {
			print '<div class="row">
				<div class="'.$colclass.'">
				<table class="table table-condensed">
				<caption align="center">'.$gethostname.'</caption>';
		}
		else {
			print '<div class="'.$colclass.'">
				<table class="table table-condensed">
				<caption>'.$gethostname.'</caption>';
		}
		
		print '<tr>
				<td style="width:40%">Number of Online Databases</td>
				<td style="width:50%">
					<div class="progress progress-sm">
						<div class="'.$dbonline_prog.'" style="width: '.$dbonline_percent.'%"></div>
					</div>
				</td>
				<td style="width:20%"><span class="'.$dbonline_label.'">'.$countdb_online.'</span></td>
				</tr>';
		
		print '<tr>
				<td>Number of Offline Databases</td>
				<td>
					<div class="progress progress-sm">
						<div class="'.$dboffline_prog.'" style="width: '.$dboffline_percent.'%"></div>
					</div>
				</td>
				<td><span class="'.$dboffline_label.'">'.$countdb_offline.'</span></td>
				</tr>';
		
		print '<tr>
				<td>Number of Pending Databases</td>
				<td>
					<div class="progress progress-sm">
						<div class="'.$dbpending_prog.'" style="width: '.$dbpending_percent.'%"></div>
					</div>
				</td>
				<td><span class="'.$dbpending_label.'">'.$countdb_pending.'</span></td>
				</tr>';

		print '<tr>
				<td>Number of Recovering Databases</td>
				<td>
					<div class="progress progress-sm">
						<div class="'.$dbrecovering_prog.'" style="width: '.$dbrecovering_percent.'%"></div>
					</div>
				</td>
				<td><span class="'.$dbrecovering_label.'">'.$countdb_recovering.'</span></td>
				</tr>';

		print '<tr>
				<td>Number of Restoring Databases</td>
				<td>
					<div class="progress progress-sm">
						<div class="'.$dbrestoring_prog.'" style="width: '.$dbrestoring_percent.'%"></div>
					</div>
				</td>
				<td><span class="'.$dbrestoring_label.'">'.$countdb_restoring.'</span></td>
				</tr>';

		print '<tr>
				<td>Number of Suspect Databases</td>
				<td>
					<div class="progress progress-sm">
						<div class="'.$dbsuspect_prog.'" style="width: '.$dbsuspect_percent.'%"></div>
					</div>
				</td>
				<td><span class="'.$dbsuspect_label.'">'.$countdb_suspect.'</span></td>
				</tr>';

		print '<tr>
				<td>Number of Others Databases</td>
				<td>
					<div class="progress progress-sm">
						<div class="'.$dbothers_prog.'" style="width: '.$dbothers_percent.'%"></div>
					</div>
				</td>
				<td><span class="'.$dbothers_label.'">'.$countdb_others.'</span></td>
				</tr>';

		if ($count % $divider == 0) {
			print '</table>
				</div>
				</div>';
		}
		else if ($count == $countHost) {
			print '</table>
				</div>
				</div>';
		}
		else {
			print '</table>
				</div>';
		}

		$count++;

		$countdb_online = 0;
		$countdb_offline = 0;
		$countdb_pending = 0;
		$countdb_recovering = 0;
		$countdb_restoring = 0;
		$countdb_suspect = 0;
		$count_others = 0;
		$totaldb = 0;
	}
	?>
		
	</table>
</body>
<script type="text/javascript">
	$(document).ready(function() {
    $('#dbstatus_table').DataTable();
} );
</script>

</html>