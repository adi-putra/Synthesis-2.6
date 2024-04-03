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
	<table id="virtualmac" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th style="text-align: center;">Power State</th>
        <th style="text-align: center;">Guest State</th>
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
            "search" => array("key_" => "vmwVMState")//seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {
                $itemname = substr($item["name"], 0, -11);
                $powerState = $item["lastvalue"];
                $itemkeyno = substr($item["key_"], 10);

                print "<tr>
                    <td>$id</td>
                    <td><a href='hostdetails_esx.php?hostid=$hostID'>$hostname: </a>$itemname</td>";
                
                if ($powerState == "powered on") {
                print "<td><button class='btn btn-block btn-success btn-sm'>$powerState</button></td>";
                }
                else if ($powerState == "") {
                print "<td><button class='btn btn-block btn-info btn-sm'>No data</button></td>";
                }
                else {
                print "<td><button class='btn btn-block btn-danger btn-sm'>$powerState</button></td>";
            }

            $params = array(
              "output" => array("lastvalue"),
              "hostids" => $hostID,
              "search" => array("key_" => "vmwVMGuestState$itemkeyno")//seach id contains specific word
              );
              //call api method
              $result = $zbx->call('item.get',$params);
              foreach ($result as $row) {
                  $guestState = $row["lastvalue"];
                  if ($guestState == "running") {
                      print "<td><button class='btn btn-block btn-success btn-sm'>$guestState</button></td></tr>";
                  }
                  else if ($guestState == "") {
                      print "<td><button class='btn btn-block btn-info btn-sm'>No data</button></td></tr>";
                  }
                  else {
                      print "<td><button class='btn btn-block btn-danger btn-sm'>$guestState</button></td></tr>";
                  }
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
    $("#virtualmac").DataTable();
  });
</script>