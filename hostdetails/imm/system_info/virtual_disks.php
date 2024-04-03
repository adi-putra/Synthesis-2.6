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
	<table id="virtualdisk_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Layout Type</th>
        <th>Disk Size</th>
        <th style="text-align: center;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $id = 1;
        $key = "system.hw.virtualdisk.layout[cpqDaLogDrvFaultTol.";
        $params = array(
        "output" => array("itemid","name", "lastvalue", "key_"),
        "hostids" => $hostid,
        "search" => array("key_" => $key)//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
            //layout types
            if ($item["lastvalue"] == 0) {
              $vdlayout = "other";
            }
            else if ($item["lastvalue"] == 2) {
              $vdlayout = "none";
            }
            else if ($item["lastvalue"] == 3) {
              $vdlayout = "RAID-1/RAID-10";
            }
            else if ($item["lastvalue"] == 4) {
              $vdlayout = "RAID-4";
            }
            else if ($item["lastvalue"] == 5) {
              $vdlayout = "RAID-5";
            }
            else if ($item["lastvalue"] == 7) {
              $vdlayout = "RAID-6";
            }
            else if ($item["lastvalue"] == 8) {
              $vdlayout = "RAID-50";
            }
            else if ($item["lastvalue"] == 9) {
              $vdlayout = "RAID-60";
            }
            else if ($item["lastvalue"] == 10) {
              $vdlayout = "RAID-1 ADM";
            }
            else if ($item["lastvalue"] == 3) {
              $vdlayout = "RAID-10 ADM";
            }
            
            $vdname = substr($item["key_"], 49);
            $vdname = substr($vdname, 0, -1);
            $key = "system.hw.virtualdisk.size[cpqDaLogDrvSize.".$vdname."]";

            print "<tr><td>$id</td>";
            print "<td>Disk $vdname</td>
                  <td>$vdlayout</td>";

            $params = array(
              "output" => array("name", "lastvalue"),
              "hostids" => $hostid,
              "search" => array("key_" => $key)//seach id contains specific word
            );
            //call api method
            $result1 = $zbx->call('item.get',$params);
            foreach ($result1 as $item1) {
              $vdsize = formatBytes($item1["lastvalue"]);
              $key = "system.hw.virtualdisk.status[cpqDaLogDrvStatus.".$vdname."]";

              print "<td>$vdsize</td>";

              $params = array(
                "output" => array("name", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => $key)//seach id contains specific word
              );
              //call api method
              $result2 = $zbx->call('item.get',$params);
              foreach ($result2 as $item2) {
                $vdstatus = $item2["lastvalue"];
                if ($vdstatus == 1) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>0ther</button></td></tr>";
                }
                else if ($vdstatus == 2) {
                  print "<td><button class='btn btn-block btn-success btn-sm'>ok</button></td></tr>";
                }
                else if ($vdstatus == 3) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>failed</button></td></tr>";
                }
                else if ($vdstatus == 4) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>Unconfigured</button></td></tr>";
                }
                else if ($vdstatus == 5) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>recovering</button></td></tr>";
                }
                else if ($vdstatus == 6) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>ready for rebuild</button></td></tr>";
                }
                else if ($vdstatus == 7) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>rebuilding</button></td></tr>";
                }
                else if ($vdstatus == 8) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>wrong Drive</button></td></tr>";
                }
                else if ($vdstatus == 9) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>bad connect</button></td></tr>";
                }
                else if ($vdstatus == 10) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>overheating</button></td></tr>";
                }
                else if ($vdstatus == 11) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>shutdown</button></td></tr>";
                }
                else if ($vdstatus == 12) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>expanding</button></td></tr>";
                }
                else if ($vdstatus == 13) {
                  print "<td><button class='btn btn-block btn-danger btn-sm'>not available</button></td></tr>";
                }
                else if ($vdstatus == 14) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>queued for expansion</button></td></tr>";
                }
                else if ($vdstatus == 15) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>multipath access degraded</button></td></tr>";
                }
                else if ($vdstatus == 16) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>erasing</button></td></tr>";
                }
                else if ($vdstatus == 17) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>predictive spare rebuild ready</button></td></tr>";
                }
                else if ($vdstatus == 18) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>rapid parity init in progress</button></td></tr>";
                }
                else if ($vdstatus == 19) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>rapid parity init in pending</button></td></tr>";
                }
                else if ($vdstatus == 20) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>noAccessEncryptedNoCntlrKey</button></td></tr>";
                }
                else if ($vdstatus == 21) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>unencryptedToEncryptedInProgress</button></td></tr>";
                }
                else if ($vdstatus == 22) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>newLogDrvKeyRekeyInProgress</button></td></tr>";
                }
                else if ($vdstatus == 23) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>noAccessEncryptedCntlrEncryptnNotEnbld</button></td></tr>";
                }
                else if ($vdstatus == 24) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>unencryptedToEncryptedNotStarted</button></td></tr>";
                }
                else if ($vdstatus == 25) {
                  print "<td><button class='btn btn-block btn-info btn-sm'>newLogDrvKeyRekeyRequestReceived</button></td></tr>";
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
    $("#virtualdisk_table").dataTable();
  });
</script>