<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

$hostid = $_GET["hostid"] ?? array("10729");
$groupid = $_GET["groupid"];

//time variables
$timefrom = $_GET['timefrom'] ?? strtotime("-1 day");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

//store itemid array
$itemid = array();
$count2 = 0;
$series = array();
$color = array();

foreach ($hostid as $hostID) {

    $params = array(
    "output" => array("name"),
    "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    //last run time job
    $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("name" => "last run time job"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    if (!empty($result)) {
        foreach ($result as $item) {

            //get host name in veeam and item run id
            $gethost_veeam = substr($item["name"], 20);
            $itemrun_id = $item["itemid"];

            // echo $itemname_run."<br>";

            //last end time job
            $params = array(
            "output" => array("itemid", "name", "error", "lastvalue"),
            "hostids" => $hostID,
            "search" => array("name" => "last end time job : $gethost_veeam"), //seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get', $params);

            foreach ($result as $item) {
                //get item end id
                $itemend_id = $item["itemid"];
            }

            $itemid[] = array(
                "runid" => $itemrun_id,
                "endid" => $itemend_id,
                "name" => $gethost_veeam,
                "hostname" => $gethostname
            );
        }
    }

    // $params = array(
    // "output" => array("itemid", "name", "error", "lastvalue"),
    // "hostids" => $hostID,
    // "search" => array("name" => "last end time job"), //seach id contains specific word
    // );
    // //call api method
    // $result = $zbx->call('item.get', $params);
    // if (!empty($result)) {
    //     foreach ($result as $item) {
    //         $itemid[] = array(
    //             "id" => $item["itemid"], 
    //             "name" => $item["name"],
    //             "hostname" => $gethostname,
    //             "lastvalue" => $item["lastvalue"]
    //         );
    //     }
    // }
}

// print "<pre>";
// print json_encode($itemid, JSON_PRETTY_PRINT);
// print "</pre>";

$itemdata = array();
$itemseries = "";

$counthost = 0;

foreach ($itemid as $item) {

  // echo $item["name"];

    $params = array(
        "output" => "extend",
        "history" => 3,
        "itemids" => $item["runid"],
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill
    );
    //call api history.get with params
    $result1 = $zbx->call('history.get', $params);
    if (!empty($result1)) {
        foreach (array_reverse($result1) as $history1) {

            $runclock = $history1["value"] * 1000;
            $runclock_txt = date("d-m-Y h:i:s", $runclock / 1000);

            $params = array(
                "output" => "extend",
                "history" => 3,
                "itemids" => $item["endid"],
                "sortfield" => "clock",
                "sortorder" => "DESC",
                "time_from" => $timefrom,
                "time_till" => $timetill
            );
            //call api history.get with params
            $result2 = $zbx->call('history.get', $params);

            foreach (array_reverse($result2) as $history2) {
                $endclock = $history2["value"] * 1000;
                $endclock_txt = date("d-m-Y h:i:s", $endclock / 1000);
            }

            $itemdata[] = array(
                "category" => $item["name"],
                "start" => $runclock,
                "end" => $endclock,
                "task" => $runclock_txt." - ".$endclock_txt
            );
        }
    }
    
    // $series[$count2] = array(
    //     "name" => $item["hostname"].": ".$item["name"],
    //     "data" => $itemdata
    // );
    // $count2++;
}

// $itemseries = [];
// foreach($itemdata as $item) {
//     $hash = $item["name"];
//     $itemseries[$hash] = $item;
// }
// $itemseries = json_encode(array_values($itemseries), JSON_PRETTY_PRINT);

$itemseries = json_encode($itemdata, JSON_PRETTY_PRINT);
// $itemseries .= $itemdata;

// for ($i=0; $i < $count2; $i++) { 
//     $color[] = "#".random_color();
// }

print "<pre>";
print $itemseries;
print "</pre>";
?>
<html>
    <head>
        <!-- Styles -->
        <style>
        #backuptimediv {
            width: 100%;
            height: 500px;
        }
        </style>
        <!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    </head>
    <body>
        <!-- HTML -->
        <div id="backuptimediv"></div>
    </body>
</html>

<!-- Chart code -->
<script>

// Themes begin
// am4core.useTheme(am4themes_animated);
// Themes end

var backuptime = am4core.create("backuptimediv", am4charts.XYChart);
backuptime.hiddenState.properties.opacity = 0; // this creates initial fade-in

backuptime.paddingRight = 30;
backuptime.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm";

var colorSet = new am4core.ColorSet();
colorSet.saturation = 0.4;

backuptime.data = <?php echo $itemseries; ?>;

backuptime.dateFormatter.dateFormat = "yyyy-MM-dd HH:mm";

var categoryAxis = backuptime.yAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "category";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.inversed = true;

var dateAxis = backuptime.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 70;
dateAxis.baseInterval = { count: 1, timeUnit: "minute" };
// dateAxis.max = new Date(2018, 0, 1, 24, 0, 0, 0).getTime();
//dateAxis.strictMinMax = true;
dateAxis.renderer.tooltipLocation = 0;

var series1 = backuptime.series.push(new am4charts.ColumnSeries());
series1.columns.template.height = am4core.percent(70);
series1.columns.template.tooltipText = "[bold]{task}[/]: {openDateX} - {dateX}";

series1.dataFields.openDateX = "start";
series1.dataFields.dateX = "end";
series1.dataFields.categoryY = "category";
series1.columns.template.propertyFields.fill = "color"; // get color from data
series1.columns.template.propertyFields.stroke = "color";
series1.columns.template.strokeOpacity = 1;

backuptime.scrollbarX = new am4core.Scrollbar();


</script>