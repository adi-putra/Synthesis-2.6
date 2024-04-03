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
	<table id="interfaces_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Type</th>
        <th style="text-align: center;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        $params = array(
        "output" => array("itemid", "name", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("key_" => "net.if.type[ifType")//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
          $interface_type = $item["lastvalue"];
          $interface_name = substr($item["name"], 0, -16);
          
          print "<tr>";
          print "<td>$id</td>";
          print "<td>$interface_name</td>";
          
          if ($interface_type == 1) {
            $interface_type = "other";
          }
          else if ($interface_type == 6) {
            $interface_type = "ethernetCsmacd";
          }
          else if ($interface_type == 53) {
            $interface_type = "propVirtual";
          }

          print "<td>$interface_type</td>";

          $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "operational status")//seach id contains specific word
          );
          //call api method
          $result1 = $zbx->call('item.get',$params);
          foreach ($result1 as $switch) {
            if ($switch["lastvalue"] == 1) {
              $interface_status = "<button class='btn btn-block btn-success btn-sm'>up</button>";
            }
            else if ($switch["lastvalue"] == 2) {
              $interface_status = "<button class='btn btn-block btn-danger btn-sm'>down</button>";
            }
            else if ($switch["lastvalue"] == 3) {
              $interface_status = "<button class='btn btn-block btn-info btn-sm'>absent</button>";
            }
          }
          print "<td>$interface_status</td></tr>";
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
    $("#interfaces_table").dataTable();
  });
</script>