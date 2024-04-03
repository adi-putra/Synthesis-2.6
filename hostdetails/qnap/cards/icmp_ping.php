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
	<?php
		$params = array(
			"output" => array("lastvalue"),
			"hostids" => $hostid,
			"search" => array("name" => "ICMP ping")
			);
		//call api method
		$result = $zbx->call('item.get',$params);
		foreach ($result as $item) {

			$value = $item["lastvalue"];
		}

		if ($value == 0) {
			$text = "UP";
		}else if ($value == 1) {
			$text = "UP";
		}else {
			$text = "Unclassified";
		}

		echo $text;
   ?>

	<script>
		var value = "<?php echo $value ?>";

		if (value == 0 ) {
			$("# ").attr("class", "small-box bg-red");
		} else if (value == 1) {
			$("#icmp_ping_div").attr("class", "small-box bg-green");
		}else {
			$("#icmp_ping_div").attr("class", "small-box bg-gray");
		}
  </script>
</body>
</html>