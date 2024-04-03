<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"] ?? array("10338", "10341", "10348", "10349", "10351" , "10352");
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

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

//data array
$logfile = array();
$count = 0;

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

    //get data file size
    $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("name" => "Total Log File Size"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    foreach ($result as $item) {
        $logfile[] = array(
            "database" => $gethostname,
            "value" => $item["lastvalue"]
        );
    }
}

//sort array by lastvalue
usort($logfile, function($a, $b) {
    return ($a["value"] > $b["value"])?-1:1;
});

//arsort($logfile);
//print json_encode($logfile);
?>

<html>
    <head>

    </head>
    <body>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Host</th>
                    <th>Total Log File Size</th>
                </tr>
            </thead>
            <tbody>
        <?php
            foreach ($logfile as $logf) {
                print "<tr>";

                print "<td>".$logf["database"]."</td>";
                print "<td>".formatBytes($logf["value"])."</td>";

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