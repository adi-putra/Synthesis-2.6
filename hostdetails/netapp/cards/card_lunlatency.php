<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

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
  $count = 0;
  //get last value of cpu utilization
  $params = array(
    "output" => array("lastvalue", "key_", "name", "error"),
    "hostids" => $hostid,
    "search" => array("name" => array("LUN Latency")),
    "searchByAny" => true
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  if (!empty($result)) {
    foreach ($result as $item) {
      $lunlatency = number_format((float)$item["lastvalue"], 2, '.', '');
    }
  
    print $lunlatency." ms";
  }
  else {
    print "No data";
  }
  ?>
</body>
</html>