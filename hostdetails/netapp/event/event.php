<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];
$timefrom = $_GET['timefrom'] ?? time() - 600;
$timetill = $_GET['timetill'] ?? time();
//display time format
$diff = $timetill - $timefrom;

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
	);
//call api method
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2) { 
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

	$bytes = max($bytes, 0); 
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	$pow = min($pow, count($units) - 1); 

	// Uncomment one of the following alternatives
	$bytes /= pow(1024, $pow);
	// $bytes /= (1 << (10 * $pow)); 

	return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>

    <div style="overflow-x:auto;">
        <table id="event_table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Event Log</th>
                </tr>
            </thead>
            <tbody>

        <?php

            // $params = array(
            // "output" => array("name","lastvalue"),
            // "hostids" => $hostid
            // );

            // $app = $zbx->call('application.get',$params);

            // foreach($app as $data){

            //     if($data['name'] == 'Events API'){

            //         $applicationid = $data['applicationid'];

            //     }
            // }

            //get item
            $params = array(
                "output" => array("itemid", "name", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("name" => "Event API"),
                "sortfield" => "itemid",
                "sortorder" => "ASC"
            );
            //call api method
            $result = $zbx->call('item.get',$params);
            //call api method
            if (!empty($result))  {
                foreach ($result as $item) {
                    $itemid = $items["itemid"];
                    $itemname = $items["name"];
                    $itemval = $items["lastvalue"];
                }
            }
            
            $params = array(
                "output" => array("value", "itemid", "clock"),
                "itemids" => $itemid,
                "history" => 2,
                "sortfield" => "clock",
                "sortorder" => "DESC",
                "limit" => 50
            );
            $result = $zbx->call('history.get', $params);
            foreach ($result as $syslog) {
                $syslogval = $syslog["value"];
                $syslogid = $syslog["itemid"];
                $syslogtime = date("Y-m-d\ H:i:s A\ ", $syslog["clock"]);
                print "<tr>";
                print "<td style='width: 100px;'>$syslogtime</td>";
                print "<td>$syslogval</td>";
                print "</tr>";
            }
        ?>
            </tbody>
        </table>
    </div>

    <!-- page script -->
    <script type="text/javascript">
    $(function () {
        $("#event_table").DataTable(
        {
            dom: 'Bfrtip',
            buttons: [
                'colvis'
            ]
        } 
        );
    });
    </script>
    </body>
</html>