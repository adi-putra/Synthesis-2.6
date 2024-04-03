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

function thousandsCurrencyFormat($num)
{

	if ($num > 1000) {

		$x = round($num);
		$x_number_format = number_format($x);
		$x_array = explode(',', $x_number_format);
		$x_parts = array('k', 'm', 'b', 't');
		$x_count_parts = count($x_array) - 1;
		$x_display = $x;
		$x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
		$x_display .= $x_parts[$x_count_parts - 1];

		return $x_display;
	}

	return $num;
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
		"search" => array("name" => "Page Lookup / Sec")
	);
	//call api method
	$result = $zbx->call('item.get', $params);
	foreach ($result as $item) {
		$current_pagelookup = $item["lastvalue"];
	}

	//   echo round(thousandsCurrencyFormat($current_pagelookup), 4);

	$current_pagelookup = round(thousandsCurrencyFormat($current_pagelookup), 4);

	if ($current_pagelookup >= 0 && $current_pagelookup < 50) {
		echo '<div class="small-box bg-green">';
	}
	if ($current_pagelookup >= 50 && $current_pagelookup < 100) {
		echo '<div class="small-box bg-yellow">';
	}
	if ($current_pagelookup >= 100) {
		echo '<div class="small-box bg-red">';
	}
	echo '<div class="inner">';
	echo '<h3>' . $current_pagelookup . '</h3>';
	echo '<p>Page lookups/sec</p>
</div>
	<a onclick="openStatTab()" href="#pagelookup" class="small-box-footer">
		More info <i class="fa fa-arrow-circle-right"></i>
	</a>
</div>';
	?>
</body>

</html>