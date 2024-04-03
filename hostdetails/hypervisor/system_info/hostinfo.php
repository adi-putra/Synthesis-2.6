<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
	);
//call api method
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2) { 
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
	<meta charset="utf-8">
	<title></title>
</head>
<body>
	<table id="example1" class="table table-bordered table-striped">
		<thead>
			<tr>
				<td><b>Host Name</b></td>
				<td><b>Status</b></td>
			</tr>
		</thead>
        <tbody>

		<?php
       
	       //get hostname
		   $params = array(
			"output" => array("hostid","name"),
			"hostids" => $hostid,
			
			);
			//call api method
			$result = $zbx->call('host.get',$params);
			$hostParentName = $result['0']['name'];

			$params = array(
			"output" => array("host","name" ,"lastvalue"),
			"selectHosts" =>"extend",
			"search" => array("name" => "hypervisor name")//seach id contains specific word
			);
			//call api method
			$result = $zbx->call('item.get',$params);    
			//$count = 1;
			foreach ($result as $item) {
				
				if($item['lastvalue'] == $hostParentName){
						
					print "<tr>";
					print "<td><a href='hostdetails_vm.php?hostid=".$item['hosts'][0]['hostid']."' >".$item['hosts'][0]['name']."</a></td>";

					$id = $item['hosts'][0]['hostid'];
					$getPing = array(
					"output" => array("name" ,"lastvalue"),
					"hostids" =>$id,
					"search" => array("name" => "VMware: ICMP ping")//seach id contains specific word
					);
				   //call api method
					$resultPing = $zbx->call('item.get',$getPing);
					foreach ($resultPing as $pingArr) {

						$ping = $pingArr['lastvalue'];
                          
						if(empty($ping)){

							$pingtxt = "<button class='btn btn-block btn-danger'>Down</button>";

						}else if ($ping == 1) {
							$pingtxt = "<button class='btn btn-block btn-success'>Up</button>";
						}
						else {
							$pingtxt = "<button class='btn btn-block btn-gray'>Unclassified</button>";
						}					
				    }
					print "<td>".$pingtxt."</a></td>";
					print "</tr>";
				}
			}
		?>
		</tbody>
    </table>

	<script>
      $(function () {
        $("#example1").DataTable();
        $('#example2').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": false,
          "ordering": true,
          "info": true,
          "autoWidth": false
        });
      });
    </script>
</body>
</html>