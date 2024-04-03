<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"] ?? array("10338", "10341", "10348", "10349", "10351" , "10352");
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

//data array
$datafile = array();
$count = 0;

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

    //get data file size
    $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("name" => ": Log File Size"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    foreach ($result as $item) {
        $datafile[] = array(
            "database" => $gethostname.":".$item["name"],
            "value" => $item["lastvalue"]
        );
    }
}

//sort array by lastvalue
usort($datafile, function($a, $b) {
    return ($a["value"] > $b["value"])?-1:1;
});

//arsort($datafile);
//print json_encode($datafile);
?>

<html>
    <head>

    </head>
    <body>

        <div id="logfilediv" style="width: auto; height: 400px;"></div>

        <!-- Chart code -->
        <script>
        am4core.options.autoDispose = true;
        //am4core.useTheme(am4themes_animated);
        //am4core.useTheme(am4themes_frozen);

    
        // Create chart instance
        var logfile = am4core.create("logfilediv", am4charts.PieChart);

        // Add data
        logfile.numberFormatter.numberFormat = "#.##b";
        logfile.data = <?php echo json_encode($datafile); ?>;

        // Add and configure Series
        var pieSeries = logfile.series.push(new am4charts.PieSeries());
        pieSeries.dataFields.value = "value";
        pieSeries.dataFields.category = "database";
        pieSeries.labels.template.text = "{value}"; 
        pieSeries.slices.template.tooltipText = "{category} : {value}";

        // Disable ticks and labels
        pieSeries.labels.template.disabled = true;
        pieSeries.ticks.template.disabled = true;

        // Add Legend
        logfile.legend = new am4charts.Legend();
        logfile.legend.position = "right";
        logfile.legend.valueLabels.template.text = "{value}"

        logfile.legend.maxHeight = undefined;
        logfile.legend.width = 350;
        logfile.legend.scrollable = true;
        
        var title = logfile.titles.create();
        title.text = "Log File Size";
        title.fontSize = 25;
        title.marginBottom = 30;
        </script>
    </body>
</html>