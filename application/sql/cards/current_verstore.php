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
    "search" => array("name" => "Version Store Size (KB)")
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  foreach ($result as $item) {
    $current_verstore = $item["lastvalue"];
  }

//   echo round($current_verstore, 2).'KB';

  $current_memclerk = round($current_verstore, 2);

  if ($current_memclerk >= 0 && $current_memclerk < 500) {
	echo '<div class="small-box bg-green">';
}
if ($current_memclerk >= 500 && $current_memclerk < 1000) {
	echo '<div class="small-box bg-yellow">';
}
if ($current_memclerk >= 1000) {
	echo '<div class="small-box bg-red">';
}
echo '<div class="inner">';
echo '<h3>' . $current_memclerk . 'KB</h3>';
echo '<p>Version Store Size</p>
</div>
	<a onclick="openStatTab()" href="#verstoresize" class="small-box-footer">
		More info <i class="fa fa-arrow-circle-right"></i>
	</a>
</div>';
  ?>
</body>
</html>