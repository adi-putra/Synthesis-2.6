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

<table id="fail_job_table" class="table table-bordered table-hover">
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
    $result1 = $zbx->call('host.get', $params);
    foreach ($result1 as $host) {
        $gethostname = $host["name"];
    }

    //get number of fail jobs
    $params = array(
    "output" => "extend",
    "hostids" => $hostID,
    "search" => array("name" => "Job ID:")
    );
    //call api method
    $result2 = $zbx->call('item.get', $params);
    foreach ($result2 as $item) {
        $params = array(
        "output" => "extend",
        "history" => 4,
        "itemids" => $item["itemid"],
        "time_from" => $timefrom,
        "time_till" => $timetill
        );
        //call api method
        $result3 = $zbx->call('history.get', $params);
        foreach ($result3 as $history) {
            $clock = date("Y-m-d h:i:s A", $history["clock"]);
            $details = $history["value"];

            //print details in table
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
    var fail_job_table = $('#fail_job_table').DataTable({
        order: [ 1, 'desc' ]
    } );
</script>

</body>
</html>