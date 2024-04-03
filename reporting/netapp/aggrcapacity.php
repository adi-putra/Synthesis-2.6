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
			<table id="aggrcaptable" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Aggregate Name</th>
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
                        $aggr_name = [];
						$params = array(
							"output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
							"hostids" => $hostID,
							"search" => array("name" => array("Aggr Total Capacity")),
							"searchByAny" => true
						);
						//call api problem.get only to get eventid
						$result = $zbx->call('item.get',$params);
						//sort array by lastvalue
                        usort($result, function($a, $b) {
                            return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
                        });

						foreach ($result as $item) {
                            if ($item["error"] == "") {
                                $item_name = substr($item["name"], 22);
                                $aggr_name[] = substr($item_name, 0, -1);
                            }
						}

                        //slice the array to only top 10
                        $aggr_name = array_slice($aggr_name, 0, 10);

                        // Loop through all nodes
                        for ($i=0; $i < count($aggr_name); $i++) {  
                            
                            // Total Capacity
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggr Total Capacity- [".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrtotal_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrtotal_value = "No data";
                            }

                            // Used Capacity
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggr Used Capacity- [".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    if (stripos($item["name"], "Forecast") !== false) {
                                        continue;
                                    }
                                    else {
                                        $aggrused_value = $item["lastvalue"];
                                    }
                                }
                            }
                            else {
                                $aggrused_value = "No data";
                            }

                            // Used Capacity %
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("Aggregate Size Used (%)[".$aggr_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $aggrusedper_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $aggrusedper_value = "No data";
                            }

                            // Mem
                            $value = $aggrusedper_value;
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
                            print "<td><a href='hostdetails_netapp.php?hostid=".$hostID."'>".$gethostname."</a>: ".$aggr_name[$i]."</td>";
                            print "<td>".formatBytes($aggrtotal_value)."</td>";
                            print "<td>".formatBytes($aggrused_value)."</td>";
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
    $('#aggrcaptable').DataTable({
        "order": [
            [4, "desc"]
        ],
        "paging": false,
        "searching": false,
        "info": false
    });
});

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