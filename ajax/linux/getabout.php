<?php
include $_SERVER['DOCUMENT_ROOT'].'/synthesis3/docs/_includes/session.php';
//$hostid = $_GET["hostid"] ?? array("10361", "10324");
$groupid = $_GET["groupid"];
$hostid = $_GET["hostid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title></title>
</head>
<body>
	<?php
	//get all versions and count
	$sysver = [];

	foreach ($hostid as $hostID) {
		$params = array(
			"output" => array("lastvalue"),
			"hostids" => $hostID, //find by groupid
			"search" => array("key_" => array("system.sw.os")),
			"searchByAny" => true
		);
		$result = $zbx->call('item.get',$params);
		if (empty($result)) {
			$sysver[] = "No data";
		}
		else {
			foreach ($result as $item) {

				$start = strpos($item["lastvalue"], ".el") + 1; // Find the position of ".el" and add 1 to include the "el" substring
				$end = strpos($item["lastvalue"], " ", $start); // Find the first whitespace character after the ".el" substring
				$sysver1 = substr($item["lastvalue"], $start, $end - $start); // Extract the substring between the start and end positions

				// echo $sysver1; // Output: el8.x86_64

				if ($sysver1 == "" || $sysver1 == "0") {
					$sysver[] = "No data";
				}
				else {
					$sysver[] = $sysver1;
				}
			}
		}
	}

	$vervalues = array_count_values($sysver); //group and count versions

	//get groupname
	$params = array(
		"output" => array("name"),
		"groupids" => $groupid
	);

	$result = $zbx->call('hostgroup.get',$params);
	foreach ($result as $group) {
		$groupname = $group["name"];
	}

	//count total hosts in group
	$params = array(
		"output" => array("hostid"),
		"groupids" => $groupid,
		"countOutput" => true
	);

	$hostcount = $zbx->call('host.get',$params);
	?>

	<!-- About table -->
	<table class="table table-bordered table-striped">
		<tr>
			<th>Group Name</th>
			<td><?php echo str_replace("Zabbix", "Synthesis", $groupname); ?></td>
		</tr>
		<tr>
			<th>Total Hosts</th>
			<td><?php echo $hostcount; ?></td>
		</tr>
	</table>

	<table id="counttable" class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th>Version Name</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$id = 1;
			$popover_cont = "<table>";

			foreach ($vervalues as $value => $count) {
				foreach ($hostid as $hostID) {
					//get hostname
					$params = array(
						"output" => array("name"),
						"hostids" => $hostID
					);
				
					$result = $zbx->call('host.get',$params);
					foreach ($result as $host) {
						$gethostname = str_replace("Zabbix", "Synthesis", $host["name"]);
					}

					//check host's version
					$params = array(
						"output" => array("lastvalue"),
						"hostids" => $hostID,
						"search" => array("key_" => array("system.sw.os")),
						"searchByAny" => true
					);
				
					$result = $zbx->call('item.get',$params);
					if (empty($result)) {
						$lastvalue = "No data";

						//check if host belong to the version
						if ($lastvalue == $value) {
							$popover_cont .= "<tr>
												<td>$gethostname</td>
												</tr>";
						}
						else {
							continue;
						}
					}
					foreach ($result as $item) {

						$start = strpos($item["lastvalue"], ".el") + 1; // Find the position of ".el" and add 1 to include the "el" substring
						$end = strpos($item["lastvalue"], " ", $start); // Find the first whitespace character after the ".el" substring
						$lastvalue = substr($item["lastvalue"], $start, $end - $start); // Extract the substring between the start and end positions

						// echo $sysver1; // Output: el8.x86_64
						
						if ($lastvalue == "" || $lastvalue == "0") {
							$lastvalue = "No data";
						}

						//check if host belong to the version
						if ($lastvalue == $value) {
							$popover_cont .= "<tr>
												<td>$gethostname</td>
												</tr>";
						}
						else {
							continue;
						}
					}
				}

				$popover_cont .= "</table>";

				print "<tr>
						<td>$id</td>
						<td>$value &nbsp;<button type='button' class='btn btn-default btn-sm' data-toggle='popover' data-content='".$popover_cont."'><i class='fa fa-info-circle'></i></button></td>
						<td>$count</td>
						</tr>";
				$id++;
				$popover_cont = "";
			}
			?>
		</tbody>
	</table>
</body>
</html>

<script>
//table settings
var counttable = $('#counttable').DataTable();

//popover enabled
counttable.$('[data-toggle="popover"]').popover({
    trigger: 'hover',
    html: true,
    animation: false
})  
</script>