<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$groupid = $_GET["groupid"];
$hostid = $_GET["hostid"];

if (empty($hostid)) {
    $params = array(
        "output" => array("hostid", "name"),
        "groupids" => $groupid
    );

    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $hostid[] = $host["hostid"];
    }
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
			<table id="cputable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Host Name</th>
						<th>CPU Usage (%)</th>
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
				
						//call api method
						$result = $zbx->call('host.get', $params);
				
						foreach ($result as $host) {
							// $gethostname = $host["name"];
							$gethostname = str_replace("Zabbix","Synthesis",$host["name"]);
						}
					
							$params = array(
							"output" => array("itemid", "name", "error", "lastvalue"),
							"hostids" => $hostID,
							"search" => array("name" => "cpu usage in percents"), //seach id contains specific word
							);
				
							//call api method
							$result = $zbx->call('item.get', $params);
				
							// echo "<pre>";
							// print_r($result);
							// echo "</pre>";
				
							foreach ($result as $item) {
								//$chart_title = $item["name"];
								$itemid[] = array(
									"id" => $item["itemid"], 
									"hostid" => $hostID,
									"name" => $item["name"],
									"hostname" => $gethostname,
									"lastvalue" => $item["lastvalue"]
								);
							}
					}

					//sort array by lastvalue
					usort($itemid, function($a, $b) {
						return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
					});
				
					//slice the array to only top 5
					$itemid = array_slice($itemid, 0, 10);

					foreach ($itemid as $item) {
						
						$usedCPU = number_format((float)$item["lastvalue"], 2, '.', '');
						$label = $usedCPU;

						if ($usedCPU > 70) {
							$status = "red";
							$loader = "progress-bar progress-bar-danger";
						} else if ($usedCPU > 30 && $usedCPU <= 70) {
							$status = "orange";
							$loader = "progress-bar progress-bar-warning";
						} else if ($usedCPU > 0 && $usedCPU <= 30) {
							$status = "seagreen";
							$loader = "progress-bar progress-bar-success";
						} else if ($usedCPU <= 0.00) {
							$status = "seagreen";
							$loader = "progress-bar progress-bar-success";
							$label = "0.00";
							$usedCPU = 1;
						}
						

						print '<tr>
                    			<td><a href="hostdetails_vm.php?hostid=' . $item["hostid"] . '#performance" target="_blank">' . $item["hostname"] . '</a></td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="' . $loader . '" style="width: ' . $usedCPU . '%;"></div>
	                                  </div>
	                                </td>
	                                <td style="color: white; background-color: ' . $status . ';">' . $label . ' %</td>
	                                </tr>';
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
		$('#cputable').DataTable({
			"order": [
				[2, "desc"]
			],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>