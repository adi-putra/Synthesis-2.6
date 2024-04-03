<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
//$httpid = $_GET['httpid'];
$webgroup = $_GET['webgroup'];
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

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
        <meta charset="UTF-8">
        <title></title>
    </head>

    <body>

	<table id="tbl_err_log" class="table table-bordered table-striped">
        <thead>
            <tr>
            <th>Time</th>
            <th>Windows System Log</th>
            </tr>
        </thead>
        <tbody>
            
            <?php 
                $params = array(
                    "output" => "extend",
                    "selectSteps" => "extend",
                    "selectTags" => "extend",
                    "search" => array("name" => $webgroup)
                );
                
                $result = $zbx->call('httptest.get', $params);
                foreach($result as $httptest){
                
                    $http_id = $httptest['httptestid'];
                    $host_id = $httptest['hostid'];
                
                    //get hostname for each web
                    $params = array(
                        "output" => "extend",
                        "hostids" => $host_id,
                        "webitems" => true,
                        "search" => array("name"=> "Last error message of scenario")
                    );
                
                    $res_item = $zbx->call('item.get', $params);
                    if(!empty($res_item)){
                        foreach($res_item as $item){
                            if (strpos($item['name'], $webgroup) !== false){
                                
                                $itemid = $item['itemid'];
            
                                $params = array(
                                    "output" => "extend",
                                    "itemids" => $itemid,
                                    "history" => 1,
                                    "time_from" => $timefrom,
                                    "time_till" => $timetill,
                                    "sortfield" => "clock",
                                    "sortorder" => "DESC",
                                    "limit" => 50,
                                    //"webitems" => true
                                );
                            
                                $res_history = $zbx->call('history.get', $params);

                                if(!empty($res_history)){
                                    foreach ($res_history as $syslog) {
                                        $syslogval = $syslog["value"];
                                        $syslogid = $syslog["itemid"];
                                        $syslogtime = date("Y-m-d\ H:i:s A\ ", $syslog["clock"]);
                                        print "<tr>";
                                        print "<td style=\"width: 100px;\">$syslogtime</td>";
                                        print "<td>$syslogval</td>";
                                        print "</tr>";
                                    }
                                }
                                
                            }
                        }
                    }
                }
            ?>
        </tbody>
    </table>
    <script type="text/javascript">
      $(function () {
        $("#tbl_err_log").dataTable();
      });
    </script>
</body>

</html>