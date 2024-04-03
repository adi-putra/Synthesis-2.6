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

  <?php
  $params = array(
    "output" => array("itemid", "name", "lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => 'Job ID:'),
    "sortfield" => "name",
    "sortorder" => "DESC",
  );
  $result = $zbx->call('item.get', $params);

  foreach ($result as $item) {
    $itemid = $item["itemid"];
    $itemname = $item["name"];
    $itemval = $item["lastvalue"];
  }

  echo '<div class="form-group">
<select class="form-control" name="jobitem" id="jobitem" onchange="jobBtn()">';
  foreach ($result as $row) {
    $getitemid = $row["itemid"];
    echo ' <option value="' . $getitemid . '" >' . $row['name'] . '</option>';
    if (empty($result) || $result == "") {
      echo ' <option>No job item available</option>';
    }
  }
  echo '
</select>
</div>';


  echo '<div id="failtable">
  <table id="failjob_table" class="table table-bordered table-striped">
<thead>
  <tr>
  <th>No</th>
    <th>Time</th>
    <th>Job Fail Details</th>
  </tr>
</thead>
<tbody>
  ';

  $params = array(
    "output" => array("itemid", "name", "lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => 'Job ID:')
  );
  $result = $zbx->call('item.get', $params);

  foreach ($result as $item) {
    $itemid = $item["itemid"];
    $itemname = $item["name"];
    $itemval = $item["lastvalue"];

    $params = array(
      "output" => array("itemid", "clock", "value"),
      "history" => 4,
      "hostids" => $hostid,
      "itemids" => $itemid,
      "sortfield" => "clock",
      "sortorder" => "DESC",
      "search" => array("value" => 'Run Date:'),
      "time_from" => $timefrom,
      "time_till" => $timetill

    );
    $result = $zbx->call('history.get', $params);
  }

  $id = 1;

  foreach ($result as $row) {
    $failid = $row["itemid"];
    $failclock = date("Y-m-d\ H:i:s\ ", $row["clock"]);
    $failvalue = $row["value"];
    // echo "<tr><td>$id</td><td>$failid</td><td>$failclock</td><td>$failvalue</td></tr>";
    echo "<tr><td>$id</td><td>$failclock</td><td>$failvalue</td></tr>";


    $id++;
  }

  echo '
  </tbody>
  </table>
  </div>';







  ?>

</body>

</html>

<!-- page script -->
<script type="text/javascript">
  $(function() {
    $("#failjob_table").dataTable();
  });

  function jobBtn() {
    var hostid = <?php echo $hostid ?>;
    var timefrom = <?php echo $timefrom ?>;
    var timetill = <?php echo $timetill ?>;
    var jobid = $("#jobitem").val();
    $("#failtable").load("application/sql/issues/load_failtable.php?hostid=" + hostid + "&jobid=" + jobid + "&timefrom=" + timefrom + "&timetill=" + timetill);
    // alert (hostid + " and " + getitemid);
    // alert ($("#jobitem").val());
    // alert(jobid);
  }
</script>