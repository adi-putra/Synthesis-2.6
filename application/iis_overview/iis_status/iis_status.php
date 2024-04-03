<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, and %i minutes');
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
</head>

<body>
	<table class="table table-bordered table-hover">
		<tr>
			<th>ID</th>
			<th>Host</th>
			<th>IIS: Uptime</th>
			<th>IIS: 80 port ping</th>	
			<th>IIS: Windows Process Activation Service (WAS) state</th>
			<th>IIS: World Wide Web Publishing Service (W3SVC) state</th>
		</tr>
		<?php
		$id = 1;
		foreach ($hostid as $hostID) {

			print "<tr>";

			print "<td>$id</td>";

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

			print "<td>$gethostname</td>";

			//get iis portping
			$params = array(
			"output" => array("itemid", "name", "lastvalue"),
			"hostids" => $hostID,
			"search" => array("name" => 'IIS: Uptime'),//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			foreach ($result as $item) {
				$iis_uptime = $item["lastvalue"];

				if ($iis_uptime == "") {
					print '<td>No data</td>';
				}
				else {
					print '<td>'.secondsToTime($iis_uptime).'</td>';
				}
			}

			//get iis portping
			$params = array(
			"output" => array("itemid", "name", "lastvalue"),
			"hostids" => $hostID,
			"search" => array("name" => 'IIS: {$IIS.PORT} port ping'),//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			foreach ($result as $item) {
				$iis_portping = $item["lastvalue"];

				if ($iis_portping == 1) {
					print '<td><button class="btn btn-block btn-success">Up</button></td>';
				}
				else {
					print '<td><button class="btn btn-block btn-danger">Down</button></td>';
				}
			}

			//get IIS: Windows Process Activation Service (WAS) state
			$params = array(
			"output" => array("itemid", "name", "lastvalue"),
			"hostids" => $hostID,
			"search" => array("key_" => 'service_state[WAS]'),//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			foreach ($result as $item) {
				$iis_wasstate = $item["lastvalue"];

				if ($iis_wasstate == 0) {
					print '<td><button class="btn btn-block btn-success">Running</button></td>';
				}
				else if ($iis_wasstate == 1) {
					print '<td><button class="btn btn-block btn-danger">Not Running</button></td>';
				}
				else {
					print '<td><button class="btn btn-block btn-info">No data</button></td>';
				}
			}

			//get IIS: World Wide Web Publishing Service (W3SVC) state
			$params = array(
			"output" => array("itemid", "name", "lastvalue"),
			"hostids" => $hostID,
			"search" => array("key_" => 'service_state[W3SVC]'),//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			foreach ($result as $item) {
				$iis_w3svc = $item["lastvalue"];

				if ($iis_w3svc == 0) {
					print '<td><button class="btn btn-block btn-success">Running</button></td>';
				}
				else if ($iis_w3svc == 1) {
					print '<td><button class="btn btn-block btn-danger">Not Running</button></td>';
				}
				else {
					print '<td><button class="btn btn-block btn-info">No data</button></td>';
				}
			}

			print "</tr>";

			$id++;
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