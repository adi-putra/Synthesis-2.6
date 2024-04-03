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
		<style>

			td{
				text-align: center;
			}

			.header{
				font-size:larger;
			}

			.head{
				text-align: center;
				font-size:larger;
			}
			
		</style>
	</head>

	<body>
		<table id="dbstatus_table" class="table table-bordered table-striped">
		<?php
		//counters
		$count = 1;

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
			
			//job status
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("key_" => 'vbr[RunStatus,'),//seach id contains specific word
				);
			
			$jobStatus = $zbx->call('item.get',$params);

			//jobs_runtime
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("key_" => 'vbr[LastRunTime'),//seach id contains specific word
				);
			
			$jobsRuntime = $zbx->call('item.get',$params);

			//jobs_endruntime
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("name" => 'Last End Time Job'),//seach id contains specific word
				);
			
			$jobsEndRuntime = $zbx->call('item.get',$params);

			//jobs_count
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("name" =>array('Number of active jobs','Number of jobs (Total)')),//seach id contains specific word
				"searchByAny"=> true
				);
			
			$jobsCount = $zbx->call('item.get',$params);

			//result backup
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("name" => 'Result backup'),//seach id contains specific word
				);
			
			$resultBackup = $zbx->call('item.get',$params);
			
		}
		?>
		<?php if(empty($jobStatus) && empty($jobsRuntime) && empty($jobsEndRuntime) && empty($resultBackup)){ ?>
		<th style="text-align:center" scope="row">No Data</th>
		<?php }else{ ?>

		<thead>
			
			<tr>
			<th class="header" scope="row">Details/Hosts</th>
			<?php
			foreach($jobsRuntime as $runtime){

				$item_name = substr($runtime["name"], 20);
			?>
			<th class="head" scope="row"><?= $item_name ?></th>
			<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row">Jobs Execution Status</th>
				<?php
				foreach($jobStatus as $status){

					if($status["lastvalue"] == 0){
						$data = "<span class='badge bg-red'>Stopped</span>" ;
					}else if ($status["lastvalue"] == 1)
					{
						$data = "<span class='badge bg-green'>Running</span>" ;
					}else{
						$data = "<span class='badge bg-gray'>Undefined</span>" ;
					}
					
				?>
				<td ><?= $data ?></td>
				<?php } ?>
			</tr>
			<tr>
				<th scope="row">Last Run Time Jobs</th>
				<?php
				foreach($jobsRuntime as $runtime){

					$dateruntime = $runtime["lastvalue"];
					$MY = new DateTimeZone('Asia/Kuala_Lumpur');
					$date = new DateTime("@$dateruntime");
					$date->setTimezone($MY);
					$data =  $date->format('d-m-Y H:i:s');
					
				?>
				<td ><?= $data ?></td>
				<?php } ?>
			</tr>
			<tr>
				<th scope="row">Last End Time Jobs</th>
				<?php
				foreach($jobsEndRuntime as $endruntime){

					$dateendruntime = $endruntime["lastvalue"];
					$MY = new DateTimeZone('Asia/Kuala_Lumpur');
					$date = new DateTime("@$dateendruntime");
					$date->setTimezone($MY);
					$data =  $date->format('d-m-Y H:i:s');
				?>
				<td ><?= $data ?></td>
				<?php } ?>
			</tr>
			<tr>
				<th scope="row">Result Backup</th>
				<?php
				foreach($resultBackup as $res){

					if ($res["lastvalue"] == 1) {
						
						$data = "<span class='badge bg-yellow'>Warning</span>";
						//$span_class = "badge bg-red";
					}
					else if ($res["lastvalue"] == 2) {
						$data = "<span class='badge bg-green'>Success</span>" ;
						//$span_class = "badge bg-green";
					}
					else {
						$data = "<span class='badge bg-gray'>Undefined</span>" ;
						//$span_class = "badge bg-default";
					}
				?>
				<td ><?= $data ?></td>
				<?php } }?>
			</tr>
		</tbody>
		</table>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#dbstatus_table').DataTable( {
			dom: 'Bfrtip',
			buttons: [
				'colvis'
			]
			});
		});
	</script>

	</body>

</html>