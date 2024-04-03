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
			<th>Total Jobs</th>
			<th>Active Jobs</th>
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
			"search" => array("key_" => 'vbr[JobsCount]'),//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			if (empty($result)) {
				print '<td><button class="btn btn-block btn-default">No data</button></td>';
			}
			else {
				foreach ($result as $item) {
					$jobs_total = $item["lastvalue"];
	
					if ($jobs_total == "") {
						print '<td><button class="btn btn-block btn-default">No data</button></td>';
					}
					else if ($jobs_total == 0) {
						print '<td><button class="btn btn-block btn-danger">'.$jobs_total.'</button></td>';
					}
					else {
						print '<td><button class="btn btn-block btn-success">'.$jobs_total.'</button></td>';
					}
				}
			}

			//get iis portping
			$params = array(
			"output" => array("itemid", "name", "lastvalue"),
			"hostids" => $hostID,
			"search" => array("key_" => 'vbr["RunningJob"]'),//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			if (empty($result)) {
				print '<td><button class="btn btn-block btn-default">No data</button></td>';
			}
			else {
				foreach ($result as $item) {
					$jobs_active = $item["lastvalue"];
	
					if ($jobs_active == "") {
						print '<td><button class="btn btn-block btn-default">No data</button></td>';
					}
					else if ($jobs_active == 0) {
						print '<td><button class="btn btn-block btn-danger">'.$jobs_active.'</button></td>';
					}
					else {
						print '<td><button class="btn btn-block btn-success">'.$jobs_active.'</button></td>';
					}
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