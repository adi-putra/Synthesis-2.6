<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
	</head>
	<body>
		<table id="joboverview_table" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>Host : Job Name</th>
					<th>Job Status</th>
					<th>Job Backup Result</th>
					<th>Last Backup Start</th>
					<th>Last Backup End</th>
					<th>Last Duration</th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($hostid as $hostID) {

				//get hostname
				$params = array(
				"output" => array("name"),
				"hostids" => $hostID,
				);
				//call api method
				$result = $zbx->call('host.get',$params);
				foreach ($result as $host) {
					$gethostname = $host["name"];
				}

				//job end backup time
				$params = array(
					"output" => array("itemid", "name", "lastvalue"),
					"hostids" => $hostID,
					"search" => array("key_" => 'vbr[LastRunTime,'),//seach id contains specific word
				);
				$result = $zbx->call('item.get',$params);
				foreach ($result as $item) {
					$gethostveeam = substr($item["name"], 20);
					$gethostveeam_conv = str_replace("Zabbix", "Synthesis", $gethostveeam);
					
					$job_startbackup = date("d-m-Y H:i:s A", $item["lastvalue"]);
					$job_startbackup_unix = $item["lastvalue"];

					//job end backup time
					$params = array(
						"output" => array("itemid", "name", "lastvalue"),
						"hostids" => $hostID,
						"search" => array("name" => "Last End Time Job : $gethostveeam"),//seach id contains specific word
					);
					$result = $zbx->call('item.get',$params);
					foreach ($result as $item) {
						$job_endbackup = date("d-m-Y H:i:s A", $item["lastvalue"]);
						$job_endbackup_unix = $item["lastvalue"];
					}

					//job end backup time
					$params = array(
						"output" => array("itemid", "name", "lastvalue"),
						"hostids" => $hostID,
						"search" => array("name" => "Job execution status $gethostveeam"),//seach id contains specific word
					);
					$result = $zbx->call('item.get',$params);
					foreach ($result as $item) {
						$job_status = $item["lastvalue"];
						if ($job_status == 0) {
							$job_status_btn = '<button class="btn btn-block btn-danger">Stopped</button>';
						}
						else if ($job_status == 1) {
							$job_status_btn = '<button class="btn btn-block btn-success">Running</button>';
						}
					}

					//job backup result
					$params = array(
						"output" => array("itemid", "name", "lastvalue"),
						"hostids" => $hostID,
						"search" => array("name" => "Result backup $gethostveeam"),//seach id contains specific word
					);
					$result = $zbx->call('item.get',$params);
					foreach ($result as $item) {
						$job_backupres = $item["lastvalue"];
						if ($job_backupres == 0) {
							$job_backupres_btn = '<button class="btn btn-block btn-danger">Failed</button>';
						}
						else if ($job_backupres == 1) {
							$job_backupres_btn = '<button class="btn btn-block btn-warning">Warning</button>';
						}
						else if ($job_backupres == 2) {
							$job_backupres_btn = '<button class="btn btn-block btn-success">Success</button>';
						}
						else if ($job_backupres == 3) {
							$job_backupres_btn = '<button class="btn btn-block btn-info">Idle</button>';
						}
						else if ($job_backupres == 4) {
							$job_backupres_btn = '<button class="btn btn-block btn-danger">Disabled, First Backup or no history</button>';
						}
						else if ($job_backupres == 5) {
							$job_backupres_btn = '<button class="btn btn-block btn-info">In progress</button>';
						}
						else if ($job_backupres == 6) {
							$job_backupres_btn = '<button class="btn btn-block btn-info">On Standby</button>';
						}
					}

					//job backup duration
					$job_duration = gmdate("H:i:s", $job_endbackup_unix - $job_startbackup_unix);
					
					print '<tr>';
					print '<td>'.$gethostname.' : '.$gethostveeam_conv.'</td>';
					print '<td>'.$job_status_btn.'</td>';
					print '<td>'.$job_backupres_btn.'</td>';
					print '<td>'.$job_startbackup.'</td>';
					print '<td>'.$job_endbackup.'</td>';
					print '<td>'.$job_duration.'</td>';
					print '</tr>';
				}
			}
			?>
			</tbody>
		</table>
	</body>

<script type="text/javascript">
	$('#joboverview_table').DataTable();
</script>

</html>