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
	<style>
		h3 {
			display: inline-block;
		}
	</style>
</head>
<body>
	<div></div>
	<?php
  //Virtual Machines
  $totalVM = 0;
  $vmOnCount = 0;
  $vmOffCount = 0;
  $vmUnCount = 0;
  $params = array(
    "output" => array("name", "lastvalue", "key_"),
    "hostids" => $hostid,
    "search" => array("key_" => "vmwVMState") //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  foreach ($result as $item) {
    if ($item["lastvalue"] == "powered on") {
      $vmOnCount++;
      $totalVM++;
    } else if ($item["lastvalue"] == "") {
      $vmUnCount++;
      $totalVM++;
    } else {
      $vmOffCount++;
      $totalVM++;
    }
  }

  print "<h3 style='color: lightgreen;'>$vmOnCount </h3>
  				<h3>/ </h3>
  				<h3 style='color: tomato;'>$vmOffCount </h3>
  				<h3>/ </h3>
  				<h3 style='color: aqua;'>$vmUnCount </h3>";
  ?>
</body>
</html>