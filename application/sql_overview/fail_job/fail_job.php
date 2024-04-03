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
    "search" => array("name" => "Job ID:"),
    "countOutput" => true
    );
    //call api method
    $result = $zbx->call('item.get', $params);

    //color for div
    if ($result > 0) {
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
                        <div>'.$result.'</div>
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
?>

</body>
</html>