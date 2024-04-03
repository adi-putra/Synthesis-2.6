<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Windows Overview</title>
    <style>
        #processlegenddiv {
            height: 150px;
        }

        #processlegendwrapper {
            max-height: 200px;
            overflow-x: none;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php

    include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

    $hostid = $_GET["hostid"] ?? array("10084");
    $timefrom = $_GET['timefrom'] ?? strtotime("today");
    $timetill = $_GET['timetill'] ?? time();
    //display time format
    $diff = $timetill - $timefrom;



    $processSeries = '';

    $params = array(
        "output" => array("name"),
        "hostids" => $hostid
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {

        $hostname = $host["name"];

        $params = array(
            "output" => array("itemid", "name"),
            "hostids" => $hostid,
            "search" => array("name" => "number of running processes"), //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        foreach ($result as $item) {
            $itemid = $item["itemid"];
            $itemname = $item["name"];

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
            $processSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
        }
    }

    ?>

    <div id="processchart" style="width: auto; height: 400px;"></div>
    <div id="processlegenddiv"></div>
    <script>
        am4core.options.autoDispose = true;

        // am4core.useTheme(am4themes_material);
        // Create chart instance
        var processchart = am4core.create("processchart", am4charts.XYChart);

        processchart.numberFormatter.numberFormat = "#";

        var title = processchart.titles.create();
        title.text = "Number of Running Processes";
        title.fontSize = 25;
        title.marginBottom = 30;
        title.align = "center";

        // Create axes
        var dateAxis = processchart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 20;
        dateAxis.renderer.labels.template.rotation = -90;
        dateAxis.renderer.labels.template.verticalCenter = "middle";
        dateAxis.renderer.labels.template.horizontalCenter = "left";

        var valueAxis = processchart.yAxes.push(new am4charts.ValueAxis());

        // Create series
        function createSeries(field, name, data) {
            var series = processchart.series.push(new am4charts.LineSeries());
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

        <?php echo $processSeries; ?>

        // Add a legend
        processchart.legend = new am4charts.Legend();
        processchart.legend.useDefaultMarker = true;
        processchart.legend.scrollable = false;
        processchart.legend.valueLabels.template.align = "right";
        processchart.legend.valueLabels.template.textAlign = "end";

        processchart.cursor = new am4charts.XYCursor();

        let legendContainer = am4core.create("processlegenddiv", am4core.Container);
        legendContainer.width = am4core.percent(100);
        legendContainer.height = am4core.percent(100);

        processchart.legend.parent = legendContainer;

        processchart.events.on("datavalidated", resizeLegend);
        processchart.events.on("maxsizechanged", resizeLegend);

        processchart.legend.events.on("datavalidated", resizeLegend);
        processchart.legend.events.on("maxsizechanged", resizeLegend);

        function resizeLegend(ev) {
            document.getElementById("processlegenddiv").style.height = processchart.legend.contentHeight + "px";
        }

        // Set up export
        processchart.exporting.menu = new am4core.ExportMenu();
        processchart.exporting.extraSprites.push({
            "sprite": legendContainer,
            "position": "bottom",
            "marginTop": 20
        });
        processchart.exporting.adapter.add("data", function(data, target) {
            // Assemble data from series
            var data = [];
            processchart.series.each(function(series) {
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