<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10713");
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

//display time format
$diff = $timetill - $timefrom;

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
     $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    if ($seconds > 31556926) {
        return $dtF->diff($dtT)->format('%y years %m months %d days');
    }
    else {
        return $dtF->diff($dtT)->format('%m months %d days %h hours');
    }
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
        "output" => array("name"),
        "hostids" => $hostid
        );
        //call api method
        $result = $zbx->call('host.get', $params);
        foreach ($result as $host) {
            $gethostname = $host["name"];
        }

        $diskname = [];

        $params = array(
        "output" => array("itemid", "name", "error", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("key_" => "forecast.vfs.fs.size"), //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        foreach ($result as $item) {

            if (stripos($item["name"], "FSNAME") !== false) {
                continue;
            }
            else {
                $strlen = strlen($item["name"]);
                $point1 = strpos($item["name"], " on ") + 3;
                $point2 = $strlen - strpos($item["name"], " in ");

                $diskname[] = substr($item["name"], $point1, -$point2);
            }

            $diskname = array_values(array_unique($diskname));

        }

        if (empty($diskname)) {
            print '<table class="table table-bordered">
                        <tr>
                            <th class="text-center" colspan="2">Forecast Free Disk</th>
                        </tr>
                        <tr>
                            <td>No data</td>
                        </tr>
                    </table>';
        }
        else {
            for ($i=0; $i < count($diskname); $i++) {
            
                print '<table class="table table-bordered">';
                print '<thead>
                        <tr>
                            <th class="text-center" colspan="2"> Forecast Free disk space on '.$diskname[$i].'</th>
                        </tr>
                    </thead>';

                $params = array(
                "output" => array("itemid", "name", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "forecast disk full " . $diskname[$i]), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {

                    print "<tr>";
                    print "<td>Time taken to disk full</td>";
                    print "<td>";
                    print secondsToTime($item["lastvalue"]);
                    print "</td>";
                    print "</tr>";
                }
    
                $params = array(
                "output" => array("itemid", "name", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "Forecast Free disk space on" . $diskname[$i] . " in 30days"), //seach id contains specific word
                );
                //call api metdod
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    print "<tr>";
                    print "<td>";
                    print $item["name"];
                    print "</td>";
                    print "<td>";
                    print formatBytes($item["lastvalue"]);
                    print "</td>";
                    print "</tr>";
                }
    
                $params = array(
                "output" => array("itemid", "name", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "Forecast Free disk space on" . $diskname[$i] . " in 90days"), //seach id contains specific word
                );
                //call api metdod
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    print "<tr>";
                    print "<td>";
                    print $item["name"];
                    print "</td>";
                    print "<td>";
                    print formatBytes($item["lastvalue"]);
                    print "</td>";
                    print "</tr>";
                }
    
                $params = array(
                "output" => array("itemid", "name", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "Forecast Free disk space on" . $diskname[$i] . " in 120days"), //seach id contains specific word
                );
                //call api metdod
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    print "<tr>";
                    print "<td>";
                    print $item["name"];
                    print "</td>";
                    print "<td>";
                    print formatBytes($item["lastvalue"]);
                    print "</td>";
                    print "</tr>";
                }
    
                $params = array(
                "output" => array("itemid", "name", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "Forecast Free disk space on" . $diskname[$i] . " in 180days"), //seach id contains specific word
                );
                //call api metdod
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    print "<tr>";
                    print "<td>";
                    print $item["name"];
                    print "</td>";
                    print "<td>";
                    print formatBytes($item["lastvalue"]);
                    print "</td>";
                    print "</tr>";
                }
    
                $params = array(
                "output" => array("itemid", "name", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "Forecast Free disk space on" . $diskname[$i] . " in 365days"), //seach id contains specific word
                );
                //call api metdod
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    print "<tr>";
                    print "<td>";
                    print $item["name"];
                    print "</td>";
                    print "<td>";
                    print formatBytes($item["lastvalue"]);
                    print "</td>";
                    print "</tr>";
                }
    
                print "</table>";
            }
        }
    ?>


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