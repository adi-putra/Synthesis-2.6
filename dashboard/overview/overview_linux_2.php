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
    "search" => array("name" => array("linux", "synthesis")),
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
    $root_util = 0;
    $root_total = 0;
    $root_used = 0;
    $home_util = 0;
    $home_total = 0;
    $home_used = 0;

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

    // root Total Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("key_" => array("vfs.fs.size[/,total]")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $root_total = $item["lastvalue"];
    }

    // root Used Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("/: Used space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $root_used = $item["lastvalue"];
    }

    // root Space Util
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("/: Space utilization")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $root_util = $item["lastvalue"];
    }

    // home Total Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("/home: Total space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $home_total = $item["lastvalue"];
    }

    // home Used Space
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("/home: Used space")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $home_used = $item["lastvalue"];
    }

    // home Space Util
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("/home: Space utilization")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $home_util = $item["lastvalue"];
    }

    if ($root_util == 0 && $home_util == 0) {
        continue;
    }
    else {
        // calculate average
        $avg = (($root_util + $home_util) / 200) * 100;

        $data[] = array(
            "hostid" => $hostID,
            "hostname" => $gethostname,
            "root_total" => $root_total,
            "root_used" => $root_used,
            "root_util" => $root_util,
            "home_total" => $home_total,
            "home_used" => $home_used,
            "home_util" => $home_util,
            "avg" => $avg
        );
    }
}

// print "<pre>";
// print json_encode($data, JSON_PRETTY_PRINT);
// print "</pre>";

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
        <table id="overview_linux_2_table" class="display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Host</th>
                    <th>/: Total Space</th>
                    <th>/: Used Space</th>
                    <th>/: Space Utilization</th>
                    <th>/: Value</th>
                    <th>/home: Total Space</th>
                    <th>/home: Used Space</th>
                    <th>/home: Space Utilization</th>
                    <th>/home: Value</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data as $d) {

                    // C Space Util
                    $root_util = number_format((float)$d["root_util"], 2, '.', '');
                    $root_util_label = $root_util;
                    
                    if ($root_util > 70) {
                        $root_util_status = "red";
                        $root_util_loader = "progress-bar progress-bar-danger";
                    } else if ($root_util > 30 && $root_util <= 70) {
                        $root_util_status = "orange";
                        $root_util_loader = "progress-bar progress-bar-warning";
                    } else if ($root_util > 0 && $root_util <= 30) {
                        $root_util_status = "seagreen";
                        $root_util_loader = "progress-bar progress-bar-success";
                    } else if ($root_util <= 0.00) {
                        $root_util_status = "seagreen";
                        $root_util_loader = "progress-bar progress-bar-success";
                        $root_util_label = "0.00";
                        $root_util_value = 1;
                    }

                    // D Space Util
                    $home_util = number_format((float)$d["home_util"], 2, '.', '');
                    $home_util_label = $home_util;
                    
                    if ($home_util > 70) {
                        $home_util_status = "red";
                        $home_util_loader = "progress-bar progress-bar-danger";
                    } else if ($home_util > 30 && $home_util <= 70) {
                        $home_util_status = "orange";
                        $home_util_loader = "progress-bar progress-bar-warning";
                    } else if ($home_util > 0 && $home_util <= 30) {
                        $home_util_status = "seagreen";
                        $home_util_loader = "progress-bar progress-bar-success";
                    } else if ($home_util <= 0.00) {
                        $home_util_status = "seagreen";
                        $home_util_loader = "progress-bar progress-bar-success";
                        $home_util_label = "0.00";
                        $home_util_value = 1;
                    }

                    print "<tr>";
                    print '<td><a href="hostdetails_linux.php?hostid=' . $d["hostid"] . '#performance" target="_blank">' . $d["hostname"] . '</a></td>';
                    print "<td>".formatBytes($d["root_total"])."</td>";
                    print "<td>".formatBytes($d["root_used"])."</td>";
                    print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $root_util_loader . '" style="width: ' . $root_util . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $root_util_status . ';">' . $root_util_label . ' %</td>';
                    print "<td>".formatBytes($d["home_total"])."</td>";
                    print "<td>".formatBytes($d["home_used"])."</td>";
                    print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $home_util_loader . '" style="width: ' . $home_util . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $home_util_status . ';">' . $home_util_label . ' %</td>';
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
var overview_linux_2_table = $('#overview_linux_2_table').DataTable({
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