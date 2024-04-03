<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

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
		"output" => array("itemid", "name", "lastvalue", "lastclock"),
		"hostids" => $hostid,
		"search" => array("name" => "Job ID:", "lastvalue" => "failed")

		// "output" => "extend",
		// "history" => 4,
		// "hostids" => $hostid,
		// "sortfield" => "clock",
		// "sortorder" => "DESC",
		// "search" => array("value" => "failed."),
		// "time_from" => $timefrom,
		// "time_till" => $timetill
	);
	//call api method
	$result = $zbx->call('item.get', $params);

	$count = 0;
	foreach ($result as $item) {
		if (!empty($item["lastvalue"])) {
			if ($item["lastclock"] >= $timefrom && $item["lastclock"] <= $timetill) {
				$count++;
			}
		}
	}

	if ($count == 0) {
		echo '<div class="small-box bg-green">';
	}
	if ($count >= 1 && $count < 10) {
		echo '<div class="small-box bg-yellow">';
	}
	if ($count >= 10) {
		echo '<div class="small-box bg-red">';
	}
	echo '<div class="inner">';
	echo '<h3>' . $count . '</h3>';
	echo '<p>Fail Job Counts</p>
  	</div>
		<a onclick="openIssueTab()" href="#jobfailed" class="small-box-footer" >
			More info <i class="fa fa-arrow-circle-right"></i>
		</a>
	</div>';
	?>
</body>
</html>