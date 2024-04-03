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
			<th>Item</th>
			<td><b>Value</b></td>
		</tr>
      <tr>
        <th>Committed Storage Space</th>
        <td>
          <?php
          $params = array(
          "output" => array("itemid", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "VMware: Committed Storage Space")//seach id contains specific word
          );
          //call api method
          $result = $zbx->call('item.get',$params);
          foreach ($result as $item) {
            $commstorage = $item["lastvalue"];
          }
          echo formatBytes($commstorage);
          ?>
        </td>
      </tr>
      <tr>
        <th>Uncommitted Storage Space</th>
        <td>
          <?php
          $params = array(
          "output" => array("itemid", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "VMware: Uncommitted Storage Space")//seach id contains specific word
          );
          //call api method
          $result = $zbx->call('item.get',$params);
          foreach ($result as $item) {
            $uncommstorage = $item["lastvalue"];
          }
          echo formatBytes($uncommstorage);
          ?>
        </td>
      </tr>
      <tr>
        <th>Unshared Storage Space</th>
        <td>
          <?php
          $params = array(
          "output" => array("itemid", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "VMware: Unshared Storage Space")//seach id contains specific word
          );
          //call api method
          $result = $zbx->call('item.get',$params);
          foreach ($result as $item) {
            $unsharedstorage = $item["lastvalue"];
          }
          echo formatBytes($unsharedstorage);
          ?>
        </td>
      </tr>
  </table>
</body>
</html>