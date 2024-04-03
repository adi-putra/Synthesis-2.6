<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
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
	<title>Disk - qnap</title>
</head>

<body>
	<table id= "disk_tbl" class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Temperature</th>
				<th>Status</th>
				<th>S.M.A.R.T. info</th>
				<th>Disk Type</th>
				<th>Model</th>
				<th>Capacity</th>
				<th>Serials Number</th>
			</tr>
		</thead>
		<tbody>
		
				<?php
					//NAME
					$params = array(
						"output" => array("key_","name","lastvalue"),
						"hostids" => $hostid,
						"search" => array("name" => "Model of HDD")
					);
					
					$result = $zbx->call('item.get',$params);
					
					$i=1;
					
					foreach($result as $row){
						
						print "<tr>";
						$params = array(
							"output" => array("key_","name","lastvalue"),
							"hostids" => $hostid,
							"search" => array("name" => "HDD")
						);
						
						$result = $zbx->call('item.get',$params);
						foreach(array_reverse($result) as $row){
							if($row["key_"] != "HdDiskCap[".$i."]"){

								if(strpos($row["key_"], "[".$i."]") !== false){

									if(strpos($row["name"], "Capacity of HDD") !== false ){
								
										$row['lastvalue'] = formatBytes($row['lastvalue']);
									}
	
									if(strpos($row["name"], "Temperature of HDD") !== false){
								
										$row['lastvalue'] = $row['lastvalue'] ."C";
									}

									if(strpos($row["name"], "Status of HDD") !== false){
										if($row['lastvalue'] == 0){

											$row['lastvalue'] = "<button class='btn btn-danger btn-block' >Not Available</button>";

										}else if($row['lastvalue'] == 1){

											$row['lastvalue'] = "<button class='btn btn-success btn-block'>Available</button>";

										}else{

											$row['lastvalue'] = "<button class='btn btn-gray btn-block'>Unclassified</button>";

										}
								
									}
	
									print "<td>".$row['lastvalue']."</td>";
								}

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
        $('#disk_tbl').DataTable({
            order: [[1, 'desc']],
        });
    } );
</script>

</html>