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

// search for all linux groupid
$params = array(
    "output" => array("groupid", "name"),
    "search" => array("name" => array("synthesis", "linux")),
    "searchByAny" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('hostgroup.get',$params);
foreach ($result as $hostgroup) {
    $linux_groupid[] = $hostgroup["groupid"];
}

// store all hostids in array
$params = array(
    "output" => array("hostid", "name"),
    "groupids" => $linux_groupid
);
//call api problem.get only to get eventid
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
    $linux_hostids[] = $host["hostid"];
}

// sort top 10
foreach ($linux_hostids as $hostID) {

    // percentage values
    $cpu_value = 0;
    $mem_value = 0;
    $cpu_core_value = 0;
    $total_mem_value = 0;
    $free_mem_value = 0;

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

    // CPU
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("cpu util", "cpu percentage")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        if ((strtotime("now") - $item["lastclock"] <= 300)) {
            $cpu_value = $item["lastvalue"];
        }
    }

    // CPU Core
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock", "delay"),
        "hostids" => $hostID,
        "search" => array("name" => array("number of cpu")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $cpu_core_value = $item["lastvalue"];
    }

    // Memory
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock"),
        "hostids" => $hostID,
        "search" => array("name" => array("memory util")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        // check if last clock is more than 5 minutes
        if ((strtotime("now") - $item["lastclock"] <= 300)) {
            $mem_value = $item["lastvalue"];
        }
    }

    // Total Memory
    $params = array(
        "output" => array("itemid", "lastvalue", "lastclock"),
        "hostids" => $hostID,
        "search" => array("name" => array("total memory")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $total_mem_value = $item["lastvalue"];
    }

    // Used Memory
    $params = array(
        "output" => array("itemid", "lastvalue", "key_", "lastclock"),
        "hostids" => $hostID,
        "search" => array("key_" => array("vm.memory.size[free]")),
        "searchByAny" => true
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('item.get',$params);
    foreach ($result as $item) {
        $free_mem_value = $item["lastvalue"];
    }

    // print $total_mem_value."<br>";
    // print $free_mem_value."<br>";

    $used_mem_value = $total_mem_value - $free_mem_value;
    $used_mem_value = formatBytes($used_mem_value);
    $total_mem_value = formatBytes($total_mem_value);

    if ($cpu_value == 0 && $mem_value == 0) {
        continue;
    }
    else {

        // calculate average
        $avg = (($cpu_value + $mem_value) / 200) * 100;

        // print "<tr>";
        // print "<td>".$gethostname."</td>";
        // print "<td>".$cpu_value."</td>";
        // print "<td>".$mem_value."</td>";
        // print "</tr>";

        $data[] = array(
            "hostid" => $hostID,
            "hostname" => $gethostname,
            "cpu" => $cpu_value,
            "cpu_core" => $cpu_core_value,
            "memory" => $mem_value,
            "total_memory" => $total_mem_value,
            "used_memory" => $used_mem_value,
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

// print "<pre>";
// print json_encode($data, JSON_PRETTY_PRINT);
// print "</pre>";
?>
<html>
    <head></head>
    <body>
        <table id="overview_linux_1_table" class="display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Host</th>
                    <th>Total CPU Cores</th>
                    <th>CPU Utilization</th>
                    <th>CPU Value</th>
                    <th>Total Memory</th>
                    <th>Used Memory</th>
                    <th>Memory Utilization</th>
                    <th>Memory Value</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data as $d) {

                    // CPU
                    $cpu_value = number_format((float)$d["cpu"], 2, '.', '');
                    $cpu_label = $cpu_value;
                    
                    if ($cpu_value > 70) {
                        $cpu_status = "red";
                        $cpu_loader = "progress-bar progress-bar-danger";
                    } else if ($cpu_value > 30 && $cpu_value <= 70) {
                        $cpu_status = "orange";
                        $cpu_loader = "progress-bar progress-bar-warning";
                    } else if ($cpu_value > 0 && $cpu_value <= 30) {
                        $cpu_status = "seagreen";
                        $cpu_loader = "progress-bar progress-bar-success";
                    } else if ($cpu_value <= 0.00) {
                        $cpu_status = "seagreen";
                        $cpu_loader = "progress-bar progress-bar-success";
                        $cpu_label = "0.00";
                        $cpu_value = 1;
                    }

                    // Mem
                    $mem_value = number_format((float)$d["memory"], 2, '.', '');
                    $mem_label = $mem_value;
                    
                    if ($mem_value > 70) {
                        $mem_status = "red";
                        $mem_loader = "progress-bar progress-bar-danger";
                    } else if ($mem_value > 30 && $mem_value <= 70) {
                        $mem_status = "orange";
                        $mem_loader = "progress-bar progress-bar-warning";
                    } else if ($mem_value > 0 && $mem_value <= 30) {
                        $mem_status = "seagreen";
                        $mem_loader = "progress-bar progress-bar-success";
                    } else if ($mem_value <= 0.00) {
                        $mem_status = "seagreen";
                        $mem_loader = "progress-bar progress-bar-success";
                        $mem_label = "0.00";
                        $mem_value = 1;
                    }

                    print "<tr>";
                    print '<td><a href="hostdetails_linux.php?hostid=' . $d["hostid"] . '#performance" target="_blank">' . $d["hostname"] . '</a></td>';
                    print "<td>".$d["cpu_core"]."</td>";
                    print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $cpu_loader . '" style="width: ' . $cpu_value . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $cpu_status . ';">' . $cpu_label . ' %</td>';
                    print "<td>".$d["total_memory"]."</td>";
                    print "<td>".$d["used_memory"]."</td>";
                    print '<td>
                            <div class="progress progress-lg progress-striped active">
                                <div class="' . $mem_loader . '" style="width: ' . $mem_value . '%;"></div>
                            </div>
                            </td>
                            <td style="color: white; background-color: ' . $mem_status . ';">' . $mem_label . ' %</td>';
                    print "<td>".$d["avg"]."</td>";
                    print "</tr>";

                    $spark_cpu_count++;
                    $spark_mem_count++;
                }
                ?>
            </tbody>
        </table>
    </body>
</html>

<script>
//table settings
var overview_linux_1_table = $('#overview_linux_1_table').DataTable({
    columnDefs: [
        {
            targets: [ 8 ],
            visible: false,
            searchable: false
        },
    ],
    order: [ 8 , 'desc' ]
});
</script>