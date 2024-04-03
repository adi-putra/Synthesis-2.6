<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
);
//call api method
$result = $zbx->call('host.get', $params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2)
{
	$units = array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	// Uncomment one of the following alternatives
	$bytes /= pow(1024, $pow);
	// $bytes /= (1 << (10 * $pow)); 

	return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title></title>
</head>

<body>
	<table id="isplinks_table" class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Last Check</th>
				<th style="text-align: center;">Status</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$id = 1;
			$params = array(
				"output" => array("name", "lastvalue", "lastclock"),
				"hostids" => $hostid,
				"search" => array("key_" => "icmpping") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			foreach ($result as $item) {
				$itemname = $item["name"];
				$itemstatus = $item["lastvalue"];
				$lastclock = date("d-m-Y h:i:s A", $item["lastclock"]);
				print "<tr>
	          	<td>$id</td>
	          	<td>$itemname</td>
	          	<td>$lastclock</td>";

				if ($itemstatus == 0) {
					print "<td><button class='btn btn-block btn-success btn-sm'>Running</button></td></tr>";
				} else {
					print "<td><button class='btn btn-block btn-danger btn-sm'>Stopped</button></td></tr>";
				}
				$id++;
			}
			?>
		</tbody>
	</table>
</body>

</html>

<!-- page script -->
<script type="text/javascript">
  $(function () {
    $("#isplinks_table").DataTable();
  });
</script>