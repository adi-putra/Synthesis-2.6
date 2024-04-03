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
        #topmemlegenddiv {
            height: 300px;
        }

        #topmemlegendwrapper {
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

    $params = array(
        "output" => array("name"),
        "hostids" => $hostid
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    $params = array(
        "output" => array("itemid", "name", "error", "lastvalue"),
        "hostids" => $hostid,
        "search" => array("name" => "TOP Process"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    foreach ($result as $item) {
        $itemid[] = array(
            "id" => $item["itemid"],
            "name" => $item["name"],
            "hostname" => $gethostname,
            "lastvalue" => $item["lastvalue"]
        );
    }

    //sort array by lastvalue
    usort($itemid, function($a, $b) {
        return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
    });

    //slice the array to only top 5
    $itemid = array_slice($itemid, 0, 10);

    //print json_encode($itemid);

    $itemdata = array();
    $topmemSeries = "";

    foreach ($itemid as $item) {
        //perform trend.get if time range above 7 days

        // $item["name"] = str_replace("VMware:", "", $item["name"]);
        $count = 0;
        $params = array(
            "output" => array("clock", "value"),
            "itemids" => $item["id"],
            "history" => 2,
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill
        );

        //call api history.get with params
        $result = $zbx->call('history.get', $params);

        foreach (array_reverse($result) as $history) {
            $clock = $history["clock"] * 1000;
            $history_val = json_decode($history["value"], true);
            $itemdata[$count] = array(
                "clock" => $clock,
                "name" => str_replace("zabbix", "synthesis", $history_val["Command"]),
                "value" => $history_val["Memory_Usage"]
            );
            $count++;
        }

        //$itemdata = json_encode($itemdata);

        // $series[$count2] = array(
        //     "name" => $item["hostname"] . ": " . $item["name"],
        //     "data" => $itemdata
        // );
        // $count2++;
        // //echo $itemdata;
        // $itemdata = json_encode($itemdata);
        // $topmemSeries .= 'createSeries("value", "' . $item["hostname"] . ": " . $item["name"] . '", ' . $itemdata . ');';
        // $itemdata = array();
    }

    // Organize the data into the desired format
    $organizedData = [];

    foreach ($itemdata as $item) {
        $name = $item['name'];
        $timestamp = intval($item['clock']); // Convert to milliseconds
        $value = floatval($item['value']);

        if (!isset($organizedData[$name])) {
            $organizedData[$name] = [
                'name' => $name,
                'data' => [],
            ];
        }

        $organizedData[$name]['data'][] = array(
            "clock" => $timestamp,
            "value" => $value
        );
    }

    foreach ($organizedData as $data) {
        $data_val = json_encode($data["data"]);
        $topmemSeries .= 'createSeries("value", "' . $data["name"] . '" , ' . $data_val . ');';
    }
    // print "<pre>";
    // echo $series;
    // print "</pre>";

    // die();

    // for ($i = 0; $i < $count2; $i++) {
    //     $color[] = "#" . random_color();
    // }

    //echo json_encode($color);
    // print $topmemSeries;
    ?>


    <div id="topmemchart" style="width: auto;height: 400px;"></div>
    <div id="topmemlegendwrapper">
        <div id="topmemlegenddiv"></div>
    </div>


    <!--<div id="chart">
</div>-->

</body>

</html>

<script>
    /*var options = {
    chart: {
        type: 'area',
        height: '400px',
        animations: {
            enabled: false,
        }
    },
    title: {
        text: "DATA || Write Latency",
        älign: "center",
        style: {
            fontSize:  '14px',
            fontWeight:  'bold',
            fontFamily:  undefined,
            color:  '#263238'
        },
    },
    dataLabels: {
        enabled: false
    },
    colors: <?php echo json_encode($color); ?>,
    stroke: {
        show: true,
        curve: "straight",
        lineCap: 'round',
        width: 2 
    },
    tooltip: {
        shared: true,
        x:{
        show: true,
        format: 'dd MMM yyyy hh:mm TT',
        },
    },
    legend:{
        position: 'right',
        onItemClick: {
            toggleDataSeries: false
        },
        onItemHover: {
            highlightDataSeries: false
        },
    },
    series: <?php echo json_encode($series); ?>,
    xaxis: {
        type: 'datetime',
        labels: {
            datetimeUTC: false
        }
    },
    yaxis: {
        labels: {
            formatter: function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';

                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

                const i = Math.floor(Math.log(bytes) / Math.log(k));

                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }
        },
    },
}

    var chart = new ApexCharts(document.querySelector("#chart"), options);

    chart.render();*/
</script>

<!-- SQL Stat graph -->
<script type="text/javascript">
    am4core.options.autoDispose = true;
    //am4core.options.minPolylineStep = 5;
    //am4core.useTheme(am4themes_animated);
    //am4core.useTheme(am4themes_frozen);
    // Create chart instance
    var topmem_chart = am4core.create("topmemchart", am4charts.XYChart);


    topmem_chart.numberFormatter.numberFormat = "#.## '%'";
    // topmem_chart.numberFormatter.bigNumberPrefixes = [
    //   { "number": 1e+3, "suffix": "Khz" },
    //   { "number": 1e+6, "suffix": "Mhz" },
    //   { "number": 1e+9, "suffix": "Ghz" }
    // ];
    //topmem_chart.fontSize = 12;

    var title = topmem_chart.titles.create();
    title.text = 'TOP: Memory Usage (%)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = topmem_chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";


    var valueAxis = topmem_chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
        var series = topmem_chart.series.push(new am4charts.LineSeries());
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

    <?php print $topmemSeries; ?>

    // Add a legend
    topmem_chart.legend = new am4charts.Legend();
    topmem_chart.legend.useDefaultMarker = true;
    topmem_chart.legend.scrollable = true;
    topmem_chart.legend.valueLabels.template.align = "right";
    topmem_chart.legend.valueLabels.template.textAlign = "end";
    //topmem_chart.legend.fontSize = 12;

    topmem_chart.cursor = new am4charts.XYCursor();

    var legendContainer = am4core.create("topmemlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    topmem_chart.legend.parent = legendContainer;

    topmem_chart.events.on("datavalidated", resizeLegend);
    topmem_chart.events.on("maxsizechanged", resizeLegend);

    topmem_chart.legend.events.on("datavalidated", resizeLegend);
    topmem_chart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
        document.getElementById("topmemlegenddiv").style.height = topmem_chart.legend.contentHeight + "px";
    }

    // Set up export
    topmem_chart.exporting.menu = new am4core.ExportMenu();
    topmem_chart.exporting.adapter.add("data", function(data, target) {
        // Assemble data from series
        var data = [];
        topmem_chart.series.each(function(series) {
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