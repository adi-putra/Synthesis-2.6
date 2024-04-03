<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];


//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid
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
	<meta charset="UTF-8">
	<title>Disk Total Size - qnap</title>
</head>

<body>
	<table class="table table-bordered table-striped" id="totaldisksize_table">
		<thead>
			<tr>
				<th>Name</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$params = array(
			"output" => array("name","lastvalue"),
			"hostids" => $hostid,
			"search" => array("name" => "Total size of")
		);
		//call api method
		$result = $zbx->call('item.get',$params);
		foreach($result as $row){
			print "<tr>";
			print "<th>".$row['name']."</th>";

			if(!empty($row['lastvalue'])){

				print "<td>".formatBytes($row['lastvalue'])."</td>";

			}else{

				print "<td>No Data</td>";
			}
			print "</tr>";
		}
		?>
		</tbody>
	</table>
</body>

</html>

<script>
	$(document).ready( function () {
		$('#totaldisksize_table').DataTable({
			order: [[1, 'desc']],
		});
	} );
</script>