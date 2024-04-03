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
	<title></title>
</head>

<body>
	<table class="table table-bordered table-striped">
		<tr>
			<th>Sonicwall Max Connections</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "maximum connections") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			foreach ($result as $item) {
				$itemid = $item["itemid"];
			}

			if (isset($itemid) == false) {
				print "<td>No data</td>";
			} else {
				$params = array(
					"output" => array("lastvalue"),
					"itemids" => $itemid
				);

				//call api history.get with params
				$result = $zbx->call('item.get', $params);
				foreach ($result as $row) {
					$maxconn = $row["lastvalue"];
					print "<td>$maxconn</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>Sonicwall Model</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "model") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			foreach ($result as $item) {
				$itemid = $item["itemid"];
			}

			if (isset($itemid) == false) {
				print "<td>No data</td>";
			} else {
				$params = array(
					"output" => array("lastvalue"),
					"itemids" => $itemid
				);

				//call api history.get with params
				$result = $zbx->call('item.get', $params);
				foreach ($result as $row) {
					$model = $row["lastvalue"];
					print "<td>$model</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>Sonicwall Serial Number</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "serial number") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			foreach ($result as $item) {
				$itemid = $item["itemid"];
			}

			if (isset($itemid) == false) {
				print "<td>No data</td>";
			} else {
				$params = array(
					"output" => array("lastvalue"),
					"itemids" => $itemid
				);

				//call api history.get with params
				$result = $zbx->call('item.get', $params);
				foreach ($result as $row) {
					$serialnum = $row["lastvalue"];
					print "<td>$serialnum</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>Firewall Status</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "firewall status") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			foreach ($result as $item) {
				$itemid = $item["itemid"];
			}

			if (isset($itemid) == false) {
				print "<td>No data</td>";
			} else {
				$params = array(
					"output" => array("lastvalue"),
					"itemids" => $itemid
				);

				//call api history.get with params
				$result = $zbx->call('item.get', $params);
				foreach ($result as $row) {
					$fwstatus = $row["lastvalue"];
					print "<td>$fwstatus</td>";
				}
			}
			?>
		</tr>
	</table>
</body>

</html>