<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
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
	//if group id exist
	if (isset($hostid)) {
		//get all versions and count
		$sysver = array();
		$params = array(
			"output" => array("lastvalue"),
			"hostids" => $hostid, //find by hostid
			"search" => array("name" => array("Product Version")),
			"searchByAny" => true
		);

		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			$sysver1 = $item["lastvalue"];
			if ($sysver1 == "" || $sysver1 == "0") {
				$sysver[] = "No data";
			}
			else {
				$sysver[] = $sysver1;
			}
		}
		$vervalues = array_count_values($sysver); //group and count versions
	}
	else {
		//get all versions and count
		$sysver = array();
		$params = array(
			"output" => array("lastvalue"),
			"groupids" => $groupid, //find by groupid
			"search" => array("name" => array("Product Version")),
			"searchByAny" => true
		);

		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {
			$sysver1 = $item["lastvalue"];
			if ($sysver1 == "" || $sysver1 == "0") {
				$sysver[] = "No data";
			}
			else {
				$sysver[] = $sysver1;
			}
		}
		$vervalues = array_count_values($sysver); //group and count versions
	}

	//get groupname
	$params = array(
		"output" => array("name"),
		"groupids" => $groupid
	);

	$result = $zbx->call('hostgroup.get',$params);
	foreach ($result as $group) {
		$groupname = str_replace("Zabbix", "Synthesis", $group["name"]);
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
	<table class="table table-bordered">
		<tr>
			<th>Group Name</th>
			<td><?php echo $groupname; ?></td>
		</tr>
		<tr>
			<th>Total Hosts</th>
			<td><?php echo $hostcount; ?></td>
		</tr>
	</table>

	<table class="table table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th>Version Name</th>
				<th>Host</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$id = 1;

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
						"search" => array("name" => array("Product Version")),
						"searchByAny" => true
					);
				
					$result = $zbx->call('item.get',$params);
					foreach ($result as $item) {
						$lastvalue = $item["lastvalue"];
						if ($lastvalue == "" || $lastvalue == "0") {
							$lastvalue = "No data";
						}
						
						if ($lastvalue == $value) {
							$popover_cont .= $gethostname."<br>";
						}
						else {
							continue;
						}
					}
				}

				print "<tr>
						<td>$id</td>
						<td>$value</td>
						<td>$popover_cont</td>
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
countcheck = countcheck + 1;
if (countcheck == countcheck_total) {
    $("#reportready").html("Report is ready!");
    $('#chooseprint').show();
    $('#reportdiv').show();
}
else {
    $("#reportready").html("Generating report...(" + countcheck + "/" + countcheck_total + ")");
}
</script>