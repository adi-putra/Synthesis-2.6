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
    <!-- small box -->
    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "hostids" => $hostid,
                        "acknowledged" => true,
                        "countOutput" => true
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo "<i class='fas fa-thumbs-up'></i>&nbsp;&nbsp;&nbsp;".$result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableYes()" class="small-box-footer">
                    Acknowledged
                </a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3 style="text-align: center;">
                    <?php
                    $params = array(
                        "hostids" => $hostid,
                        "acknowledged" => false,
                        "countOutput" => true
                        );
                    //call api method
                    $result = $zbx->call('problem.get',$params);

                    echo "<i class='fas fa-thumbs-down'></i>&nbsp;&nbsp;&nbsp;".$result;
                    ?>
                    </h3>
                </div>
                <a href="javascript:;" onclick="searchTableNo()" class="small-box-footer">
                    Unacknowledged
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