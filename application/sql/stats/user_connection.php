<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Windows Overview</title>
    <style>
        #userconnlegenddiv {
            height: 150px;
        }

        #userconnlegendwrapper {
            max-height: 200px;
            overflow-x: none;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php

    include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

    $hostid = $_GET["hostid"] ?? array("10713");
    $timefrom = $_GET['timefrom'] ?? strtotime("today");
    $timetill = $_GET['timetill'] ?? time();
    //display time format
    $diff = $timetill - $timefrom;



    $userconnSeries = '';

    $params = array(
        "output" => array("name"),
        "hostids" => $hostid
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {

        $hostname = $host["name"];

        $params = array(
            "output" => array("itemid", "name", "error"),
            "hostids" => $hostid,
            "search" => array("name" => "Users Connected") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        foreach ($result as $item) {
            $itemid = $item["itemid"];
            $itemname = $item["name"];
            $itemerror = $item["error"];

            //perform trend.get if time range above 7 days
            if ($diff >= 604800) {
                $params = array(
                    "output" => array("clock", "value_max"),
                    "itemids" => $itemid,
                    "sortfield" => "clock",
                    "sortorder" => "DESC",
                    "time_from" => $timefrom,
                    "time_till" => $timetill
                );

                //call api history.get with params
                $result = $zbx->call('trend.get', $params);
                $chart_data = '';
                foreach ($result as $row) {
                    //$row["clock"] = date(" H:i\ ", $row["clock"]);
                    $row["clock"] = $row["clock"] * 1000;
                    $chart_data .= "{clock: ";
                    $chart_data .= $row["clock"];
                    $chart_data .= ", ";
                    $chart_data .= "value: ";
                    $chart_data .= $row["value_max"];
                    $chart_data .= "},";
                }
            } else {
                $params = array(
                    "output" => array("clock", "value"),
                    "history" => 0,
                    "itemids" => $itemid,
                    "sortfield" => "clock",
                    "sortorder" => "DESC",
                    "time_from" => $timefrom,
                    "time_till" => $timetill
                );

                //call api history.get with params
                $result = $zbx->call('history.get', $params);
                $chart_data = '';
                foreach (array_reverse($result) as $row) {
                    //$row["clock"] = date(" H:i\ ", $row["clock"]);
                    $row["clock"] = $row["clock"] * 1000;
                    $chart_data .= "{clock: ";
                    $chart_data .= $row["clock"];
                    $chart_data .= ", ";
                    $chart_data .= "value: ";
                    $chart_data .= $row["value"];
                    $chart_data .= "},";
                }
            }
            $chart_data = substr($chart_data, 0, -1);
            $userconnSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
        }
    }

    if (!empty($itemerror)) {
        echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
    } else if ($result == false) {
        echo '<div><h5 class="text-danger">No data available</h5></div>';
    } else {
        echo '
        <div id="userconnchart" style="width: auto; height: 400px;"></div>
        <div id="userconnlegenddiv"></div>
    ';
    }

    ?>


    <script>
        am4core.options.autoDispose = true;

        // am4core.useTheme(am4themes_material);
        // Create chart instance
        var userconnchart = am4core.create("userconnchart", am4charts.XYChart);

        userconnchart.numberFormatter.numberFormat = "#";

        var title = userconnchart.titles.create();
        title.text = "User Connections";
        title.fontSize = 25;
        title.marginBottom = 30;
        title.align = "center";

        // Create axes
        var dateAxis = userconnchart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 20;
        dateAxis.renderer.labels.template.rotation = -90;
        dateAxis.renderer.labels.template.verticalCenter = "middle";
        dateAxis.renderer.labels.template.horizontalCenter = "left";

        var valueAxis = userconnchart.yAxes.push(new am4charts.ValueAxis());
        valueAxis.min = 0;

        // Create series
        function createSeries(field, name, data) {
            var series = userconnchart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = field;
            series.dataFields.dateX = "clock";
            series.name = name;
            series.tooltipText = "{name}: [b]{valueY}[/]";
            series.fillOpacity = 0.1;
            series.strokeWidth = 2;
            series.data = data;
            series.legendSettings.valueText = '[[last: {valueY.close}]] [[min: {valueY.low}]] [[avg: {valueY.average}]] [[max: {valueY.high}]]';

            return series;
        }

        <?php echo $userconnSeries; ?>

        // Add a legend
        userconnchart.legend = new am4charts.Legend();
        userconnchart.legend.useDefaultMarker = true;
        userconnchart.legend.scrollable = false;
        userconnchart.legend.valueLabels.template.align = "right";
        userconnchart.legend.valueLabels.template.textAlign = "end";

        userconnchart.cursor = new am4charts.XYCursor();

        let legendContainer = am4core.create("userconnlegenddiv", am4core.Container);
        legendContainer.width = am4core.percent(100);
        legendContainer.height = am4core.percent(100);

        userconnchart.legend.parent = legendContainer;

        userconnchart.events.on("datavalidated", resizeLegend);
        userconnchart.events.on("maxsizechanged", resizeLegend);

        userconnchart.legend.events.on("datavalidated", resizeLegend);
        userconnchart.legend.events.on("maxsizechanged", resizeLegend);

        function resizeLegend(ev) {
            document.getElementById("userconnlegenddiv").style.height = userconnchart.legend.contentHeight + "px";
        }

        // Set up export
        userconnchart.exporting.menu = new am4core.ExportMenu();
        userconnchart.exporting.extraSprites.push({
            "sprite": legendContainer,
            "position": "bottom",
            "marginTop": 20
        });
        userconnchart.exporting.adapter.add("data", function(data, target) {
            // Assemble data from series
            var data = [];
            userconnchart.series.each(function(series) {
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
</body>

</html>