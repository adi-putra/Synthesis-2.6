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
	<title>Network Interface - qnap</title>
</head>
<body>
	<table id="networkinterface_tbl" class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$params = array(
				"output" => array("name","key_","lastvalue"),
				"hostids" => $hostid,
				"search" => array("name" => "Sent")
			);
			//call api method
			$result = $zbx->call('item.get',$params);
			foreach($result as $row){

				print "<tr>";

				$net_name = substr($row["key_"], 11, -6);
				$net_val = $row["lastvalue"];

				// $net_name = substr($row["key_"], 10);

				if(!empty($net_name)){

					print "<td>".$net_name."</td>";

					if ($net_val > 0) {
						print '<td><button class="btn btn-success btn-block">Online</button></td>';
					}
					else {
						print '<td><button class="btn btn-danger btn-block">Offline</button></td>';
					}

				}else{

					print "<td>No Data</td>";
					print '<td><button class="btn btn-default btn-block">No Data</button></td>';
				}

				print "</tr>";
			}
			?>
		</tbody>
	</table>
</body>
<script>
		$(document).ready( function () {
			$('#networkinterface_tbl').DataTable({
				order: [[1, 'desc']],
			});
		} );
	</script>

</html>