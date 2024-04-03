<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get values
$hostid = $_GET['hostid'];
$groupid = $_GET["groupid"];
$timefrom = $_GET['timefrom'] ?? strtotime('today');
$timetill = $_GET['timetill'] ?? time();

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
    <!-- small box -->
    <div class="row">
        <div class="col-md-2">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "groupids" => $groupid,
                        "hostids" => $hostid,
                        "severities" => 5,
                        "countOutput" => true,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo $result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableDisaster()" class="small-box-footer">
                    Disaster
                </a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box" style="background-color: tomato; color: white;">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "groupids" => $groupid,
                        "hostids" => $hostid,
                        "severities" => 4,
                        "countOutput" => true,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo $result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableHigh()" class="small-box-footer">
                    High
                </a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box" style="background-color: #ff6600; color: white;">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "groupids" => $groupid,
                        "hostids" => $hostid,
                        "severities" => 3,
                        "countOutput" => true,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo $result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableAverage()" class="small-box-footer">
                    Average
                </a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box" style="background-color: orange; color: white;">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "groupids" => $groupid,
                        "hostids" => $hostid,
                        "severities" => 2,
                        "countOutput" => true,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo $result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableWarning()" class="small-box-footer">
                    Warning
                </a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "groupids" => $groupid,
                        "hostids" => $hostid,
                        "severities" => 1,
                        "countOutput" => true,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo $result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableInfo()" class="small-box-footer">
                    Info
                </a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box" style="background-color: gray; color: white;">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "groupids" => $groupid,
                        "hostids" => $hostid,
                        "severities" => 0,
                        "countOutput" => true,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo $result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableUnclassified()" class="small-box-footer">
                    Unclassified
                </a>
            </div>
        </div>
    </div>
	<?php
  //get last value of cpu utilization
  /*$params = array(
    "output" => array("lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => "cpu percentage")
    );
  //call api method
  $result = $zbx->call('item.get',$params);
  foreach ($result as $item) {
    $current_cpu = $item["lastvalue"];
  }

  echo round($current_cpu, 2)." %";*/
  ?>
</body>
</html>

<script>
//table.columns(3).search("info").draw();
</script>