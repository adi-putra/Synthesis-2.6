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
$result = $zbx->call('host.get', $params);
foreach ($result as $host) {
  $hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2)
{
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
      <th>Number of Disks</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Number of Disks") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $numOfDisk = $item['lastvalue'];
          }
        }
        print $numOfDisk;
        ?>
      </td>
    </tr>
    <tr>
      <th>
        Disk Failed Message</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Disk Failed Message") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $numOfDisk = $item['lastvalue'];
          }
        }
        print $numOfDisk;
        ?>
      </td>
    </tr>
    <tr>
      <th>Failed Disk Count</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Failed Disk Count") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $failedDiskCount = $item['lastvalue'];
          }
        }
        print $failedDiskCount;
        ?>
      </td>
    </tr>
    <tr>
      <th>Prefailed Disks Count</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Prefailed Disks Count") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $preDiskCount = $item['lastvalue'];
          }
        }
        print $preDiskCount;
        ?>
      </td>
    </tr>
    <tr>
      <th>Number of verifying parity Disks</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Number of verifying parity Disks") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $numOfParityDisk = $item['lastvalue'];
          }
        }
        print $numOfParityDisk;
        ?>
      </td>
    </tr>
    <tr>
      <th>
        Number of spare Disks</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => " Number of spare Disks") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $numOfSpareDisk = $item['lastvalue'];
          }
        }
        print $numOfSpareDisk;
        ?>
      </td>
    </tr>
  </table>

</body>

</html>