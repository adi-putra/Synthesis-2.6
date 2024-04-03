<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid
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
	<meta charset="UTF-8">
	<title></title>
</head>

<body>
	<table id="netcard_table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $params = array(
        "output" => array("itemid", "name", "key_", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("key_" => "net.if.status")//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        if (!empty($result)) {
            $count = 1;
            foreach ($result as $item) {
                $netcard = substr($item["name"], 0, -20);
                $itemvalue = $item["lastvalue"];
                if ($itemvalue > 0) {
                $netcardStatus = "<td style='color: green;'>Online</td>";
                }
                else {
                $netcardStatus = "<td style='color: red;'>Offline</td>";
                }

                print "<tr>";
                print "<td>$count</td>";
                print "<td>$netcard</td>";
                print $netcardStatus;
                print "</tr>";
                $count++;
            }
        }
        else {
            print "<td>No data</td>";
        }
        ?>
        </tbody>
  </table>
</body>

</html>

<script>
$(document).ready(function () {
    $('#netcard_table').DataTable();
});
</script>