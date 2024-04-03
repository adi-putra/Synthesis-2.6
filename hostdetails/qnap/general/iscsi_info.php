<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
//get hostid
$hostid = $_GET['hostid'];


//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid
);
//call api method
$result = $zbx->call('host.get', $params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
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
		<title>ISCSI Info - qnap</title>
	</head>

	<body>
		<table id="iscsi_tbl" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>LUN Name</th>
					<th>LUN Status</th>
					<th>LUN Capacity Used (%)</th>
					<th>LUN Capacity (GB)</th>
				</tr>
			</thead>

			<tbody>
		
				<?php
					//NAME
					$params = array(
						"output" => array("key_","name","lastvalue"),
						"hostids" => $hostid,
						"search" => array("name" => "LUN Name")
					);
					
					$result = $zbx->call('item.get',$params);
					
					$i=1;
					
					foreach($result as $row){
						
						print "<tr>";
						$params = array(
							"output" => array("key_","name","lastvalue"),
							"hostids" => $hostid,
							"search" => array("name" => "LUN")
						);
						
						$result = $zbx->call('item.get',$params);
						foreach(array_reverse($result) as $row){
							if(strpos($row["key_"], "[".$i."]") !== false){

								if(strpos($row["name"], "Capacity") !== false && strpos($row["name"], "Used") == false){
							
									$row['lastvalue'] = formatBytes($row['lastvalue']);
								}

								if(strpos($row["name"], "Status") !== false){
							
									$row['lastvalue'] = "<button class='btn btn-success'>".$row['lastvalue']."</button>";
								}

								if(strpos($row["name"], "Used") !== false){
							
															
									$row['lastvalue'] = '<div class="progress xs">
															<div class="progress-bar progress-bar-aqua" style="width:'.$row['lastvalue'].'" role="progressbar" aria-valuenow="'.$row['lastvalue'].'" aria-valuemin="0" aria-valuemax="100">
															<span class="sr-only">'.$row['lastvalue'].'%</span>
															</div>
														</div>';
								}


								

								print "<td>".$row['lastvalue']."</td>";
							}
						}
						print "</tr>";

						$i ++;
					}
					

				?>
			</tbody>
		
		</table>
	</body>

	<script>
		$(document).ready( function () {
			$('#iscsi_tbl').DataTable({
				order: [[1, 'desc']],
			});
		} );
	</script>

</html>