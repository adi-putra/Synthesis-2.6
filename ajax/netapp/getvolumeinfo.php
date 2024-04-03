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
			<table id="volinfotable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Volume Name</th>
						<th>Aggregate</th>
                        <th>Language</th>
                        <th>VServer</th>
                        <th>iNodes usage</th>
                        <th>State</th>
                        <th>Type</th>
                        <th>Space Guarantee</th>
                        <th>Space Guarantee Enabled</th>
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
							"search" => array("key_" => array("volAggrName")),
							"searchByAny" => true
						);
						//call api problem.get only to get eventid
						$result = $zbx->call('item.get',$params);
						foreach ($result as $item) {
                            if ($item["error"] == "") {

                                $item_key = substr($item["key_"], 12);
                                $vol_key[] = substr($item_key, 0, -1);

                                $item_name = substr($item["name"], 7);
                                $vol_name[] = substr($item_name, 0, -11);
                            }
						}

                        // Loop through all nodes
                        for ($i=0; $i < count($vol_name); $i++) { 

                            // Aggregate
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volAggrName[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $volaggr_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $volaggr_value = "No data";
                            }

                            // Language
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volLanguage[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $vollang_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $vollang_value = "No data";
                            }

                            // iNodes
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("voliNodes[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $volinode_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $volinode_value = "No data";
                            }

                            
                            // Vserver
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volVserver[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $volvserver_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $volvserver_value = "No data";
                            }

                            // State
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volState[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $volstate_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $volstate_value = "No data";
                            }

                            // Type
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volType[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $voltype_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $voltype_value = "No data";
                            }

                            // Space Guarantee
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volSpaceGuarantee[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $volspaceg_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $volspaceg_value = "No data";
                            }


                            // Space Guarantee Enabled
                            $params = array(
                                "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                                "hostids" => $hostID,
                                "search" => array("key_" => array("volSpaceGuaranteeEnabled[".$vol_key[$i]."]")),
                                "searchByAny" => true
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('item.get',$params);
                            if (!empty($result)) {
                                foreach ($result as $item) {
                                    $volspacege_value = $item["lastvalue"];
                                }
                            }
                            else {
                                $volspacege_value = "No data";
                            }

                            // Mapping
                            // Volume Type
                            if ($voltype_value == 1) {
                                $voltype_value = '<button class="btn btn-block btn-info">Traditional</button>';
                            }
                            else if ($voltype_value == 2) {
                                $voltype_value = '<button class="btn btn-block btn-info">Flexible</button>';
                            }
                            else if ($voltype_value == 3) {
                                $voltype_value = '<button class="btn btn-block btn-info">Striped</button>';
                            }
                            else if ($voltype_value == 4) {
                                $voltype_value = '<button class="btn btn-block btn-info">Asis</button>';
                            }
                            else if ($voltype_value == 5) {
                                $voltype_value = '<button class="btn btn-block btn-info">Snaplock</button>';
                            }

                            // Volume Space Guarantee
                            if ($volspaceg_value == 0) {
                                $volspaceg_value = '<button class="btn btn-block btn-danger">None</button>';
                            }
                            else if ($volspaceg_value == 1) {
                                $volspaceg_value = '<button class="btn btn-block btn-info">File</button>';
                            }
                            else if ($volspaceg_value == 2) {
                                $volspaceg_value = '<button class="btn btn-block btn-info">Volume</button>';
                            }

                            // Volume Space Guarantee Enabled
                            if ($volspacege_value == 1) {
                                $volspacege_value = '<button class="btn btn-block btn-danger">False</button>';
                            }
                            else if ($volspacege_value == 2) {
                                $volspacege_value = '<button class="btn btn-block btn-success">True</button>';
                            }

                            print "<tr>";
                            print "<td><a href='hostdetails_netapp.php?hostid=".$hostID."'>".$gethostname."</a>: ".$vol_name[$i]."</td>";
                            print "<td>".$volaggr_value."</td>";
                            print "<td>".$vollang_value."</td>";
                            print "<td>".$volvserver_value."</td>";
                            print "<td>".$volinode_value."</td>";
                            print "<td>".$volstate_value."</td>";
                            print "<td>".$voltype_value."</td>";
                            print "<td>".$volspaceg_value."</td>";
                            print "<td>".$volspacege_value."</td>";
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
		$('#volinfotable').DataTable({
			// "order": [
			// 	[4, "desc"]
			// ],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>