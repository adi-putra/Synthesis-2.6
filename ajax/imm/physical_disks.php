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
	<table id="physicaldisk_table" class="table table-bordered table-striped">
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

        $id = 1;
        $params = array(
        "output" => array("name", "lastvalue", "key_"),
        "hostids" => $hostID,
        "search" => array("name" => "physical disk status")//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
            //media types
            $pdstatus = $item["lastvalue"];
            $pdname = $item["name"];

            print "<tr><td><a href='hostdetails_imm.php?hostid=".$hostID."' target='_blank'>$gethostname</a></td>";
            print "<td>Disk $pdname</td>";
            
            if ($pdstatus == "Normal") {
              print "<td><button class='btn btn-block btn-success btn-sm'>$pdstatus</button></td></tr>";
            }
            else {
              print "<td><button class='btn btn-block btn-danger btn-sm'>$pdstatus</button></td></tr>";
            }
        }      
      $id++;      
      }
      ?>
    </tbody>
  </table>
</body>
</html>

<!-- page script -->
<script type="text/javascript">
  $(function () {
    $("#physicaldisk_table").dataTable();
  });
</script>