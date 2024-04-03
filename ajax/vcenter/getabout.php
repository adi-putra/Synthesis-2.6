<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//$hostid = $_GET["hostid"] ?? array("10361", "10324");

$groupid = $_GET["groupid"];
$hostid = $_GET["hostid"];

if (empty($hostid)) {
    $params = array(
        "output" => array("hostid", "name"),
        "groupids" => $hostID
    );

    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $hostid[] = $host["hostid"];
    }
}

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

	if (!empty($hostid)) {
		foreach ($hostid as $hostID) {
			$params = array(
				"output" => array("lastvalue"),
				"hostids" => $hostID, //find by groupid
				"search" => array("name" => array("version")),
				"searchByAny" => true
			);
			$result = $zbx->call('item.get',$params);
			if (empty($result)) {
				$sysver[] = "No data";
			}
			else {
				foreach ($result as $item) {
					$sysver1 = $item["lastvalue"];
					if ($sysver1 == "" || $sysver1 == "0") {
						$sysver[] = "No data";
					}
					else {
						$sysver[] = $sysver1;
						// $sysver[] = substr($sysver1, strpos($sysver1, "Microsoft"));
					}
				}
			}
		}

		$vervalues = array_count_values($sysver); //group and count versions
	}

	if (!empty($groupid)) {
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
	}
	?>
	
	<!-- About table -->
	<table class="table table-bordered table-striped">
		<tr>
			<th>Group Name</th>
			<td>
				<?php 
					if (empty($groupname)) {
						echo "No data";
					}
					else {
						echo str_replace("Zabbix","Synthesis",$groupname); 
					}
				?>
			</td>
			
		</tr>
		<tr>
			<th>Total Hosts</th>
			<td>
			<?php 
				if (empty($hostcount)) {
					echo "No data";
				}
				else {
					echo $hostcount;
				}
			?>
			</td>
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
					if (empty($result)) {
						$gethostname = "No data";
					}
					else {
						foreach ($result as $host) {
							$gethostname = str_replace("Zabbix", "Synthesis", $host["name"]);
						}
					}

					//check host's version
					$params = array(
						"output" => array("lastvalue"),
						"hostids" => $hostID,
						"search" => array("name" => array("version")),
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
					else {
						foreach ($result as $item) {
							$lastvalue = $item["lastvalue"];
							// $lastvalue = substr($lastvalue, strpos($lastvalue, "Microsoft"));
							
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