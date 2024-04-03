<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

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
	<table id="hypepower" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th style="text-align: center;">Hypervisor IP</th>
        <th style="text-align: center;">Power State</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        foreach ($hostid as $hostID) {
            //get hostname
            $params = array(
                "output" => array("name"),
                "hostids" => $hostID
                );
            //call api method
            $result = $zbx->call('host.get',$params);
            foreach ($result as $host) {
                $hostname = $host["name"];
            }

            $params = array(
              "output" => array("name", "lastvalue", "key_"),
              "hostids" => $hostID,
              "search" => array("name" => "VMware: Hypervisor name")//seach id contains specific word
              );
              //call api method
              $result = $zbx->call('item.get',$params);
              foreach ($result as $item) {
                  $itemname = $item["name"];
                  $hypervisorName = $item["lastvalue"];
              }

            $params = array(
            "output" => array("name", "lastvalue", "key_"),
            "hostids" => $hostID,
            "search" => array("name" => "VMware: Power state")//seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {
                $itemname = $item["name"];
                $powerState = $item["lastvalue"];

                print "<tr>
                    <td><a href='hostdetails_vm.php?hostid=".$hostID."' target='_blank'>$hostname: </a>$itemname</td>
                    <td>$hypervisorName</td>";
                
                if ($powerState == "1") {
                print "<td><button class='btn btn-block btn-success btn-sm'>Powered on</button></td></tr>";
                }
                else if ($powerState == "") {
                print "<td><button class='btn btn-block btn-info btn-sm'>No data</button></td></tr>";
                }
                else {
                print "<td><button class='btn btn-block btn-danger btn-sm'>Powered off</button></td></tr>";
            }

            $id++;
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
    $("#hypepower").DataTable({
        "order": [
            [2, "asc"]
        ]
    });
  });
</script>