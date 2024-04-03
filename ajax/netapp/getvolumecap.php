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
			<table id="volcaptable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Volume Name</th>
						<th>Total Capacity</th>
                        <th>Used Capacity</th>
                        <th>Used Capacity (%)</th>
                        <th>Value</th>
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

						// Get Nodes Name
                        $vol_name = [];
                        $vol_key = [];
						$params = array(
							"output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
							"hostids" => $hostID,
							"search" => array("key_" => array("vol_totalcapacity[")),
							"searchByAny" => true
						);
						//call api problem.get only to get eventid
						$result = $zbx->call('item.get',$params);
						foreach ($result as $item) {
                            if ($item["error"] == "") {

                                $item_key = substr($item["key_"], 18);
                                $vol_key[] = substr($item_key, 0, -1);

                                $comma_name = stripos($item["key_"], ",") + 1;
                                $item_name = substr($item["key_"], $comma_name);
                                $vol_name[] = substr($item_name, 0, -1);
                            }
						}

                        // Loop through all nodes
                        for ($i=0; $i < count($vol_key); $i++) {
                            
                            // Total Capacity
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("vol_totalcapacity[".$vol_key[$i])),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            foreach ($result as $item) {
                                $voltotal_value = $item["lastvalue"];
                            }

                            // Used Capacity
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("vol_usedcapacity[".$vol_key[$i])),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            foreach ($result as $item) {
                                $volused_value = $item["lastvalue"];
                            }

                            // Used Capacity %
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("vol_usedpercent[".$vol_key[$i])),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            foreach ($result as $item) {
                                $volusedper_value = $item["lastvalue"];
                            }

                            // Mem
                            $value = $volusedper_value;
                            $label = $value;
                            
                            if ($value > 70) {
                                $status = "red";
                                $loader = "progress-bar progress-bar-danger";
                            } else if ($value > 30 && $value <= 70) {
                                $status = "orange";
                                $loader = "progress-bar progress-bar-warning";
                            } else if ($value > 0 && $value <= 30) {
                                $status = "seagreen";
                                $loader = "progress-bar progress-bar-success";
                            } else if ($value <= 0.00) {
                                $status = "seagreen";
                                $loader = "progress-bar progress-bar-success";
                                $label = "0.00";
                                $value = 1;
                            }

                            print "<tr>";
                            print "<td><a href='hostdetails_netapp.php?hostid=".$hostID."'>".$gethostname."</a>: ".$vol_name[$i]."</td>";
                            print "<td>".formatBytes($voltotal_value)."</td>";
                            print "<td>".formatBytes($volused_value)."</td>";
                            print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $loader . '" style="width: ' . $value . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $status . ';">' . $label . ' %</td>';
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
	$(function() {
		$('#volcaptable').DataTable({
			"order": [
				[4, "desc"]
			],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>