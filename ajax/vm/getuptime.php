<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$groupid = $_GET["groupid"];
$hostid = $_GET["hostid"];

if (empty($hostid)) {
    $params = array(
        "output" => array("hostid", "name"),
        "groupids" => $groupid
    );

    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $hostid[] = $host["hostid"];
    }
}

function secondsToTime($seconds)
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    $daycount = $dtF->diff($dtT)->format('%d');
    $yearcount = $dtF->diff($dtT)->format('%y');
    $weekcount = (int)ltrim($daycount / 7, '0.'); // convert to int -> divide days by 7 -> trim decimal  

    // If year is 0 then trim year, if day is more than 7 then convert to weeks
    if ($yearcount == 0 && $daycount > 7) {
        return $dtF->diff($dtT)->format('%m month(s), ' . $weekcount . ' week(s)');
    } else if ($yearcount == 0 && $daycount <= 7) {
        return $dtF->diff($dtT)->format('%m month(s), %d day(s)');
    } else if ($daycount > 7) {
        return $dtF->diff($dtT)->format('%y year(s), %m month(s), ' . $weekcount . ' week(s)');
    } else {
        return $dtF->diff($dtT)->format('%y year(s), %m month(s), %d day(s)');
    }

    // return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

// Get year
function secondsToYear($seconds)
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    $yearcount = $dtF->diff($dtT)->format('%y');
    return $yearcount;
}

// Get month
function secondsToMonth($seconds)
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    $monthcount = $dtF->diff($dtT)->format('%m');
    return $monthcount;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Windows Overview</title>
</head>

<body>
    <div class="row">
        <div class="col-md-12">
            <!-- CPU Util table -->
            <table id="uptimetable" class="table table-bordered table-striped">
                <caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
                <thead>
                    <tr>
                        <th>Host Name</th>
                        <th>Uptime</th>
                        <th>Duration</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

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
                        "output" => array("itemid", "name", "error", "lastvalue"),
                        "hostids" => $hostID,
                        "search" => array("name" => "uptime"), //seach id contains specific word
                        );
            
                        //call api method
                        $result = $zbx->call('item.get', $params);
            
                        // echo "<pre>";
                        // print_r($result);
                        // echo "</pre>";
            
                        foreach ($result as $item) {
                            if (stripos($item["name"], "OS") !== false) {
                                continue;
                            }
                            else {
                                //$chart_title = $item["name"];
                                $itemid[] = array(
                                    "id" => $item["itemid"], 
                                    "hostid" => $hostID,
                                    "name" => $item["name"],
                                    "hostname" => $gethostname,
                                    "lastvalue" => $item["lastvalue"]
                                );
                            }
                        }
                    }
                        
                    
                    //sort array by lastvalue
                    usort($itemid, function($a, $b) {
                        return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
                    });
                
                    //slice the array to only top 5
                    $itemid = array_slice($itemid, 0, 10);

                    foreach ($itemid as $item) {
                        
                        $uptimedays = $item["lastvalue"];

                        if ($uptimedays == 0) {
                            $uptimeduration = secondsToTime($uptimedays);
                            $status = "red";
                            $loader = "progress-bar progress-bar-danger";
                            $bar = 0;
                        }
                        else {
                            $uptimeduration = secondsToTime($uptimedays);


                            $exYear = secondsToYear($uptimedays); // Extract year from $uptimedays
                            $exMonth = secondsToMonth($uptimedays); // Extract month from $uptimedays
                            $rebootunix = time() - $uptimedays;
                            $rebootdate = date("d-m-Y h:i:s A", $rebootunix);

                            if ($exMonth == 0 && $exYear == 0) {
                                $status = "red";
                                $loader = "progress-bar progress-bar-danger";
                            }
                            else if ($exMonth < 3 || $exMonth == 0) {
                                if ($exYear == 0) {
                                    $status = "red";
                                    $loader = "progress-bar progress-bar-danger";
                                }
                                else {
                                    $status = "seagreen";
                                    $loader = "progress-bar progress-bar-success";
                                }
                            } else if ($exMonth >= 3 && $exMonth < 6) {
                                $status = "orange";
                                $loader = "progress-bar progress-bar-warning";
                            } else if ($exMonth >= 6 || $exYear >= 1) {
                                $status = "seagreen";
                                $loader = "progress-bar progress-bar-success";
                            } 
                            // (Actual Value / Max Value) * 100 , max value is total seconds withtin a year
                            $bar = ($uptimedays / 31536000) * 100;

                            if ($bar < 1) {
                                $bar = 100;
                            }
                        }
                    
                
                    print '<tr>
                            <td><a href="hostdetails_vm.php?hostid='.$item["hostid"].'" target="_blank">' . $item["hostname"] . '</a></td>
                            <td>
                                <div class="progress progress-lg progress-striped active">
                                    <div class="' . $loader . '" style="width: ' . $bar . '%;"></div>
                                </div>
                                </td>
                                <td style="color: white; background-color: ' . $status . ';">' . $uptimeduration . '</td>
                                <td>' . $bar . '</td>
                                </tr>';
                    }
     
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

<script>
    $(function() {
        $('#uptimetable').DataTable({
            "order": [
                [3, "asc"]
            ],
            "columnDefs": [
                {
                    "targets": [ 3 ],
                    "visible": false
                }
            ],
            "scrollY": "400px",
            scrollCollapse: true,
            "paging": true
        });
    });
</script>