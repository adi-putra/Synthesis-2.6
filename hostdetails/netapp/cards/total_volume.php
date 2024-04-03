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
  //get last value of application id
  // $params = array(
  //   "output" => array("name","lastvalue"),
  //   "hostids" => $hostid
  // );
  // //call api method
  // $result = $zbx->call('application.get',$params);

  // foreach($result as $data){

  //   if($data['name'] == 'Volumes'){

  //     $applicationid = $data['applicationid'];

  //   }
  // }
  

  // get item
  $params = array(
    "output" => array("name","lastvalue"),
    "hostids" => $hostid,
    "name" => "Volumes"
  );
  
  $volume = $zbx->call('item.get',$params);  

  echo "<pre>";
  print_r($volume['name']);
  echo "</pre>";
  foreach ($volume as $volumes) {

    if(str_contains($volumes['name'], 'Total Size') ){

  
      // echo "<pre>";
      // print_r($volumes);
      // echo "</pre>";

    }

  }
 
  //echo round($cpu_time, 2)." %";
  ?>
</body>
</html>