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


  $params = array(
    //   "output" => array("name","lastvalue","triggerid","eventid","objectid"),
      "hostids" => $hostid
  );
  //call api method
  $result = $zbx->call('problem.get',$params);

//   $count = 1; 
//     foreach ($result as $problem)  
//     { 
//        if($problem['severity'] == 4){

//             echo $count ;
//        }
      
//         $count ++; 
//     } 

  $count = count($result);
  $sum = 0;
  for($i = 0; $i <= $count; $i++ ){

    if($result[$i]['severity'] == 4){

        $sum += $i;

        echo $sum;

    }
  }
            
  echo "<pre>";
  print_r($result);
  echo "</pre>";

?>                          
</body>
</html>