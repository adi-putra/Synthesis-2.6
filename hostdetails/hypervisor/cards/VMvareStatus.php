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
    "search" => array("name" => "VMware: Overall status")
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  foreach ($result as $item) {
    $ping = $item["lastvalue"];
  }

  if ($ping == 0) {
    $pingtxt = "Unknown";
  }
  else if($ping == 1){
    $pingtxt = "OK";

  }else if($ping == 2){

    $pingtxt = "It Might Have Problem";

  }else if($ping == 3){

    $pingtxt = "It Has Problem";

  }else{

    $pingtxt = "Undefined";
  }

  echo $pingtxt;
  ?>

  <script>
    var ping = "<?php echo $ping ?>";

    if (ping == 0) {
      $("#healthstatusdiv").attr("class", "small-box bg-gray");
    }

    else if (ping == 1) {
      $("#healthstatusdiv").attr("class", "small-box bg-green");
    } 
    else if (ping == 2) {
      $("#healthstatusdiv").attr("class", "small-box bg-yellow");
    } 
    else if (ping == 3) {
      $("#healthstatusdiv").attr("class", "small-box bg-red");
    } 
    else {
      $("#healthstatusdiv").attr("class", "small-box bg-gray");
    }
  </script>
</body>
</html>