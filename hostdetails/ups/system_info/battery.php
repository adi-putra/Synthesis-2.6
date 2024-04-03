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

//for seconds to time
function secondsToHMS($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%H hours %I minutes %S seconds');
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
      <th>Replacement Status</th>
      <?php
      $params = array(
        "output" => array("name", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("name" => "Battery replacement status") //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        $itemstatus = $item["lastvalue"];

        if ($itemstatus == 1) {
          print "<td><button class='btn btn-block btn-success btn-sm'>Healthy</button></td></tr>";
        } else {
          print "<td><button class='btn btn-block btn-danger btn-sm'>Unhealthy</button></td></tr>";
        }
      }
      ?>
    </tr>
    <tr>
      <th>Charge Status</th>
      <?php
      $params = array(
        "output" => array("name", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("name" => "Battery charge") //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        $itemstatus = $item["lastvalue"];

        if ($itemstatus >= 75) {
          print "<td><button class='btn btn-block btn-success btn-sm'>$itemstatus%</button></td></tr>";
        } else if ($itemstatus >= 30 && $itemstatus < 75) {
          print "<td><button class='btn btn-block btn-warning btn-sm'>$itemstatus%</button></td></tr>";
        } else if ($itemstatus < 30) {
          print "<td><button class='btn btn-block btn-danger btn-sm'>$itemstatus%</button></td></tr>";
        } else {
          print "<td><button class='btn btn-block btn-danger btn-sm'>$itemstatus%</button></td></tr>";
        }
      }
      ?>
    </tr>
    <tr>
    <tr>
      <th>Runtime</th>
      <?php
      $params = array(
        "output" => array("itemid"),
        "hostids" => $hostid,
        "search" => array("name" => "Runtime remaining") //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "<td>No data</td>";
      } else {
        $params = array(
          "output" => array("lastvalue"),
          "itemids" => $itemid
        );

        //call api history.get with params
        $result = $zbx->call('item.get', $params);
        foreach ($result as $row) {
          $runtime = $row["lastvalue"];
          $runtimeduration = secondsToHMS($runtime);
          print "<td>$runtimeduration</td>";
        }
      }
      ?>
    </tr>
    <tr>
      <th>Run on Time Remaining</th>
      <?php
      $params = array(
        "output" => array("itemid"),
        "hostids" => $hostid,
        "search" => array("name" => "time on battery") //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        $itemid = $item["itemid"];
      }

      if (isset($itemid) == false) {
        print "<td>No data</td>";
      } else {
        $params = array(
          "output" => array("lastvalue"),
          "itemids" => $itemid
        );

        //call api history.get with params
        $result = $zbx->call('item.get', $params);
        foreach ($result as $row) {
          $remaining = $row["lastvalue"];
          $remainingduration = secondsToHMS($remaining);
          print "<td>$remainingduration</td>";
        }
      }
      ?>
    </tr>
    </tr>
  </table>
</body>

</html>