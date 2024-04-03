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
            <table id="clustertable" class="table table-bordered table-striped">
                <caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
                <thead>
                    <tr>
                        <th>Cluster Name</th>
                        <th>Status</th>
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
                        "search" => array("name" => "cluster"), //seach id contains specific word
                        );
            
                        //call api method
                        $result = $zbx->call('item.get', $params);
                        if (empty($result)) {
                            print '<tr>';
                            print '<td>No data</td>';
                            print '<td>No data</td>';
                            print '</tr>';
                        }
                        else {
                            foreach ($result as $item) {

                                $cluster_name = substr($item["name"], 19);
                                $cluster_name = substr($cluster_name, 0, -9);

                                if ($item["lastvalue"] == 0) {
                                    $status_btn = '<button type="button" class="btn btn-block btn-default">Unknown</button>';
                                }
                                else if ($item["lastvalue"] == 1) {
                                    $status_btn = '<button type="button" class="btn btn-block btn-success">OK</button>';
                                }
                                else if ($item["lastvalue"] == 2) {
                                    $status_btn = '<button type="button" class="btn btn-block btn-warning">It might have a problem</button>';
                                }
                                else if ($item["lastvalue"] == 3) {
                                    $status_btn = '<button type="button" class="btn btn-block btn-danger">It has a problem</button>';
                                }
                                else {
                                    $status_btn = '<button type="button" class="btn btn-block btn-default">No data</button>';
                                }

                                print '<tr>';
                                print '<td>'.$cluster_name.'</td>';
                                print '<td>'.$status_btn.'</td>';
                                print '</tr>';
                            }
                        }
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
        $('#clustertable').DataTable({
            "scrollY": "400px",
            scrollCollapse: true,
            "paging": true
        });
    });
</script>