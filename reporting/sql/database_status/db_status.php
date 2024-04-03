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
	<table id="dbstatus_table" class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Host</th>
				<th>Online</th>
				<th>Offline</th>
				<th>Pending</th>
				<th>Restoring</th>
				<th>Recovering</th>
				<th>Suspect</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
	
	<?php
	foreach ($hostid as $hostID) {

		print "<tr>";

		$params = array(
			"output" => array("name"),
			"hostids" => $hostID
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('host.get',$params);
		foreach ($result as $host) {
			print "<td>".$host["name"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Online Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Offline Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Pending Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Restoring Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Recovering Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Suspect Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		$params = array(
			"output" => array("lastvalue", "name", "error"),
			"hostids" => $hostID,
			"search" => array("name" => "Number of Total Databases")
		);
		//call api problem.get only to get eventid
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			print "<td>".$item["lastvalue"]."</td>";
		}

		print "</tr>";
	}
	?>

		</tbody>
	</table>
</body>
<script type="text/javascript">
	/*$(document).ready(function() {
		$('#dbstatus_table').DataTable();
	} );*/
</script>

<script>
	

	countcheck = countcheck + 1;
    if (countcheck == 13) {
        $("#reportready").html("Report is ready!");
    }
	else {
        $("#reportready").html("Generating report...(" + countcheck + "/13)");
    }
</script>

</html>