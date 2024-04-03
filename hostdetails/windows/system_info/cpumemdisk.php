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
	<table class="table table-bordered table-striped">
      <tr>
        <th>CPU Count</th>
        <?php
        $params = array(
        "output" => array("itemid"),
        "hostids" => $hostid,
        "searchByAny" => true,
        "search" => array(
            "name" => array("cpu count")
        )
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
            $itemid = $item["itemid"];
        }

        if (isset($itemid) == false) {
          print "<td>No data</td>";
        }
        else {
        $params = array(
        "output" => array("lastvalue"),
        "itemids" => $itemid
        );

        //call api history.get with params
        $result = $zbx->call('item.get',$params);
        foreach ($result as $row) {
          $cpuCount = $row["lastvalue"];
          print "<td>$cpuCount</td>";
          }
        }
        ?>
      </tr>
      <tr>
        <th>Total Memory</th>
        <td>
        <?php
        $params = array(
        "output" => array("itemid"),
        "hostids" => $hostid,
        "search" => array("name" => "total memory")//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
            $itemid = $item["itemid"];
        }

        if (isset($itemid) == false) {
          print "No data";
        }
        else {
        $params = array(
        "output" => array("lastvalue"),
        "itemids" => $itemid
        );

        //call api history.get with params
        $result = $zbx->call('item.get',$params);
        foreach ($result as $row) {
          $totalMem = $row["lastvalue"];
          print formatBytes($totalMem);
          }
        }
        ?>
        </td>
      </tr>
      <?php
        $params = array(
        "output" => array("itemid", "name"),
        "hostids" => $hostid,
        "search" => array("name" => "Used disk space on")//seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get',$params);
        foreach ($result as $item) {
            $itemid = $item["itemid"];
            $itemname = $item["name"];

            if (isset($itemid) == false) {
              print "<th>No disk data</th>";
            }
            else {
            $params = array(
            "output" => array("name","lastvalue"),
            "itemids" => $itemid
            );

            //call api history.get with params
            $result = $zbx->call('item.get',$params);
            foreach ($result as $row) {
              $diskname = ucwords(substr($row["name"], 5));
              $diskused = $row["lastvalue"]/1024/1024/1024;
              $diskused = number_format((float)$diskused, '2', '.', ''); // round to 2 dec points
              }

            $params = array(
            "output" => array("itemid", "name"),
            "hostids" => $hostid,
            "search" => array("name" => "total $diskname")//seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {
              //if the current item is the same as the previous item, break the loop
              if (ucwords(substr($item["name"], 6)) != $diskname ) {
                break;
              }

                $itemid = $item["itemid"];  
                $itemname = $item["name"];
              
              
                if (isset($itemid) == false) {
                  print "<th>No disk data</th>";
                }
                else {
                $params = array(
                "output" => array("lastvalue"),
                "itemids" => $itemid
                );

                //call api history.get with params
                $result = $zbx->call('item.get',$params);
                foreach ($result as $row) {
                  $disktotal = $row["lastvalue"]/1024/1024/1024; //collect data
                  $disktotal = number_format((float)$disktotal, '2', '.', ''); //round to 2 dec points
                  }
                  
                  if ($diskused == 0 and $disktotal == 0) {
                    $diskpercent = "No data";
                  }
                  else {
                    $diskpercent = ($diskused/$disktotal)*100;
                    $diskpercent = number_format((float)$diskpercent, '2', '.', '');
                  }
                  
                  print "<tr><th rowspan='2'>".$diskname."</th>";
                  print "<td>".$diskused." / ".$disktotal." GB</td></tr>";
                  print "<tr><td><div class='progress progress-xs'>";
                  if (is_numeric($diskpercent)) {
                    if ($diskpercent <= 60) {
                       print "<div class='progress-bar progress-bar-green' role='progressbar' aria-valuemin='0' aria-valuemax='100' style='width: $diskpercent%'>
                            </div>
                          </div>
                        </td>
                        <td><span class='badge bg-green'>$diskpercent %</span></td>
                        </tr>";
                    }
                    else if ($diskpercent > 60 && $diskpercent <= 80) {
                      print "<div class='progress-bar progress-bar-yellow' role='progressbar' aria-valuemin='0' aria-valuemax='100' style='width: $diskpercent%'>
                            </div>
                          </div>
                        </td>
                        <td><span class='badge bg-yellow'>$diskpercent %</span></td>
                        </tr>";
                    }
                    else if ($diskpercent > 80) {
                      print "<div class='progress-bar progress-bar-red' role='progressbar' aria-valuemin='0' aria-valuemax='100' style='width: $diskpercent%'>
                            </div>
                          </div>
                        </td>
                        <td><span class='badge bg-red'>$diskpercent %</span></td>
                        </tr>";
                    }
                  }
                  
                  else {
                    print "<div class='progress-bar progress-bar-default' role='progressbar' aria-valuemin='0' aria-valuemax='100' style='width: $diskpercent%'>
                            </div>
                          </div>
                        </td>
                        <td><span class='badge bg-default'>$diskpercent</span></td>
                        </tr>";
                  }

                }
                
            }

          }
          
          
        }
        ?>
    </table>
</body>
</html>