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
    "search" => array("name" => 'VMware: Status of "DL3xx Production Cluster" cluster')
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  foreach ($result as $item) {
    $status = $item["lastvalue"];
  }

  if ($status == 0) {
    $statustxt = "Unknown";
  }
  else if($status == 1){
    $statustxt = "OK";

  }else if($status == 2){

    $statustxt = "It Might Have Problem";

  }else if($status == 3){

    $statustxt = "It Has Problem";

  }else{

    $statustxt = "Undefined";
  }

  echo $statustxt;
  ?>

  <script>
    var status = "<?php echo $status ?>";

    if (status == 0) {
      $("#clusterstatusdiv").attr("class", "small-box bg-gray");
    }

    else if (status == 1) {
      $("#clusterstatusdiv").attr("class", "small-box bg-green");
    } 
    else if (status == 2) {
      $("#clusterstatusdiv").attr("class", "small-box bg-yellow");
    } 
    else if (status == 3) {
      $("#clusterstatusdiv").attr("class", "small-box bg-red");
    } 
    else {
      $("#clusterstatusdiv").attr("class", "small-box bg-gray");
    }
  </script>
</body>
</html>