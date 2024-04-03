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
	<table class="table table-bordered table-striped">
      <tr>
        <th>Date</th>
        <?php
          $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "UPS Self Test Date")//seach id contains specific word
          );
          //call api method
          $result = $zbx->call('item.get',$params);
          foreach ($result as $item) {
              $itemid = $item["itemid"];
              $itemvalue = $item["lastvalue"];
          }

          if (isset($itemid) == false || empty($itemvalue)) {
            print "<td>No date</td>";
          }
          else {
          $params = array(
          "output" => array("lastvalue"),
          "itemids" => $itemid
          );

          //call api history.get with params
          $result = $zbx->call('item.get',$params);
          foreach ($result as $row) {
            $testdate = $row["lastvalue"];
            print "<td>$testdate</td>";
            }
          }
          ?>
      </tr>
      <tr>
        <th>Result</th>
        <?php 
       			$params = array(
              "output" => array("name", "lastvalue"),
              "hostids" => $hostid,
              "search" => array("name" => "UPS Self Test Result") //seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get', $params);
            foreach ($result as $item) {
              $testresult = $item["lastvalue"];
      
              if ($testresult == 1) {
                print "<td><strong class='text-success'>PASSED</button></strong></tr>";
              } else {
                print "<td><strong class='text-danger'>FAILED</button></strong></tr>";
              }
            }
        ?>
      </tr>
    </table>
</body>
</html>