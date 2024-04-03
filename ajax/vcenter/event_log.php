<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

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
	<table id="eventlog" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Host</th>
        <th>Timestamp</th>
        <th>Local Time</th>
        <th>Value</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        foreach ($hostid as $hostID) {
          $params = array(
          "output" => array("name"),
          "hostids" => $hostID
          );
      
          //call api method
          $result = $zbx->call('host.get', $params);
      
          foreach ($result as $host) {
            // $gethostname = $host["name"];
            $gethostname = str_replace("Zabbix","Synthesis",$host["name"]);
          }

          $params = array(
              "output" => array("itemid", "name"),
              "hostids" => $hostID,
              "search" => array("name" => "event log")
              );
          //call api method
          $result = $zbx->call('item.get',$params);
          foreach ($result as $item) {
              if (stripos($item["name"], "VMWare:") !== false) {
                  continue;
              }
              else {
                  $itemid = $item["itemid"];
              }
          }

          //GET EVENT LOG
          $params = array(
              "itemids" => $itemid,
              "history" => 2,
              "time_from" => $timefrom,
              "time_till" => $timetill,
              "sortfield" => array("clock"),
              "sortorder" => "DESC",
              "limit" => 50
              );
          //call api method
          $result = $zbx->call('history.get',$params);
          foreach ($result as $hist) {
              $timestamp = date("Y-m-d\ H:i:s A\ ", $hist["timestamp"]);
              $localtime = date("Y-m-d\ H:i:s A\ ", $hist["clock"]);
              print "<tr>";
              print "<td><a href='hostdetails_vcenter.php?hostid=".$hostID."'>".$gethostname."</a></td>";
              print "<td>".$timestamp."</td>";
              print "<td>".$localtime."</td>";
              print "<td>".$hist["value"]."</td>";
              print "</tr>";
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
    $("#eventlog").DataTable({
        "order": [
            [0, "desc"]
        ]
    });
  });
</script>