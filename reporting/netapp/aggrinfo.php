<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];

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
	<title>Windows Overview</title>
</head>

<body>
	<div class="row">
		<div class="col-md-12">
			<!-- CPU Util table -->
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Aggregate Name</th>
						<th>State</th>
                        <th>Owner (Node)</th>
                        <th>Raid Type</th>
                        <th>Type</th>
                        <th>Filesystem Status</th>
                        <th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($hostid as $hostID) {

						$params = array(
							"output" => array("name"),
							"hostids" => $hostID
						);
						//call api
						$result = $zbx->call('host.get', $params);
						foreach ($result as $row) {
							$gethostname = $row["name"];
						}

						// Get Aggr Name
                        $aggr_name = [];
						$params = array(
							"output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
							"hostids" => $hostID,
							"search" => array("name" => array("Aggregate Owners")),
							"searchByAny" => true
						);
						//call api problem.get only to get eventid
						$result = $zbx->call('item.get',$params);
						foreach ($result as $item) {
                            if ($item["error"] == "") {
                                $item_name = substr($item["name"], 17);
                                $aggr_name[] = substr($item_name, 0, -1);
                            }
						}

                        // Loop through all nodes
                        for ($i=0; $i < count($aggr_name); $i++) {  
                            
                            // State
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate State[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrstate_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrstate_value = "No data";
                            }

                            // Owners
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate Owners[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrowner_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrowner_value = "No data";
                            }

                            // Status
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate Status[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrstatus_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrstatus_value = "No data";
                            }

                            // Type
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate Type[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrtype_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrtype_value = "No data";
                            }

                            // Raid Type
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate Raidtype[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrraidtype_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrraidtype_value = "No data";
                            }

                            // FS Status
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate Filesystem Status[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrfsstatus_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrfsstatus_value = "No data";
                            }

                            // Mapping
                            //Aggr Type
                            if ($aggrtype_value == 0) {
                                $aggrtype_value = '<button class="btn btn-block btn-default">Unknown</button>';
                            }
                            else if ($aggrtype_value == 1) {
                                $aggrtype_value = '<button class="btn btn-block btn-default">Traditional</button>';
                            }
                            else if ($aggrtype_value == 2) {
                                $aggrtype_value = '<button class="btn btn-block btn-default">Aggregate</button>';
                            }
                            else if ($aggrtype_value == 3) {
                                $aggrtype_value = '<button class="btn btn-block btn-default">Striped</button>';
                            }

                            //FS Status
                            if ($aggrfsstatus_value == 1) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-warning">Unmounted</button>';
                            }
                            else if ($aggrfsstatus_value == 2) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-success">Mounted</button>';
                            }
                            else if ($aggrfsstatus_value == 3) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-warning">Frozen</button>';
                            }
                            else if ($aggrfsstatus_value == 4) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-danger">Unknown</button>';
                            }
                            else if ($aggrfsstatus_value == 5) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-info">Creating</button>';
                            }
                            else if ($aggrfsstatus_value == 6) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-info">Mounting</button>';
                            }
                            else if ($aggrfsstatus_value == 6) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-info">Unmounting</button>';
                            }
                            else if ($aggrfsstatus_value == 6) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-danger">No FS Info</button>';
                            }
                            else if ($aggrfsstatus_value == 6) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-info">Replaying</button>';
                            }
                            else if ($aggrfsstatus_value == 6) {
                                $aggrfsstatus_value = '<button class="btn btn-block btn-info">Replayed</button>';
                            }

                            //State
                            if ($aggrstate_value == "online") {
                                $aggrstate_value = '<button class="btn btn-block btn-success">Online</button>';
                            }
                            else if ($aggrstate_value == "offline") {
                                $aggrstate_value = '<button class="btn btn-block btn-danger">Offline</button>';
                            }
                            

                            print "<tr>";
                            print "<td><a href='hostdetails_netapp.php?hostid=".$hostID."'>".$gethostname."</a>: ".$aggr_name[$i]."</td>";
                            print "<td>".$aggrstatus_value."</td>";
                            print "<td>".$aggrowner_value."</td>";
                            print "<td>".$aggrraidtype_value."</td>";
                            print "<td>".$aggrtype_value."</td>";
                            print "<td>".$aggrfsstatus_value."</td>";
                            print "<td>".$aggrstate_value."</td>";
                            print "</tr>";
                        }
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
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