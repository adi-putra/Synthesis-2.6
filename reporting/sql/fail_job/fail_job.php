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
                <th>Total Fail Job</th>
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
    "search" => array("name" => "Job ID:"),
    "countOutput" => true
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    
    print "<td>$gethostname</td>";
    print "<td>$result</td>";

    print "</tr>";
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