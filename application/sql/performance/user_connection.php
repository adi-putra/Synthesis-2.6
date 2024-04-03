<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Windows Overview</title>
    <style>
        #userconn1legenddiv {
            height: 150px;
        }

        #userconn1legendwrapper {
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



    $userconn1Series = '';

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
            $userconn1Series .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
        }
    }

    if (!empty($itemerror)) {
        echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
    } else if ($result == false) {
        echo '<div><h5 class="text-danger">No data available</h5></div>';
    } else {
        echo '
        <div id="userconn1chart" style="width: auto; height: 400px;"></div>
        <div id="userconn1legenddiv"></div>
    ';
    }

    ?>


    <script>
        am4core.options.autoDispose = true;

        // am4core.useTheme(am4themes_material);
        // Create chart instance
        var userconn1chart = am4core.create("userconn1chart", am4charts.XYChart);

        userconn1chart.numberFormatter.numberFormat = "#";

        var title = userconn1chart.titles.create();
        title.text = "User Connections";
        title.fontSize = 25;
        title.marginBottom = 30;
        title.align = "center";

        // Create axes
        var dateAxis = userconn1chart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 20;
        dateAxis.renderer.labels.template.rotation = -90;
        dateAxis.renderer.labels.template.verticalCenter = "middle";
        dateAxis.renderer.labels.template.horizontalCenter = "left";

        var valueAxis = userconn1chart.yAxes.push(new am4charts.ValueAxis());
        valueAxis.min = 0;

        // Create series
        function createSeries(field, name, data) {
            var series = userconn1chart.series.push(new am4charts.LineSeries());
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

        <?php echo $userconn1Series; ?>

        // Add a legend
        userconn1chart.legend = new am4charts.Legend();
        userconn1chart.legend.useDefaultMarker = true;
        userconn1chart.legend.scrollable = false;
        userconn1chart.legend.valueLabels.template.align = "right";
        userconn1chart.legend.valueLabels.template.textAlign = "end";

        userconn1chart.cursor = new am4charts.XYCursor();

        let legendContainer = am4core.create("userconn1legenddiv", am4core.Container);
        legendContainer.width = am4core.percent(100);
        legendContainer.height = am4core.percent(100);

        userconn1chart.legend.parent = legendContainer;

        userconn1chart.events.on("datavalidated", resizeLegend);
        userconn1chart.events.on("maxsizechanged", resizeLegend);

        userconn1chart.legend.events.on("datavalidated", resizeLegend);
        userconn1chart.legend.events.on("maxsizechanged", resizeLegend);

        function resizeLegend(ev) {
            document.getElementById("userconn1legenddiv").style.height = userconn1chart.legend.contentHeight + "px";
        }

        // Set up export
        userconn1chart.exporting.menu = new am4core.ExportMenu();
        userconn1chart.exporting.extraSprites.push({
            "sprite": legendContainer,
            "position": "bottom",
            "marginTop": 20
        });
        userconn1chart.exporting.adapter.add("data", function(data, target) {
            // Assemble data from series
            var data = [];
            userconn1chart.series.each(function(series) {
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