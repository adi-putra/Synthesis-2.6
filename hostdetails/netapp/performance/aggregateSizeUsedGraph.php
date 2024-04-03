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
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title></title>
    <style>
        #aggreUsedlegenddiv {
            height: 300px;
        }

        #aggreUsedlegendwrapper {
            max-height: 400px;
            overflow-x: none;
            overflow-y: auto;
        }
    </style>
</head>

<body>

    <?php
    //store itemid array
    $itemid = array();
    $count2 = 0;
    $series = array();
    $color = array();

    function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    function random_color()
    {
        return random_color_part() . random_color_part() . random_color_part();
    }

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

        $params = array(
            "output" => array("itemid", "name", "key_", "error", "lastvalue"),
            "hostids" => $hostID,
            "search" => array("name" => "Aggregate Size Used"), //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        foreach ($result as $item) {

            if ($item["error"] == "") {
                $itemname = str_replace("[", "[[", $item["name"]);
                $itemname = str_replace("]", "]]", $itemname);

                // print $itemname."<br>";

                $itemid[] = array(
                    "id" => $item["itemid"],
                    "name" => $itemname,
                    "hostname" => $gethostname,
                    "lastvalue" => $item["lastvalue"]
                );
            }
        }
    }

    //sort array by lastvalue
    usort($itemid, function ($a, $b) {
        return ($a["lastvalue"] > $b["lastvalue"]) ? -1 : 1;
    });

    //slice the array to only top 5
    //$itemid = array_slice($itemid, 0, 5);

    // print json_encode($itemid);

    $itemdata = array();
    $aggreusedseries = "";

    foreach ($itemid as $item) {
        //perform trend.get if time range above 7 days
        if ($diff >= 604800) {
            $count = 0;
            $params = array(
                "output" => array("clock", "value_max"),
                "itemids" => $item["id"],
                "sortfield" => "clock",
                "sortorder" => "DESC",
                "time_from" => $timefrom,
                "time_till" => $timetill
            );

            //call api history.get with params
            $result = $zbx->call('trend.get', $params);
            foreach ($result as $trend) {
                $itemdata[$count] = array(
                    "clock" => $trend["clock"] * 1000,
                    "value" => $trend["value_max"]
                );
                $count++;
            }
        } else {
            $count = 0;
            $params = array(
                "output" => array("clock", "value"),
                "history" => 0,
                "itemids" => $item["id"],
                "sortfield" => "clock",
                "sortorder" => "DESC",
                "time_from" => $timefrom,
                "time_till" => $timetill
            );

            //call api history.get with params
            $result = $zbx->call('history.get', $params);
            foreach (array_reverse($result) as $history) {
                $clock = $history["clock"] * 1000;
                $itemdata[$count] = array(
                    "clock" => $clock,
                    "value" => $history["value"]
                );
                $count++;
            }
        }
        //$itemdata = json_encode($itemdata);
        $series[$count2] = array(
            "name" => $item["hostname"] . ": " . $item["name"],
            "data" => $itemdata
        );
        $count2++;
        //echo $itemdata;
        if (empty($itemdata)) {
            continue;
        } else {
            $itemdata = json_encode($itemdata);
            $aggreusedseries .= 'createSeries("value", "' . $item["hostname"] . ": " . $item["name"] . '", ' . $itemdata . ');';
            $itemdata = array();
        }
    }

    for ($i = 0; $i < $count2; $i++) {
        $color[] = "#" . random_color();
    }

    //echo json_encode($color);
    //print $aggreusedseries;

    if ($aggreusedseries == "") {
        print '<h5 style="color: red;">No data available</h5>';
    } else {
        print '<div id="aggreusedchart" style="width: auto;height: 400px;"></div>
              <div id="aggreUsedlegendwrapper">
                  <div id="aggreUsedlegenddiv"></div>
              </div>';
    }
    ?>


    </script>

</body>

</html>

<!-- SQL Stat graph -->
<script type="text/javascript">
    //am4core.options.minPolylineStep = 5;
    //am4core.useTheme(am4themes_animated);
    //am4core.useTheme(am4themes_frozen);
    am4core.options.autoDispose = true;
    // Create chart instance
    var aggreused_chart = am4core.create("aggreusedchart", am4charts.XYChart);


    aggreused_chart.numberFormatter.numberFormat = "#.## '%'";
    //aggreused_chart.fontSize = 12;

    var title = aggreused_chart.titles.create();
    title.text = 'Aggregate Size Used (%)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = aggreused_chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";


    var valueAxis = aggreused_chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
        var series = aggreused_chart.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = field;
        series.dataFields.dateX = "clock";
        series.name = name;
        series.tooltipText = "{name}: [b]{valueY}[/]";
        //series.tooltip.fontSize = 12;
        series.fillOpacity = 0.1;
        series.strokeWidth = 2;
        series.data = data;
        series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.##\')}]] [[min: {valueY.low.formatNumber(\'#.##\')}]] [[avg: {valueY.average.formatNumber(\'#.##\')}]] [[max: {valueY.high.formatNumber(\'#.##\')}]]';

        return series;
    }

    <?php print $aggreusedseries; ?>

    // Add a legend
    aggreused_chart.legend = new am4charts.Legend();
    aggreused_chart.legend.useDefaultMarker = true;
    aggreused_chart.legend.scrollable = true;
    aggreused_chart.legend.valueLabels.template.align = "right";
    aggreused_chart.legend.valueLabels.template.textAlign = "end";
    //aggreused_chart.legend.fontSize = 12;

    aggreused_chart.cursor = new am4charts.XYCursor();

    var legendContainer = am4core.create("aggreUsedlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    aggreused_chart.legend.parent = legendContainer;

    aggreused_chart.events.on("datavalidated", resizeLegend);
    aggreused_chart.events.on("maxsizechanged", resizeLegend);

    aggreused_chart.legend.events.on("datavalidated", resizeLegend);
    aggreused_chart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
        document.getElementById("aggreUsedlegenddiv").style.height = aggreused_chart.legend.contentHeight + "px";
    }

    // Set up export
    aggreused_chart.exporting.menu = new am4core.ExportMenu();
    aggreused_chart.exporting.adapter.add("data", function(data, target) {
        // Assemble data from series
        var data = [];
        aggreused_chart.series.each(function(series) {
            for (var i = 0; i < series.data.length; i++) {
                series.data[i].name = series.name;
                data.push(series.data[i]);
            }
        });
        return {
            data: data
        };
    });
</script>