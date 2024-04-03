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
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Host</th>
                <th>Total SQL Backup Fail</th>
            </tr>
        </thead>
        <tbody>

<?php

$count = 1;

foreach ($hostid as $hostID) {

    print "<tr>";

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
        if ($item["lastclock"] == 0 and $item["lastvalue"] == "") {
            $count_value = 0;
        }
        else {
            $params = array(
            "output" => array("value", "itemid", "clock"),
            "itemids" => $item["itemid"],
            "history" => 2,
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill,
            "countOutput" => true
            );
            $count_value = $zbx->call('history.get', $params);
        } 
    }

    print "<td>$gethostname</td>";
    print "<td>$count_value</td>";

    print "<tr>";
}
?>
        </tbody>
    </table>
</body>
</html>

<script>
	

    countcheck = countcheck + 1;
    if (countcheck == 13) {
        $("#reportready").html("Report is ready!");
        $('#chooseprint').show();
        $('#reportdiv').show();
    }
    else {
        $("#reportready").html("Generating report...(" + countcheck + "/13)");
    }
</script>