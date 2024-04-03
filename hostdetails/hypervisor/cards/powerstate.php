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
  //get last value of cpu utilization
  $params = array(
    "output" => array("lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => "VMware: power state")
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  foreach ($result as $item) {
    $powerstate = $item["lastvalue"];
  }

  if ($powerstate == 1) {
    $powerstatetxt = "Powered On";
  }
  else {
    $powerstatetxt = "Powered Off";
  }

  echo $powerstatetxt;
  ?>

  <script>
    var powerstate = "<?php echo $powerstate ?>";

    if (powerstate == 1) {
      $("#powerstatediv").attr("class", "small-box bg-green");
    } 
    else {
      $("#powerstatediv").attr("class", "small-box bg-red");
    }
  </script>
</body>
</html>