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
	<table id="dbstatus_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Database</th>
        <!-- <th>Type</th> -->
        <th style="text-align: center;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        $params = array(
        "output" => array("itemid", "name", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("key_" => '_state,{$ODBC}]'),//seach id contains specific word
       
        );
        //call api method
        $result = $zbx->call('item.get',$params);

        // $lastvalue = array();
        foreach ($result as $item) { 
          // print_r ($item);
          // $lastvalue[$item] = $row["lastvalue"];
          $dbstatus = $item["lastvalue"];
          $dbname = $item["name"];
          
          print "<tr>";
          print "<td>$id</td>";
          print "<td>$dbname</td>";
          
          if ($dbstatus == 0) {
            $dbstatus = "<button class='btn btn-block btn-success btn-sm'>ONLINE</button>";
          }
          else if ($dbstatus == 1) {
            $dbstatus = "<button class='btn btn-block btn-info btn-sm'>RESTORING</button>";
          }
          else if ($dbstatus == 2) {
            $dbstatus = "<button class='btn btn-block btn-info btn-sm'>RECOVERING</button>";
          }
          else if ($dbstatus == 3) {
            $dbstatus = "<button class='btn btn-block btn-warning btn-sm'>RECOVERY_PENDING</button>";
          }
          else if ($dbstatus == 4) {
            $dbstatus = "<button class='btn btn-block btn-danger btn-sm'>SUSPECT</button>";
          }
          else if ($dbstatus == 5) {
            $dbstatus = "<button class='btn btn-block btn-danger btn-sm'>EMERGENCY</button>";
          }
          else if ($dbstatus == 6) {
            $dbstatus = "<button class='btn btn-block btn-danger btn-sm'>OFFLINE</button>";
          }
          else if ($dbstatus == 7) {
            $dbstatus = "<button class='btn btn-block btn-danger btn-sm'>NOT EXIST</button>";
          }
          
          print "<td>$dbstatus</td></tr>";

          $id++;
        }
        // array_multisort($lastvalue, SORT_DESC, $result); 

        
        ?>
        
    </tbody>
  </table>
</body>
</html>

<!-- page script -->
<script type="text/javascript">
  $(document).ready(function() {
    $('#dbstatus_table').DataTable( {
        "order": [[ 2, "asc" ]]
    } );
} );
</script>