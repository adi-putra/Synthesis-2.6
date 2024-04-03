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
	<table class="table table-bordered table-striped">
		<tr>
			<th>Edition</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "SQL Server : Edition") //seach id contains specific word
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
					$edition = $row["lastvalue"];
					print "<td>$edition</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>Product Level</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "SQL Server : Product Level") //seach id contains specific word
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
					$productlvl = $row["lastvalue"];
					print "<td>$productlvl</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>Version</th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "SQL Server : Version") //seach id contains specific word
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
					$version = $row["lastvalue"];
					print "<td>$version</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>Full Version Description</th>	
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "SQL Server : Full Version Description") //seach id contains specific word
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
					$version = $row["lastvalue"];
					print "<td>$version</td>";
				}
			}
			?>
		</tr>
	</table>
</body>

</html>