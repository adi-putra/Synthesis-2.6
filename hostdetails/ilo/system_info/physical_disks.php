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
        <th>ID</th>
        <th>Name</th>
        <th>Serial Number</th>
        <th>Disk Size</th>
        <th style="text-align: center;">Status</th>
        <th style="text-align: center;">S.M.A.R.T Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        $key = "system.hw.physicaldisk.serialnumber[cpqDaPhyDrvSerialNum.";
        $params = array(
        "output" => array("name", "lastvalue", "key_"),
        "hostids" => $hostid,
        "search" => array("key_" => $key)//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
            //media types
            $pdSerialNum = $item["lastvalue"];
            
            $pdname = substr($item["key_"], 57);
            $pdname = substr($pdname, 0, -1);
            $key = "system.hw.physicaldisk.size[cpqDaPhyDrvMediaType.".$pdname."]";

            print "<tr><td>$id</td>";
            print "<td>Disk $pdname</td>
                  <td>$pdSerialNum</td>";

            $params = array(
              "output" => array("name", "lastvalue"),
              "hostids" => $hostid,
              "search" => array("key_" => $key)//seach id contains specific word
            );
            //call api method
            $result1 = $zbx->call('item.get',$params);
            foreach ($result1 as $item1) {
              $pdsize = formatBytes($item1["lastvalue"]);
              $key = "system.hw.physicaldisk.status[cpqDaPhyDrvStatus.".$pdname."]";

              print "<td>$pdsize</td>";

              $params = array(
                "output" => array("name", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => $key)//seach id contains specific word
              );
              //call api method
              $result2 = $zbx->call('item.get',$params);
              foreach ($result2 as $item2) {
                $pdstatus = $item2["lastvalue"];
                if ($pdstatus == 1) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>other</button></td>";
                }
                else if ($pdstatus == 2) {
                  print "<td><button class='btn btn-block btn-success btn-sm'>ok</button></td>";
                }
                else if ($pdstatus == 3) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>failed</button></td>";
                }
                else if ($pdstatus == 4) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>predictiveFailure</button></td>";
                }

                $key = "system.hw.physicaldisk.smart_status[cpqDaPhyDrvSmartStatus.".$pdname."]";

                $params = array(
                  "output" => array("name", "lastvalue"),
                  "hostids" => $hostid,
                  "search" => array("key_" => $key)//seach id contains specific word
                );
                //call api method
                $result3 = $zbx->call('item.get',$params);
                foreach ($result3 as $item3) {
                  $pdsmartstatus = $item3["lastvalue"];
                  if ($pdsmartstatus == 1) {
                    print "<td><button class='btn btn-block btn-info btn-sm'>other</button></td></tr>";
                  }
                  else if ($pdsmartstatus == 2) {
                    print "<td><button class='btn btn-block btn-success btn-sm'>ok</button></td></tr>";
                  }
                  else if ($pdsmartstatus == 3) {
                    print "<td><button class='btn btn-block btn-info btn-sm'>replaceDrive</button></td></tr>";
                  }
                  else if ($pdsmartstatus == 4) {
                    print "<td><button class='btn btn-block btn-info btn-sm'>replaceDriveSSDWearOut</button></td></tr>";
                  }
                }
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