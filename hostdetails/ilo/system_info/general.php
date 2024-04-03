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
      <th>System Object ID</th>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "system.objectid")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "<td>No data</td>";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $sysObjID = $row["lastvalue"];
        print "<td>$sysObjID</td>";
        }
      }
      ?>
    </tr>
    <tr>
      <th>System Description</th>
      <td>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "system.descr")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "No data";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $sysDesc = $row["lastvalue"];
        print $sysDesc;
        }
      }
      ?>
      </td>
    </tr>
    <tr>
      <th>System Location</th>
      <td>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "system.location")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "No data";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $sysLocation = $row["lastvalue"];
        print $sysLocation;
        }
      }
      ?>
      </td>
    </tr>
    <tr>
      <th>System Contact Details</th>
      <td>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "system.contact")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "No data";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $sysContact = $row["lastvalue"];
        if ($sysContact == "") {
          print "No data";
        }
        else {
        print $sysContact;
        }
      }
      }
      ?>
      </td>
    </tr>
    <tr>
      <th>SNMP Traps(fallback)</th>
      <td>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "snmptrap.fallback")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "No data";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $snmptrap = $row["lastvalue"];
        if ($snmptrap == "") {
          print "No data";
        }
        else {
        print $snmptrap;
        }
      }
      }
      ?>
      </td>
    </tr>
    <tr>
      <th>Hardware Serial Number</th>
      <td>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "system.hw.serialnumber")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "No data";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $serialNum = $row["lastvalue"];
        if ($serialNum == "") {
          print "No data";
        }
        else {
        print $serialNum;
        }
      }
      }
      ?>
      </td>
    </tr>
    <tr>
      <th>Hardware Model Name</th>
      <td>
      <?php
      $params = array(
      "output" => array("itemid"),
      "hostids" => $hostid,
      "search" => array("key_" => "system.hw.model")//seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get',$params);
      foreach ($result as $item) {
          $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "No data";
      }
      else {
      $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid
      );

      //call api history.get with params
      $result = $zbx->call('item.get',$params);
      foreach ($result as $row) {
        $modelName = $row["lastvalue"];
        if ($modelName == "") {
          print "No data";
        }
        else {
        print $modelName;
        }
      }
      }
      ?>
      </td>
    </tr>
  </table>
</body>
</html>