<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];
$timefrom = $_GET['timefrom'] ?? strtotime('today');
$timetill = $_GET['timetill'] ?? strtotime('now +2 minutes');
$fromdiff = $timefrom - strtotime('today');
$tilldiff = $timetill - strtotime('now +2 minutes');

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
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
	<meta charset="utf-8">
	<title></title>
</head>

<body>
	<?php
	$params = array(
		"output" => array("itemid","name","lastvalue"),
		"hostids" => $hostid,
		"search" => array("name" => "ICMP response time"),
	
	);
	//call api method
	$result = $zbx->call('item.get', $params);
	foreach ($result as $item) {
		$lastval_responsetime = $item["lastvalue"] * 1000;
		$itemid = $item['itemid'];
	}
	$lastval_responsetime = round($lastval_responsetime, 2);
	
// print_r ($result);
	// $params = array(
    //     "output" => array("value", "clock"),
    //     "itemids" => $itemid,
    //     "history" => 0,
    //     "sortfield" => "clock",
    //     "sortorder" => "DESC",
    //     "time_from" => $timefrom, 
    //     "time_till" => $timetill,
	// 	"limit" => 1
    //   );

    //   $result = $zbx->call('history.get', $params);
	// //   print_r ($result);

	//   foreach ($result as $row) {
	// 	$current_responsetime = $row['value'] * 1000;
	// }

	// $current_responsetime = round($current_responsetime, 2);

	// // print_r ($fromdiff);
	// // print_r ($tilldiff);

	if ($current_responsetime >= 0 && $current_responsetime < 50) {
		echo '<div class="small-box bg-green">';
	}
	if ($current_responsetime >= 50 && $current_responsetime < 70) {
		echo '<div class="small-box bg-yellow">';
	}
	if ($current_responsetime >= 70) {
		echo '<div class="small-box bg-red">';
	}
	echo '<div class="inner">';

	// if ($fromdiff > 60 || $tilldiff > 60 || $fromdiff < -60 || $tilldiff < -60){
	// 	echo '<h3>' . $current_responsetime . 'ms</h3>';
	// } else{
		echo '<h3>' . $lastval_responsetime . 'ms</h3>';
	// }
	
	echo '<p>Response Time</p>
	</div>
		<a href="#responsetime" class="small-box-footer">
			More info <i class="fa fa-arrow-circle-right"></i>
		</a>
	</div>';
	?>
</body>

</html>