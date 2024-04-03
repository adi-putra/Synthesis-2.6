<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];
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
			<!-- Used Mem table -->
			<table id="esximemtable" class="table table-bordered table-striped">
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
							"search" => array("key_" => "vmwMemUsed"), //seach id contains specific word
                            "limit" => 1
                        );
						//call api method
						$result = $zbx->call('item.get', $params);
						foreach ($result as $item) {
							$itemid = $item["itemid"];
							$usedESXIMem = number_format((float)$item["lastvalue"], 2, '.', '');
						}

						$label = $usedESXIMem;

						//echo $usedMemPercent;

						if ($usedESXIMem > 70) {
							$status = "red";
							$loader = "progress-bar progress-bar-danger";
						} else if ($usedESXIMem > 30 && $usedESXIMem <= 70) {
							$status = "orange";
							$loader = "progress-bar progress-bar-warning";
						} else if ($usedESXIMem > 0 && $usedESXIMem <= 30) {
							$status = "seagreen";
							$loader = "progress-bar progress-bar-success";
						} else if ($usedESXIMem <= 0.00) {
							$status = "seagreen";
							$loader = "progress-bar progress-bar-success";
							$label = "0.00";
						}

						print '<tr>
                    			<td><a href="hostdetails_esx.php?hostid=' . $hostID . '#performance" target="_blank">' . $hostname . '</a></td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="' . $loader . '" style="width: ' . $usedESXIMem . '%;"></div>
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
		$('#esximemtable').DataTable({
			"order": [
				[2, "desc"]
			],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>