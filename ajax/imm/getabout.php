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
			"search" => array("name" => "hardware model name")
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
			"search" => array("name" => "hardware model name")
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
			<td><?php echo $groupname; ?></td>
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
						$gethostname = $host["name"];
					}

					//check host's version
					$params = array(
						"output" => array("lastvalue"),
						"hostids" => $hostID,
						"search" => array("name" => "hardware model name")
					);
				
					$result = $zbx->call('item.get',$params);
					foreach ($result as $item) {
						$lastvalue = $item["lastvalue"];
						
						if ($lastvalue == "" || $lastvalue == "0") {
							$lastvalue = "No data";
						}

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
						<td>$value &nbsp;<button type='button' class='btn btn-default btn-sm' data-toggle='popover' title='Hosts' data-content='".$popover_cont."'><i class='fa fa-info-circle'></i></button></td>
						<td>$count</td>
						</tr>";
				$id++;
				$popover_cont = "";
			}
			?>
		</tbody>
	</table>

	<script type="text/javascript">
      $(function () {
        $('#counttable').DataTable();
      });

	  $(function () {
		$('[data-toggle="popover"]').popover({
			html: true,
			trigger: "hover"
		})
	})
    </script>
</body>
</html>