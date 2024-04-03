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
			}
			
		</style>
	</head>

	<body>
		<table id="vm_jobs_overview" class="table table-bordered table-striped">
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
			
			//Number of VMs in job
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("name" => 'Number of VMs in job : '),//seach id contains specific word
				);
			
			$VM_no = $zbx->call('item.get',$params);

			//VMs Failed
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("name" => 'Number of VMs Failed '),//seach id contains specific word
				);
			
			$VM_failed = $zbx->call('item.get',$params);

			//vm warning
			$params = array(
				"output" => array("itemid", "name", "lastvalue"),
				"hostids" => $hostID,
				"search" => array("name" => 'Number of VMs Warning '),//seach id contains specific word
				);
			
			$VM_warning = $zbx->call('item.get',$params);

			// echo "<pre>";
			// print_r($VM_warning);
			// echo "</pre>";
			
		}
		?>
 
		<?php if(empty($VM_no) && empty($VM_failed) && empty($VM_warning)){ ?>
		<th style="text-align:center" scope="row">No Data</th>
		<?php }else{ ?>

		<thead>
			<tr>
			<th class="header" scope="row">Details/Hosts</th>
			<?php
				foreach($VM_no as $item){

					$item_name = substr($item["name"], 23);
				?>
			<th class="head" scope="row"><?= $item_name ?></th>
			<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row">Number of jobs in VMs</th>
				<?php
				foreach($VM_no as $no){

					$data = $no["lastvalue"];
					
				?>
				<td ><?= $data ?></td>
				<?php } ?>
			</tr>
			<tr>
				<th scope="row">Number of VMs Failed</th>
				<?php
				foreach($VM_failed as $failed){

					$data = $failed["lastvalue"];
					
				?>
				<td ><?= $data ?></td>
				<?php } ?>
			</tr>
			<tr>
				<th scope="row">Number of VMs Warning</th>
				<?php
				foreach($VM_warning as $warning){

					$data = $warning["lastvalue"];
				?>
				<td ><?= $data ?></td>
				<?php } }?>
			</tr>
		</tbody>
		</table>

		<script type="text/javascript">
		$(document).ready(function() {
			$('#vm_jobs_overview').DataTable( {
			dom: 'Bfrtip',
			buttons: [
				'colvis'
			]
			});
		});
	</script>

	</body>

</html>