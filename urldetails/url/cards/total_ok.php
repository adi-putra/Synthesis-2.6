<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

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

	<?php
	$params = array(
		"output" => "extend",
		"selectHttpTests" => "extend"
	);

	$result = $zbx->call('host.get', $params);
	foreach($result as $host){

		$hostlist = $host['host'];
		$hostids = $host['hostid'];
		if(!empty($host['httpTests'])){

			$params = array(
				"output" => array("name","lastvalue"),
				"hostids" => $hostids,
				"webitems" => true
			);

			$res_webitem = $zbx->call('item.get',$params);

			if(!empty($res_webitem)){
				$count = 0;
				foreach($res_webitem as $webItems){
					if (strpos($webItems['name'], "Response code for step") !== false){

						if($webItems['lastvalue'] == 200){

							$count ++;
						}
					}
				}
			}
		}
	}

	echo $count;
		
  ?>
</body>
</html>