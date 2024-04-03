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
			<th><?php print $hostname ?></th>
			<?php
			$params = array(
				"output" => array("itemid"),
				"hostids" => $hostid,
				"search" => array("name" => "Firmware Version") //seach id contains specific word
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
					$sysinfo = $row["lastvalue"];
					print "<td>$sysinfo</td>";
				}
			}
			?>
		</tr>
		<tr>
			<th>IP</th>
			<td>
				<?php
				//get ip
				$params = array(
					"output" => array("hostid"),
					"hostids" => $hostid,
					"selectInterfaces" => array("ip")
				);
				//call api method
				$result = $zbx->call('host.get', $params);
				foreach ($result as $host) {
					foreach ($host["interfaces"] as $interface) {
						$ip = $interface["ip"];
						$ipArr[] = $ip;
					}
					// print_r(array_unique($ipArr));
					$ipArr = array_unique($ipArr);
					foreach ($ipArr as $ips){
						print "$ips <br/>";
					}
				}
				?>
			</td>
		</tr>
		<tr>
			<th>Group</th>
			<td>
				<?php
				//get host group name
				$params = array(
					"output" => array("hostid"),
					"hostids" => $hostid,
					"selectGroups" => array("name")
				);
				//call api method
				$result = $zbx->call('host.get', $params);
				foreach ($result as $host) {
					foreach ($host["groups"] as $group) {
						$groupname = $group["name"];
						print "$groupname <br>";
					}
				}
				?>
			</td>
		</tr>
		<tr>
			<th>Interfaces</th>
			<?php
			$params = array(
				"output" => array("itemid", "name", "key_", "lastvalue"),
				"hostids" => $hostid,
				"search" => array("name" => "Incoming traffic") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);



			if (!empty($result)) {
				$count = 1;
				foreach ($result as $item) {
					$interface = substr($item["key_"], 11);
					$interface = substr($interface, 1, -1);
					$itemvalue = $item["lastvalue"];
					if ($itemvalue > 0) {
					  $interfaceStatus = "<td style='color: green;'>Online</td>";
					}
					else {
					  $interfaceStatus = "<td style='color: red;'>Offline</td>";
					}
					print "<td>$count. $interface</td>";
					print $interfaceStatus;
					print "</tr>";
					print "<tr><td></td>";
					$count++;
				}
				
			} else {
				print "<td>No data</td>";
			}

			?>
		</tr>
	</table>
</body>

</html>