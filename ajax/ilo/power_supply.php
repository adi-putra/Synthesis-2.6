<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];
$groupid = $_GET["groupid"];

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
	<table id="powersupply_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Host</th>
        <th>Name</th>
        <th style="text-align: center;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($hostid as $hostID) {
        $params = array(
        "output" => array("hostid", "name"),
        "hostids" => $hostID
        );
        //call api method
        $result = $zbx->call('host.get',$params);
        foreach ($result as $host) {
            $gethostname = $host["name"];
        }
        $params = array(
        "output" => array("itemid", "name", "lastvalue", "error"),
        "hostids" => $hostID,
        "search" => array("key_" => "sensor.psu.status")//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
          $psustatus = $item["lastvalue"];
          $psuname = substr($item["name"], 0, -20);
          print "<td><a href='hostdetails_ilo.php?hostid=".$hostID."' target='_blank'>$gethostname</a></td>";
          print "<td>$psuname</td>";
          if ($item["error"] !== "") {
            print "<td><button class='btn btn-block btn-danger btn-sm'>".$item["error"]."</button></td></tr>";  
          }
          else {
            if (is_numeric($psustatus)) {
                if ($psustatus == 1) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>other</button></td></tr>";
                }
                else if ($psustatus == 2) {
                  print "<td><button class='btn btn-block btn-success btn-sm'>ok</button></td></tr>";
                }
                else if ($psustatus == 3) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>degraded</button></td></tr>";
                }
                else if ($psustatus == 4) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>failed</button></td></tr>";
                }
                else {
                    print "<td><button class='btn btn-block btn-danger btn-sm'>no data</button></td></tr>";  
                }
              }
              else {
                if ($psustatus == "Normal") {
                  print "<td><button class='btn btn-block btn-success btn-sm'>$psustatus</button></td></tr>";
                } 
              }
          }
        }
      }
    ?>
    </tbody>
  </table>
</body>
</html>

<!-- page script -->
<script type="text/javascript">
  $(function () {
    $("#powersupply_table").dataTable();
  });
</script>