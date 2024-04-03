<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>ESX Overview</title>
</head>

<body>
	<div class="row">
		<div class="col-md-12">
			<!-- Used Mem table -->
			<table id="memtable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Host Name</th>
						<th>Used Memory (%)</th>
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
							$hostname = $row["name"];
						}

						//get used memory
						$params = array(
							"output" => array("itemid", "lastvalue"),
							"hostids" => $hostID,
							"search" => array("name" => "used memory") //seach id contains specific word
						);
						//call api method
						$result = $zbx->call('item.get', $params);
						foreach ($result as $item) {
							$itemid = $item["itemid"];
							$usedMem = $item["lastvalue"];
						}

						//get total memory
						$params = array(
							"output" => array("itemid", "lastvalue"),
							"hostids" => $hostID,
							"search" => array("name" => "total memory") //seach id contains specific word
						);
						//call api method
						$result = $zbx->call('item.get', $params);
						foreach ($result as $item) {
							$itemid = $item["itemid"];
							$totalMem = $item["lastvalue"];
						}

						//calculate used mem percent
						$usedMemPercent = ($usedMem/$totalMem) * 100;
						$usedMemPercent =  number_format((float)$usedMemPercent, 2, '.', '');

						$label = $usedMemPercent;

						//echo $usedMemPercent;

						if ($usedMemPercent > 70) {
							$status = "red";
							$loader = "progress-bar progress-bar-danger";
						} else if ($usedMemPercent > 30 && $usedMemPercent <= 70) {
							$status = "orange";
							$loader = "progress-bar progress-bar-warning";
						} else if ($usedMemPercent > 0 && $usedMemPercent <= 30) {
							$status = "seagreen";
							$loader = "progress-bar progress-bar-success";
						} else if ($usedMemPercent <= 0.00) {
							$status = "seagreen";
							$loader = "progress-bar progress-bar-success";
							$label = "0.00";
						}

						print ' <tr>
                    			   <td><a href="hostdetails_esx.php?hostid=' . $hostID . '#performance" target="_blank">' . $hostname . '</a></td>
	                               <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="' . $loader . '" style="width: ' . $usedMemPercent . '%;"></div>
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
		$('#memtable').DataTable({
			"order": [
				[2, "desc"]
			],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>