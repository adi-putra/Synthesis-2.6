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
			<table id="luncaptable" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>LUN Name</th>
						<th>Total Capacity</th>
                        <th>Available Capacity</th>
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

						// Get LUN Name
                        $lun_name = [];
						$params = array(
							"output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
							"hostids" => $hostID,
							"search" => array("name" => array("LUN Available Capacity")),
							"searchByAny" => true
						);
						//call api problem.get only to get eventid
						$result = $zbx->call('item.get',$params);
						foreach ($result as $item) {
                            if ($item["error"] == "") {
                                $item_name = substr($item["name"], 26);
                                $lun_name[] = substr($item_name, 0, -1);
                            }
						}

                        // Loop through all nodes
                        for ($i=0; $i < count($lun_name); $i++) {  
                            
                            // Total Capacity
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("LUN Total Capacity - [".$lun_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $luntotal_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $luntotal_value = "No data";
                            }

                            // Available Capacity
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("LUN Available Capacity - [".$lun_name[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    if ($item["lastvalue"] < 0) {
                                        $lunfree_value = substr($item["lastvalue"], 1);
                                        $lunfree_value = formatBytes($lunfree_value);
                                        $lunfree_value = "-".$lunfree_value;
                                    }
                                    else {
                                        $lunfree_value = formatBytes($item["lastvalue"]);
                                    }
                                }
                            }
                            else {
                                $lunfree_value = "No data";
                            }

                            // Used Capacity %
                            // $params = array(
                            //     "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                            //     "hostids" => $hostID,
                            //     "search" => array("name" => array("LUN Size Used (%)[".$lun_name[$i]."]")),
                            //     "searchByAny" => true
                            // );
                            // //call api problem.get only to get eventid
                            // $result = $zbx->call('item.get',$params);
                            // if (!empty($result)) {
                            //     foreach ($result as $item) {
                            //         $lunusedper_value = $item["lastvalue"];
                            //     }
                            // }
                            // else {
                            //     $lunusedper_value = "No data";
                            // }

                            // Mem
                            // $value = $lunusedper_value;
                            // $label = $value;
                            
                            // if ($value > 70) {
                            //     $status = "red";
                            //     $loader = "progress-bar progress-bar-danger";
                            // } else if ($value > 30 && $value <= 70) {
                            //     $status = "orange";
                            //     $loader = "progress-bar progress-bar-warning";
                            // } else if ($value > 0 && $value <= 30) {
                            //     $status = "seagreen";
                            //     $loader = "progress-bar progress-bar-success";
                            // } else if ($value <= 0.00) {
                            //     $status = "seagreen";
                            //     $loader = "progress-bar progress-bar-success";
                            //     $label = "0.00";
                            //     $value = 1;
                            // }

                            print "<tr>";
                            print "<td><a href='hostdetails_netapp.php?hostid=".$hostID."'>".$gethostname."</a>: ".$lun_name[$i]."</td>";
                            print "<td>".formatBytes($luntotal_value)."</td>";
                            print "<td>".$lunfree_value."</td>";
                            // print "<td>".formatBytes($lunfree_value)."</td>";
                            // print '<td>
                            // <div class="progress progress-lg progress-striped active">
                            //     <div class="' . $loader . '" style="width: ' . $value . '%;"></div>
                            // </div>
                            // </td>
                            // <td style="color: white; background-color: ' . $status . ';">' . $label . ' %</td>';
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
    $('#luncaptable').DataTable({
        "order": [
            [2, "asc"]
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