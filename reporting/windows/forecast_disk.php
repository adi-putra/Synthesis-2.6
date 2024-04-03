<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//$hostid = $_GET["hostid"] ?? array("10361", "10324");
$groupid = $_GET["groupid"];
$hostid = $_GET["hostid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    $daycount = $dtF->diff($dtT)->format('%d');
    $yearcount = $dtF->diff($dtT)->format('%y');
    $weekcount = (int)ltrim($daycount / 7, '0.'); // convert to int -> divide days by 7 -> trim decimal  

    // If year is 0 then trim year, if day is more than 7 then convert to weeks
    if ($yearcount == 0 && $daycount > 7) {
        return $dtF->diff($dtT)->format('%m month(s), ' . $weekcount . ' week(s)');
    } else if ($yearcount == 0 && $daycount <= 7) {
        return $dtF->diff($dtT)->format('%m month(s), %d day(s)');
    } else if ($daycount > 7) {
        return $dtF->diff($dtT)->format('%y year(s), %m month(s), ' . $weekcount . ' week(s)');
    } else {
        return $dtF->diff($dtT)->format('%y year(s), %m month(s), %d day(s)');
    }

    // return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

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



//declare array of data
$data_arr = [];

foreach ($hostid as $hostID) {

    //get hostname
    $params = array(
        "output" => array("name"),
        "hostids" => $hostID, //find by hostid
    );

    $result = $zbx->call('host.get',$params);
    foreach ($result as $host) {
        $hostname = $host["name"];
    }

    $params = array(
        "output" => array("name", "lastvalue", "key_"),
        "hostids" => $hostID, //find by hostid
        "search" => array("key_" => "vfs.fs.forecast.30d")
    );

    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $lastvalue =  round($item["lastvalue"],2);

        $data_arr[] = array(
            "itemname" => $hostname." : ".$item["name"],
            "lastvalue" => $lastvalue
        );
    }
}

//sort array by lastvalue
usort($data_arr, function ($a, $b) {
    return $a['lastvalue'] <=> $b['lastvalue'];
});
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title></title>
</head>
<body>
    <table class="table table-bordered">
        <tr>
            <th>Host</th>
            <th>Forecast Free Space in 30 days</th>
        </tr>
        <?php
        foreach ($data_arr as $data) {
            print "<tr>";
            print "<td>".$data["itemname"]."</td>";
            print "<td>".formatBytes($data["lastvalue"])."</td>";
            print "</tr>";
        }
        ?>
    </table>
</body>
</html>

<script>
countcheck = countcheck + 1;
if (countcheck == countcheck_total) {
    $("#reportready").html("Report is ready!");
    $('#chooseprint').show();
    $('#reportdiv').show();
}
else {
    $("#reportready").html("Generating report...(" + countcheck + "/" + countcheck_total + ")");
}
</script>