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
	<table id="diskarrays_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th style="text-align: center;">Controller Status</th>
        <th style="text-align: center;">Cache Controller Status</th>
        <th style="text-align: center;">Battery Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        $key = "system.hw.diskarray.status[cpqDaCntlrCondition.";
        $params = array(
        "output" => array("name", "lastvalue", "key_"),
        "hostids" => $hostid,
        "search" => array("key_" => $key)//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
          $daName = substr($item["key_"], 47);
          $daName = substr($daName, 0,-1);
          $da_CStatus = $item["lastvalue"];

          print "<tr><td>$id</td>";
          print "<td>$daName</td>";

            if ($da_CStatus == 1) {
            print "<td><button class='btn btn-block btn-info btn-sm'>other</button></td>";
            }
            else if ($da_CStatus == 2) {
              print "<td><button class='btn btn-block btn-success btn-sm'>ok</button></td>";
            }
            else if ($da_CStatus == 3) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>degraded</button></td>";
            }
            else if ($da_CStatus == 4) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>failed</button></td>";
            }

          $key = "system.hw.diskarray.cache.status[cpqDaAccelStatus.".$daName."]";

          $params = array(
          "output" => array("name", "lastvalue", "key_"),
          "hostids" => $hostid,
          "search" => array("key_" => $key)//seach id contains specific word
          );
          //call api method
          $result1 = $zbx->call('item.get',$params);
          foreach ($result1 as $item1) {
            $da_CCStatus = $item1["lastvalue"];

            if ($da_CCStatus == 1) {
            print "<td><button class='btn btn-block btn-info btn-sm'>other</button></td>";
            }
            else if ($da_CCStatus == 2) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>invalid</button></td>";
            }
            else if ($da_CCStatus == 3) {
              print "<td><button class='btn btn-block btn-success btn-sm'>enabled</button></td>";
            }
            else if ($da_CCStatus == 4) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>tmpDisabled</button></td>";
            }
            else if ($da_CCStatus == 5) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>permDisabled</button></td>";
            }
            else if ($da_CCStatus == 6) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>cacheModFlashMemNotAttached/button></td>";
            }
            else if ($da_CCStatus == 7) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>cacheModDegradedFailsafeSpeed</button></td>";
            }
            else if ($da_CCStatus == 8) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>cacheModCriticalFailure</button></td>";
            }
            else if ($da_CCStatus == 9) {
              print "<td><button class='btn btn-block btn-danger btn-sm'>cacheReadCacheNotMapped</button></td>";
            }
            
            $key = "system.hw.diskarray.cache.battery.status[cpqDaAccelBattery.".$daName."]";
            $params = array(
            "output" => array("name", "lastvalue", "key_"),
            "hostids" => $hostid,
            "search" => array("key_" => $key)//seach id contains specific word
            );
            //call api method
            $result2 = $zbx->call('item.get',$params);
            foreach ($result2 as $item2) {
              $da_BattStatus = $item2["lastvalue"];
              if ($da_BattStatus == 1) {
                print "<td><button class='btn btn-block btn-info btn-sm'>other</button></td>";
              }
              else if ($da_BattStatus == 2) {
                print "<td><button class='btn btn-block btn-success btn-sm'>ok</button></td>";
              }
              else if ($da_BattStatus == 3) {
                print "<td><button class='btn btn-block btn-info btn-sm'>recharging</button></td>";
              }
              else if ($da_BattStatus == 4) {
                print "<td><button class='btn btn-block btn-danger btn-sm'>failed</button></td>";
              }
              else if ($da_BattStatus == 5) {
                print "<td><button class='btn btn-block btn-danger btn-sm'>degraded</button></td>";
              }
              else if ($da_BattStatus == 6) {
                print "<td><button class='btn btn-block btn-danger btn-sm'>not present</button></td>";
              }
              else if ($da_BattStatus == 7) {
                print "<td><button class='btn btn-block btn-danger btn-sm'>capacitor failed</button></td>";
              }
            }
          }
          $id++;
        }  
      ?>
    </tr>
    </tbody>
  </table>
</body>
</html>

<!-- page script -->
<script type="text/javascript">
  $(function () {
    $("#diskarrays_table").DataTable();
  });
</script>