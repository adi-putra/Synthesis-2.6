<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

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
        <table id="problem_table" class="table table-bordered table-striped">
            <thead>
                <tr>
                <th>Problem Time</th>
                <th>Host</th>
                <th>Problem</th>
                <th>Severity</th>
                <th>Acknow.</th>
                </tr>
            </thead>

            <?php
            //get hostname
            $params = array(
                "output" => array("name"),
                "hostids" => $hostid
            );
            //call api method
            $host = $zbx->call('host.get',$params);
            $hostname = $host[0]['name'];

            $params = array(
               // "output" => array("clock","name","severity","acknowledged","eventid","objectid"),
                "hostids" => $hostid,
                "hostids" => $hostid,
                "groupids" => $groupid,
                "selectAcknowledges" => array("userid", "action", "clock", "message", "old_severity", "new_severity"),
                "time_from" => $timefrom,
                "time_till" => $timetill
            );
            //call api method
            $result = $zbx->call('problem.get',$params);
            echo "<pre>";
            print_r($result);
            echo "</pre>";

            print "<tr>";
            foreach($result as $data){


                date_default_timezone_set("Asia/Kuala_Lumpur");
                $clock = date("Y-m-d H:i:s", $data['clock']);
                $problem = $data['name'];
                $severity = $data['severity'];
                $acknowledged = $data['acknowledged'];

                $eventId = $data['eventid'];
                $objectids = $data['objectid'];
                
                if($severity == 4){
                    
                    if($acknowledged == 0){

                    $acknowledged = "No";

                    }

                    print "<td>$clock</td>";
                    print "<td>$hostname</td>";
                    print "<td>$problem</td>";
                    print "<td><button class='btn btn-danger'>High</button></td>";
                    print "<td>$acknowledged</td>";
                }
            }
            print "</tr>";
            ?> 
        </table>
    </div> 

    <script type="text/javascript">
        $(function () {
        $("#problem_table").dataTable();
        });
  </script>
</body>
</html>