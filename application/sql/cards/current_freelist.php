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
    "search" => array("name" => "Free List Stalls / sec")
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  foreach ($result as $item) {
    $current_freelist = $item["lastvalue"];
  }

//   echo round($current_freelist, 2);

  $current_freelist = round($current_freelist, 2);

  if ($current_freelist >= 0 && $current_freelist < 50) {
	  echo '<div class="small-box bg-green">';
  }
  if ($current_freelist >= 50 && $current_freelist < 100) {
	  echo '<div class="small-box bg-yellow">';
  }
  if ($current_freelist >= 100) {
	  echo '<div class="small-box bg-red">';
  }
  echo '<div class="inner">';
  echo '<h3>' . $current_freelist . '</h3>';
  echo '<p>Free List Stalls/sec</p>
	</div>
	<a onclick="openStatTab()" href="#freelist" class="small-box-footer">
		More info <i class="fa fa-arrow-circle-right"></i>
	</a>
	</div>';
  ?>
</body>
</html>