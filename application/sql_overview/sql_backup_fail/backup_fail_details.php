<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"];
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<body>

<table id="backup_fail_table" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Host</th>
                <th>Date</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>

<?php

$count = 1;

foreach ($hostid as $hostID) {
    //get hostname
    $params = array(
    "output" => array("name"),
    "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    //get number of fail jobs
    $params = array(
    "output" => "extend",
    "hostids" => $hostID,
    "search" => array("name" => "Event Log (Backup Failed)")
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    foreach ($result as $item) {

        $params = array(
        "output" => array("value", "itemid", "clock"),
        "itemids" => $item["itemid"],
        "history" => 2,
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill
        );
        $result = $zbx->call('history.get', $params);
        foreach ($result as $history) {
            $clock = date("Y-m-d h:i:s A", $history["clock"]);
            $details = $history["value"];

            print "<tr>
                    <td>$gethostname</td>
                    <td>$clock</td>
                    <td>$details</td>
                    </tr>";
        }
    }
}
?>

        </tbody>
</table>

<script>
    //table settings
    var backup_fail_table = $('#backup_fail_table').DataTable({
        order: [ 1, 'desc' ]
    } );
</script>


</body>
</html>

