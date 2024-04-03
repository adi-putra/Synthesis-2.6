<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET["hostid"];
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();
//display time format
$diff = $timetill - $timefrom;

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
  return $dtF->diff($dtT)->format('%y-%m-%d %h:%i:%s');
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
  <table id="backup_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Time</th>
        <th>SQL Backup Failed</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $id = 1;
      $params = array(
        "output" => array("itemid", "name", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("name" => 'Event Log (Backup Failed)'), //seach id contains specific word
        "sortfield" => "itemid",
        "sortorder" => "ASC"
      );
      //call api method
      $result = $zbx->call('item.get', $params);


      foreach ($result as $item) {
        $itemid = $item["itemid"];
        $itemname = $item["name"];
        $itemval = $item["lastvalue"];
      }
      $params = array(
        "output" => array("value", "itemid", "clock"),
        "itemids" => $itemid,
        "history" => 2,
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill
      );

      $result = $zbx->call('history.get', $params);


      foreach ($result as $backup) {
        $backupval = $backup["value"];
        $backupid = $backup["itemid"];
        $backuptime = date("Y-m-d\ H:i:s\ ", $backup["clock"]);
        print "<tr>";
        print "<td>$id</td>";
        print "<td style=\"width: 100px;\">$backuptime</td>";
        print "<td>$backupval</td>";
        print "</tr>";
        $id++;
      }

      ?>
    </tbody>
  </table>
</body>

</html>

<!-- page script -->
<script type="text/javascript">
  $(function() {
    $("#backup_table").dataTable();
  });
</script>