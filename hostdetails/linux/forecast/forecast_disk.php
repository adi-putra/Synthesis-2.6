<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10713");
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

//display time format
$diff = $timetill - $timefrom;

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
     $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    if ($seconds > 31556926) {
        return $dtF->diff($dtT)->format('%y years %m months %d days');
    }
    else {
        return $dtF->diff($dtT)->format('%m months %d days %h hours');
    }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<body>

    <?php
        $params = array(
        "output" => array("name"),
        "hostids" => $hostid
        );
        //call api method
        $result = $zbx->call('host.get', $params);
        foreach ($result as $host) {
            $gethostname = $host["name"];
        }

        $diskname = [];

        $params = array(
        "output" => array("itemid", "name", "key_", "error", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("key_" => "vfs.fs.timeleft"), //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        foreach ($result as $item) {
            
            $itemname = substr($item["key_"], 16);
            $diskname[] = substr($itemname, 0, -7);

        }

        if (empty($diskname)) {
            print '<p class="text-danger">No data</p>';
        }
        else {
            for ($i=0; $i < count($diskname); $i++) {
            
                print '<table class="table table-bordered table-striped">';
                print '<thead>
                        <tr>
                            <th class="text-center" colspan="2"> Forecast Free space on '.$diskname[$i].'</th>
                        </tr>
                    </thead>';

                $params = array(
                "output" => array("itemid", "name", "key_", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => "vfs.fs.timeleft[".$diskname[$i].",pused]"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {

                    $lastvalue = $item["lastvalue"];

                    if ($lastvalue > 31560000) {
                        $status = "green";
                    } else if ($lastvalue > 13150000 && $lastvalue <= 31560000) {
                        $status = "orange";
                    } else if ($lastvalue > 0 && $lastvalue <= 13150000) {
                        $status = "red";
                    } else if ($lastvalue <= 0.00) {
                        $status = "red";
                    }

                    print '<tr>';
                    print "<th>".$item["name"]."</th>";
                    print '<td style="color: white; background-color: ' . $status . ';">' . secondsToTime($lastvalue) . ' </td>';
                    print '</tr>';

                }
    
                $params = array(
                "output" => array("itemid", "name", "key_", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => "vfs.fs.forecast.30d[".$diskname[$i].",free]"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    $lastvalue = $item["lastvalue"];

                    if ($lastvalue > 10737418240) {
                        $status = "green";
                    } else if ($lastvalue > 1073741824 && $lastvalue <= 10737418240) {
                        $status = "orange";
                    } else if ($lastvalue > 0 && $lastvalue <= 1073741824) {
                        $status = "red";
                    } else if ($lastvalue <= 0.00) {
                        $status = "red";
                    }

                    print '<tr>';
                    print "<th>".$item["name"]."</th>";
                    print '<td style="color: white; background-color: ' . $status . ';">' . formatBytes($lastvalue) . ' </td>';
                    print '</tr>';

                }
    
                $params = array(
                "output" => array("itemid", "name", "key_", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => "vfs.fs.forecast.60d[".$diskname[$i].",free]"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    $lastvalue = $item["lastvalue"];

                    if ($lastvalue > 10737418240) {
                        $status = "green";
                    } else if ($lastvalue > 1073741824 && $lastvalue <= 10737418240) {
                        $status = "orange";
                    } else if ($lastvalue > 0 && $lastvalue <= 1073741824) {
                        $status = "red";
                    } else if ($lastvalue <= 0.00) {
                        $status = "red";
                    }

                    print '<tr>';
                    print "<th>".$item["name"]."</th>";
                    print '<td style="color: white; background-color: ' . $status . ';">' . formatBytes($lastvalue) . ' </td>';
                    print '</tr>';
                }
    
                $params = array(
                "output" => array("itemid", "name", "key_", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => "vfs.fs.forecast.90d[".$diskname[$i].",free]"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    $lastvalue = $item["lastvalue"];

                    if ($lastvalue > 10737418240) {
                        $status = "green";
                    } else if ($lastvalue > 1073741824 && $lastvalue <= 10737418240) {
                        $status = "orange";
                    } else if ($lastvalue > 0 && $lastvalue <= 1073741824) {
                        $status = "red";
                    } else if ($lastvalue <= 0.00) {
                        $status = "red";
                    }

                    print '<tr>';
                    print "<th>".$item["name"]."</th>";
                    print '<td style="color: white; background-color: ' . $status . ';">' . formatBytes($lastvalue) . ' </td>';
                    print '</tr>';
                }
    
                $params = array(
                "output" => array("itemid", "name", "key_", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => "vfs.fs.forecast.180d[".$diskname[$i].",free]"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    $lastvalue = $item["lastvalue"];

                    if ($lastvalue > 10737418240) {
                        $status = "green";
                    } else if ($lastvalue > 1073741824 && $lastvalue <= 10737418240) {
                        $status = "orange";
                    } else if ($lastvalue > 0 && $lastvalue <= 1073741824) {
                        $status = "red";
                    } else if ($lastvalue <= 0.00) {
                        $status = "red";
                    }

                    print '<tr>';
                    print "<th>".$item["name"]."</th>";
                    print '<td style="color: white; background-color: ' . $status . ';">' . formatBytes($lastvalue) . ' </td>';
                    print '</tr>';
                }
    
                $params = array(
                "output" => array("itemid", "name", "key_", "error", "lastvalue"),
                "hostids" => $hostid,
                "search" => array("key_" => "vfs.fs.forecast.365d[".$diskname[$i].",free]"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    
                    $lastvalue = $item["lastvalue"];

                    if ($lastvalue > 10737418240) {
                        $status = "green";
                    } else if ($lastvalue > 1073741824 && $lastvalue <= 10737418240) {
                        $status = "orange";
                    } else if ($lastvalue > 0 && $lastvalue <= 1073741824) {
                        $status = "red";
                    } else if ($lastvalue <= 0.00) {
                        $status = "red";
                    }

                    print '<tr>';
                    print "<th>".$item["name"]."</th>";
                    print '<td style="color: white; background-color: ' . $status . ';">' . formatBytes($lastvalue) . ' </td>';
                    print '</tr>';
                }
    
                print "</table>";
            }
        }
    ?>


</body>
</html>