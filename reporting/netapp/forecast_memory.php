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
            "search" => array("name" => "forecast memory"), //seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get', $params);
            if (empty($result)) {
                print '<table class="table table-bordered">
                        <tr>
                            <th class="text-center" colspan="2">Forecast Used Memory</th>
                        </tr>
                        <tr>
                            <td>No data</td>
                        </tr>
                        </table>';
            }
            else {
                print '<table class="table table-bordered">
                        <tr>
                            <th class="text-center" colspan="2">Forecast Used Memory</th>
                        </tr>';

                foreach ($result as $item) {
                    print "<tr>";
                    print "<td>";
                    print $item["name"];
                    print "</td>";
                    print "<td>";
                    print number_format((float)$item["lastvalue"], 2, '.', '')." %";
                    print "</td>";
                    print "</tr>";
                }

                print '</table>';
            }
        }
    ?>
    
<!--<div id="chart">
</div>-->

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