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
	<title>ESX Overview</title>
</head>

<body>
	<div class="row">
		<div class="col-md-12">
			<!-- CPU Util table -->
			<table id="disktable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Host Name</th>
						<th>Free Disk (%)</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
					<?php

					//get disk space on percentage
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

						//Current Disk Percentage
						$params = array(
							"output" => array("itemid", "name", "key_"),
							"hostids" => $hostID,
							"search" => array("name" => "free space on datastore") //seach id contains specific word
						);
						//call api method
						$result = $zbx->call('item.get', $params);
						foreach ($result as $item) {
							
								$itemid = $item["itemid"];
								$itemkey = substr($item["key_"], 0, -7);
								$itemkey = substr($itemkey, 40);
								$itemname = "Free space on ".$itemkey;

								$params = array(
									"output" => array("lastvalue"),
									"itemids" => $itemid
								);
								//call api item.get with params
								$result = $zbx->call('item.get', $params);
								foreach ($result as $row) {
									$diskpercent = number_format((float)$row["lastvalue"], 2, '.', '');
									$disklabel = $diskpercent;

									if ($diskpercent > 70) {
										$diskstatus = "seagreen";
										$diskloader = "progress-bar progress-bar-success";
									} else if ($diskpercent > 30 && $diskpercent <= 70) {
										$diskstatus = "orange";
										$diskloader = "progress-bar progress-bar-warning";
									} else if ($diskpercent > 0 && $diskpercent <= 30) {
										$diskstatus = "red";
										$diskloader = "progress-bar progress-bar-danger";
									} else if ($diskpercent <= 0.00) {
										$diskstatus = "red";
										$diskloader = "progress-bar progress-bar-danger";
										$disklabel = "0.00";
										$diskpercent = 1;
									}
								}

								print '<tr>
	                              <td><a href="hostdetails_esx.php?hostid=' . $hostID . '#capacity" target="_blank">' . $hostname . ":</a> " . $itemname . '</td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="' . $diskloader . '" style="width: ' . $diskpercent . '%;"></div>
	                                  </div>
	                                </td>
	                              <td style="color: white; background-color:' . $diskstatus . ';">' . $disklabel . ' %</td>
	                           </tr>';
							
						}
					}

					?>
				</tbody>
			</table>
		</div>
	</div>
	<script type="text/javascript">
		$(function() {
			$('#disktable').DataTable({
				"order": [
					[2, "asc"]
				],
				"scrollY": "400px",
				"scrollCollapse": true,
				"paging": false
			});
		});
	</script>
</body>

</html>