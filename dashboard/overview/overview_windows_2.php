<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/session.php");

$hostid = $_GET["hostid"];
$groupid = $_GET["groupid"];

//time variables
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  //return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
  if ($seconds < 86400) {
	return $dtF->diff($dtT)->format('%h hours %i minutes');
  }
  else if ($seconds >= 86400) {
	return $dtF->diff($dtT)->format('%a days');
  }
}

function formatBytes($bytes, $precision = 2)
{
  $units = array('B', 'KB', 'MB', 'GB', 'TB');

  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);

  // Uncomment one of the following alternatives
  $bytes /= pow(1024, $pow);
  // $bytes /= (1 << (10 * $pow)); 

  return round($bytes, $precision) . ' ' . $units[$pow];
}

// search for all windows groupid
$params = array(
    "output" => array("groupid", "name"),
    "search" => array("name" => array("window", "ws")),
    "searchByAny" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('hostgroup.get',$params);
foreach ($result as $hostgroup) {
    $windows_groupid[] = $hostgroup["groupid"];
}

// store all hostids in array
$params = array(
    "output" => array("hostid", "name"),
    "groupids" => $windows_groupid
);
//call api problem.get only to get eventid
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
    $windows_hostids[] = $host["hostid"];
}

// sort top 10
foreach ($windows_hostids as $hostID) {

    // percentage values
    $c_total = 0;
    $c_used = 0;
    $c_util = 0;
    $d_total = 0;
    $d_used = 0;
    $d_util = 0;

    //Hostname
    $params = array(
        "output" => array("hostid", "name"),
        "hostids" => $hostID
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('host.get',$params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    // C Total Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("Total disk space on C:", "C: Total space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $c_total = $item["lastvalue"];
    }

    // C Used Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("Used disk space on C:", "C: Used space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $c_used = $item["lastvalue"];
    }

    // C Space Util
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("key_" => array("vfs.fs.size[C:,pused]")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $c_util = $item["lastvalue"];
    }

    // D Total Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("Total disk space on D:", "D: Total space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $d_total = $item["lastvalue"];
    }

    // D Used Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("Used disk space on D:", "D: Used space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $d_used = $item["lastvalue"];
    }

    // D Space Util
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("key_" => array("vfs.fs.size[D:,pused]")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $d_util = $item["lastvalue"];
    }

    if ($c_util == 0 && $d_util == 0) {
        continue;
    }
    else {
        // calculate average
        $avg = (($c_util + $d_util) / 200) * 100;

        $data[] = array(
            "hostid" => $hostID,
            "hostname" => $gethostname,
            "c_total" => $c_total,
            "c_used" => $c_used,
            "c_util" => $c_util,
            "d_total" => $d_total,
            "d_used" => $d_used,
            "d_util" => $d_util,
            "avg" => $avg
        );
    }
}

// sorting before slice
if (!empty($data)) {
    usort($data, function($a, $b) {
        return ($a["avg"] > $b["avg"])?-1:1;
    });
    
    // slice array to top 10 only
    $data = array_slice($data, 0, 10);
}
?>
<html>
    <head></head>
    <body>
        <table id="overview_windows_2_table" class="display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Host</th>
                    <th>C: Total Space</th>
                    <th>C: Used Space</th>
                    <th>C: Space Utilization</th>
                    <th>C: Value</th>
                    <th>D: Total Space</th>
                    <th>D: Used Space</th>
                    <th>D: Space Utilization</th>
                    <th>D: Value</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data as $d) {

                    // C Space Util
                    $c_util = number_format((float)$d["c_util"], 2, '.', '');
                    $c_util_label = $c_util;
                    
                    if ($c_util > 70) {
                        $c_util_status = "red";
                        $c_util_loader = "progress-bar progress-bar-danger";
                    } else if ($c_util > 30 && $c_util <= 70) {
                        $c_util_status = "orange";
                        $c_util_loader = "progress-bar progress-bar-warning";
                    } else if ($c_util > 0 && $c_util <= 30) {
                        $c_util_status = "seagreen";
                        $c_util_loader = "progress-bar progress-bar-success";
                    } else if ($c_util <= 0.00) {
                        $c_util_status = "seagreen";
                        $c_util_loader = "progress-bar progress-bar-success";
                        $c_util_label = "0.00";
                        $c_util_value = 1;
                    }

                    // D Space Util
                    $d_util = number_format((float)$d["d_util"], 2, '.', '');
                    $d_util_label = $d_util;
                    
                    if ($d_util > 70) {
                        $d_util_status = "red";
                        $d_util_loader = "progress-bar progress-bar-danger";
                    } else if ($d_util > 30 && $d_util <= 70) {
                        $d_util_status = "orange";
                        $d_util_loader = "progress-bar progress-bar-warning";
                    } else if ($d_util > 0 && $d_util <= 30) {
                        $d_util_status = "seagreen";
                        $d_util_loader = "progress-bar progress-bar-success";
                    } else if ($d_util <= 0.00) {
                        $d_util_status = "seagreen";
                        $d_util_loader = "progress-bar progress-bar-success";
                        $d_util_label = "0.00";
                        $d_util_value = 1;
                    }

                    print "<tr>";
                    print '<td><a href="hostdetails_windows.php?hostid=' . $d["hostid"] . '#performance" target="_blank">' . $d["hostname"] . '</a></td>';
                    print "<td>".formatBytes($d["c_total"])."</td>";
                    print "<td>".formatBytes($d["c_used"])."</td>";
                    print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $c_util_loader . '" style="width: ' . $c_util . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $c_util_status . ';">' . $c_util_label . ' %</td>';
                    print "<td>".formatBytes($d["d_total"])."</td>";
                    print "<td>".formatBytes($d["d_used"])."</td>";
                    print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $d_util_loader . '" style="width: ' . $d_util . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $d_util_status . ';">' . $d_util_label . ' %</td>';
                    print "<td>".$d["avg"]."</td>";
                    print "</tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
</html>

<script>
//table settings
var overview_windows_2_table = $('#overview_windows_2_table').DataTable({
    columnDefs: [
        {
            targets: [ 9 ],
            visible: false,
            searchable: false
        },
    ],
    order: [ 9 , 'desc' ]
});
</script>