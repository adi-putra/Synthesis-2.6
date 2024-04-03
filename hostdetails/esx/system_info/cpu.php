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
	//   $bytes /= pow(1024, $pow);
//   $bytes /= (1 << (10 * $pow)); 

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
      <th>CPU Frequency</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "CPU frequency") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $cpu_freq = round($item["lastvalue"]/1000000000, 2)." GHz";
          }
        }
        print $cpu_freq;
        ?>
      </td>
    </tr>
    <tr>
      <th>CPU Model</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "CPU model") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $cpu_model = $item['lastvalue'];
          }
        }
        print $cpu_model;
        ?>
      </td>
    </tr>
    <tr>
      <th>CPU Cores</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "CPU cores") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $cpu_cores = $item['lastvalue'];
          }
        }
        print $cpu_cores;
        ?>
      </td>
    </tr>
    <tr>
      <th>CPU Threads</th>
      <td>
        <?php
        //Number of Disks
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "CPU threads") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $cpu_thread = $item['lastvalue'];
          }
        }
        print $cpu_thread;
        ?>
      </td>
    </tr>
  </table>

</body>

</html>