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
	<title>System Info - Linux</title>
</head>

<body>
	<table class="table table-bordered table-striped">
		<tr>
			<th>Hostname</th>
			<td><?php print $hostname ?></td>
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
			<th>Version</th>
			<?php
			$params = array(
				"output" => array("itemid", "lastvalue"),
				"hostids" => $hostid,
				"search" => array("key_" => array("system.sw.os")), //seach id contains specific word
				"searchByAny" => true
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			if (empty($result)) {
				print "<td>No data</td>";
			}
			else {
				foreach ($result as $item) {
					$start = strpos($item["lastvalue"], ".el") + 1; // Find the position of ".el" and add 1 to include the "el" substring
					$end = strpos($item["lastvalue"], " ", $start); // Find the first whitespace character after the ".el" substring
					$version = substr($item["lastvalue"], $start, $end - $start); // Extract the substring between the start and end positions
				}

				print "<td>$version</td>";
			}
			?>
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
			<th>Network Card</th>
			<?php
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostid,
				"search" => array("name" => "bits received") //seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get', $params);
			if (!empty($result)) {
				print "<td>";
				$count = 1;
				foreach ($result as $item) {

					//get name
					$start = stripos($item["name"], "interface") + strlen("interface "); // Find the position of ".el" and add 1 to include the "el" substring
					$end = stripos($item["name"], ":", $start); // Find the first whitespace character after the ".el" substring
					$netcard = substr($item["name"], $start, $end - $start); // Extract the substring between the start and end positions
					
					//get status
					if ($item["lastvalue"] == "" || $item["lastvalue"] <= 0) {
						$status = "text-danger";
					}
					else {
						$status = "text-success";
					}
					
					print "<p class='".$status."'>$count. $netcard</p>";

					$count++;
				}
				print "</td>";
			} else {
				print "<td>No data</td>";
			}

			?>
		</tr>
	</table>
</body>

</html>