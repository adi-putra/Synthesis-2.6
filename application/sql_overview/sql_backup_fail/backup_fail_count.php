<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"];
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<body>

<?php

$count = 1;

foreach ($hostid as $hostID) {
    //get hostname
    $params = array(
    "output" => array("name"),
    "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    //get number of fail jobs
    $params = array(
    "output" => "extend",
    "hostids" => $hostID,
    "search" => array("name" => "Event Log (Backup Failed)")
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    foreach ($result as $item) {
        if ($item["lastclock"] == 0 and $item["lastvalue"] == "") {
            $count_value = 0;
        }
        else {
            $params = array(
            "output" => array("value", "itemid", "clock"),
            "itemids" => $item["itemid"],
            "history" => 2,
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill,
            "countOutput" => true
            );
            $count_value = $zbx->call('history.get', $params);
        }

        //color for div
        if ($count_value > 0) {
            $divclass = "<div class='small-box bg-red'>";
        }
        else {
            $divclass = "<div class='small-box' style='background-color: gray; color: white;'>";
        }

        if ($count == 1) {
            print '<div class="row">';
        }

        print '<div class="col-md-3">
                    '.$divclass.'
                        <div class="inner">
                            <h3 style="text-align: center;">
                            <div>'.$count_value.'</div>
                            </h3>
                        </div>
                        <a href="#" class="small-box-footer">
                            '.$gethostname.'
                        </a>
                    </div>
                </div>';
        
        if ($count % 4 == 0) {
            print '</div>';
            $count = 1;
        }
        else {
            $count++;
        }
    }
}
?>

</body>
</html>