<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];
$jobid = $_GET["jobid"];
$timefrom = $_GET['timefrom'];
$timetill = $_GET['timetill'];
//display time format
$diff = $timetill - $timefrom;

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%y-%m-%d %h:%i:%s');
}



// if (isset($_POST["job_id"])) {
// $output = '<tr><td>'.rand(1,100).'</td><td>'.rand(1,100).'</td><td>'.rand(1,100).'</td></tr>';
// $output = '<h3>You did it!</h3>';
// echo "<script> alert('You did it!'); </script>";

$params = array(
  "output" => array("itemid", "name", "lastvalue"),
  "hostids" => $hostid,
  "search" => array("name" => 'Job ID:')
);
$result = $zbx->call('item.get', $params);

foreach ($result as $item) {
  $itemid = $item["itemid"];
  // $itemname = $item["name"];
  // $itemval = $item["lastvalue"];

  $params = array(
    "output" => array("itemid", "clock", "value"),
    "history" => 4,
    "hostids" => $hostid,
    "itemids" => $jobid,
    "sortfield" => "clock",
    "sortorder" => "DESC",
    "search" => array("value" => 'Run Date:'),
    "time_from" => $timefrom,
    "time_till" => $timetill

  );
  $result = $zbx->call('history.get', $params);
}
echo '
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
$id = 1;
foreach ($result as $row) {
  $failid = $row["itemid"];
  $failclock = date("Y-m-d\ H:i:s\ ", $row["clock"]);
  $failvalue = $row["value"];
  // echo '<tr><td>' . $id . '</td><td>' . $failid . '</td><td>' . $failclock . '</td><td>' . $failvalue . '</td></tr>';
  echo '<tr><td>' . $id . '</td><td>' . $failclock . '</td><td>' . $failvalue . '</td></tr>';


  $id++;
}
echo '
</tbody>
</table>';

?>

<script type="text/javascript">
  $(function() {
    $("#failjob_table").dataTable();
  });
</script>