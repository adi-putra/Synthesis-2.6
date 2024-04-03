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

            $params = array(
            "output" => array("itemid", "name", "error", "lastvalue"),
            "hostids" => $hostid,
            "search" => array("name" => "Forecast Used Memory in 30 days"), //seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get', $params);
            if (empty($result)) {
                print '<p class="text-danger">No data</p>';
            }
            else {
                print '<table class="table table-bordered table-striped">
                        <tr>
                            <th class="text-center" colspan="2">Forecast Used Memory</th>
                        </tr>';

                foreach ($result as $item) {

                    $lastvalue = number_format((float)$item["lastvalue"], 2, '.', '');
                    $label = $lastvalue;

                    if ($lastvalue > 70) {
                        $status = "red";
                        $loader = "progress-bar progress-bar-danger";
                    } else if ($lastvalue > 30 && $lastvalue <= 70) {
                        $status = "orange";
                        $loader = "progress-bar progress-bar-warning";
                    } else if ($lastvalue > 0 && $lastvalue <= 30) {
                        $status = "seagreen";
                        $loader = "progress-bar progress-bar-success";
                    } else if ($lastvalue <= 0.00) {
                        $status = "seagreen";
                        $loader = "progress-bar progress-bar-success";
                        $label = "0.00";
                        $lastvalue = 1;
                    }
                

                    print '<tr>
                        <th>'.$item["name"].'</th>
                        <td style="color: white; background-color: ' . $status . ';">' . $label . ' %</td>
                        </tr>';

                }

                print '</table>';
            }
        }
    ?>
    
<!--<div id="chart">
</div>-->

</body>
</html>