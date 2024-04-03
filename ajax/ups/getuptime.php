<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];


//
function secondsToTime($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	$daycount = $dtF->diff($dtT)->format('%d');
	$yearcount = $dtF->diff($dtT)->format('%y');
	$weekcount = (int)ltrim($daycount / 7, '0.'); // convert to int -> divide days by 7 -> trim decimal  

	// If year is 0 then trim year, if day is more than 7 then convert to weeks
	if ($yearcount == 0 && $daycount > 7) {
		return $dtF->diff($dtT)->format('%m month(s), ' . $weekcount . ' week(s)');
	} else if ($yearcount == 0 && $daycount <= 7) {
		return $dtF->diff($dtT)->format('%m month(s), %d day(s)');
	} else if ($daycount > 7) {
		return $dtF->diff($dtT)->format('%y year(s), %m month(s), ' . $weekcount . ' week(s)');
	} else {
		return $dtF->diff($dtT)->format('%y year(s), %m month(s), %d day(s)');
	}

	// return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

// Get year
function secondsToYear($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	$yearcount = $dtF->diff($dtT)->format('%y');
	return $yearcount;
}

// Get month
function secondsToMonth($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	$monthcount = $dtF->diff($dtT)->format('%m');
	return $monthcount;
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
			<table id="uptimetable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Host Name</th>
						<th>Uptime</th>
						<th>Duration</th>
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

						//Current CPU Utilization Loader
						$params = array(
							"output" => array("itemid", "name", "key_"),
							"hostids" => $hostID,
							"search" => array("name" => "uptime") //seach id contains specific word
						);

						//call api method
						$result = $zbx->call('item.get', $params);
						foreach ($result as $item) {
							$itemid = $item["itemid"];
						}

						if (isset($itemid) == false) {
							print "<td>No data</td>";
						} else {
							$params = array(
								"output" => array("lastvalue"),
								"itemids" => $itemid
							);

							//call api history.get with params
							$result = $zbx->call('item.get', $params);
							foreach ($result as $row) {
								$uptimedays = $row["lastvalue"];
								$uptimeduration = secondsToTime($uptimedays);
								
								
								$exYear = secondsToYear($uptimedays); // Extract year from $uptimedays
								$exMonth = secondsToMonth($uptimedays); // Extract month from $uptimedays
								$rebootunix = time() - $uptimedays;
								$rebootdate = date("d-m-Y h:i:s A", $rebootunix);

								if ($exMonth < 3 || $exMonth == 0) {
									$status = "red";
									$loader = "progress-bar progress-bar-danger";
								} else if ($exMonth >= 3 && $exMonth < 6) {
									$status = "orange";
									$loader = "progress-bar progress-bar-warning";
								} else if ($exMonth >= 6 || $exYear >= 1) {
									$status = "seagreen";
									$loader = "progress-bar progress-bar-success";
								}
								// (Actual Value / Max Value) * 100 , max value is total seconds withtin a year
								$bar = ($uptimedays / 31536000) * 100;
							}
						}
						print '<tr>
                    			<td><a href="hostdetails_ups.php?hostid=' . $hostID . '#performance" target="_blank">' . $hostname . '</a></td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="' . $loader . '" style="width: ' . $bar . '%;"></div>
	                                  </div>
	                                </td>
	                                <td style="color: white; background-color: ' . $status . ';">' . $uptimeduration . '</td>
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
		$('#uptimetable').DataTable({
			"order": [
				[2, "desc"]
			],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>