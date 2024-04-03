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
						<th>VIF Name</th>
						<th>Is Home</th>
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
                        $vif_name = [];
						$params = array(
							"output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
							"hostids" => $hostID,
							"search" => array("name" => array("is Home")),
							"searchByAny" => true
						);
						//call api problem.get only to get eventid
						$result = $zbx->call('item.get',$params);
						foreach ($result as $item) {
                            if ($item["error"] == "") {
                                $item_name = substr($item["name"], 4);
                                $vif_name[] = substr($item_name, 0, -9);
                            }
						}

                        // Loop through all nodes
                        for ($i=0; $i < count($vif_name); $i++) { 
                            
                            // Aggregate
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("name" => array("VIF[".$vif_name[$i]."] is Home")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $vifishome_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $vifishome_value = "No data";
                            }

                            // Mapping
                            // Volume Type
                            if ($vifishome_value == 1) {
                                $vifishome_value = '<button class="btn btn-block btn-success">True</button>';
                            }
                            else if ($vifishome_value <> 1) {
                                $vifishome_value = '<button class="btn btn-block btn-danger">False</button>';
                            }

                            print "<tr>";
                            print "<td><a href='hostdetails_netapp.php?hostid=".$hostID."'>".$gethostname."</a>: ".$vif_name[$i]."</td>";
                            print "<td>".$vifishome_value."</td>";
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