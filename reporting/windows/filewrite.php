<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10713");
$groupid = $_GET["groupid"];
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
    #filewritelegenddiv {
      height: 300px;
    }

    #filewritelegendwrapper {
      max-height: 400px;
      overflow-x: none;
      overflow-y: auto;
      width: 900px;
      margin:0 auto;
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

function random_color_part() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

function random_color() {
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

        $params = array(
        "output" => array("itemid", "name", "error", "lastvalue"),
        "hostids" => $hostID,
        "search" => array("name" => "file write"), //seach id contains specific word
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
    }
}

//sort array by lastvalue
usort($itemid, function($a, $b) {
    return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
});

//slice the array to only top 5
$itemid = array_slice($itemid, 0, 10);

// print json_encode($itemid);

$itemdata = array();
$filewriteSeries = "";

foreach ($itemid as $item) {
    //declare highest val
    $max_val = 0;

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
    }
    else {
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
    // $series[$count2] = array(
    //     "name" => $item["hostname"].": ".$item["name"],
    //     "data" => $itemdata,
    // );
    // $count2++;

    //echo $itemdata;
    $itemdata = json_encode($itemdata);
    $filewriteSeries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
    $itemdata = array();
}

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//echo json_encode($color);
// print "<pre>";
// print $filewriteSeries;
// print "</pre>";
?>


<div class="row no-print">
    <div class="col-xs-12">
        <div id="filewritechart" style="width: auto;height: 400px;"></div>
    </div>
</div>
<div class="row no-print">
    <div class="col-xs-12">
        <div id="filewritelegendwrapper">
            <div id="filewritelegenddiv"></div>
        </div>
    </div>
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
    colors: <?php echo json_encode($color);?>,
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
    series: <?php echo json_encode($series);?>,
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
//am4core.options.minPolylineStep = 5;
//am4core.useTheme(am4themes_animated);
//am4core.useTheme(am4themes_frozen);
// Create chart instance
var filewrite_chart = am4core.create("filewritechart", am4charts.XYChart);


filewrite_chart.numberFormatter.numberFormat = "#.##b";
//filewrite_chart.fontSize = 12;

var title = filewrite_chart.titles.create();
title.text = 'Top 10 - File write bytes per second';
title.fontSize = 25;
title.marginBottom = 30;
title.align = 'center';

// Create axes
var dateAxis = filewrite_chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 20;
dateAxis.renderer.labels.template.rotation = -90;
dateAxis.renderer.labels.template.verticalCenter = "middle";
dateAxis.renderer.labels.template.horizontalCenter = "left";


var valueAxis = filewrite_chart.yAxes.push(new am4charts.ValueAxis());

// Create series
function createSeries(field, name, data) {
    var series = filewrite_chart.series.push(new am4charts.LineSeries());
    series.dataFields.valueY = field;
    series.dataFields.dateX = "clock";
    series.name = name;
    series.tooltipText = "{name}: [b]{valueY}[/]";
    //series.tooltip.fontSize = 12;
    series.fillOpacity = 0.1;
    series.strokeWidth = 2;
    series.data = data;
    series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.##a\')}]] [[min: {valueY.low.formatNumber(\'#.##a\')}]] [[avg: {valueY.average.formatNumber(\'#.##a\')}]] [[max: {valueY.high.formatNumber(\'#.##a\')}]]';

    return series;
}

<?php print $filewriteSeries; ?>

// Add a legend
filewrite_chart.legend = new am4charts.Legend();
filewrite_chart.legend.useDefaultMarker = true;
filewrite_chart.legend.scrollable = false;
filewrite_chart.legend.valueLabels.template.align = "right";
filewrite_chart.legend.valueLabels.template.textAlign = "end";
//filewrite_chart.legend.fontSize = 12;

filewrite_chart.cursor = new am4charts.XYCursor();

var legendContainer = am4core.create("filewritelegenddiv", am4core.Container);
legendContainer.width = am4core.percent(100);
legendContainer.height = am4core.percent(100);

filewrite_chart.legend.parent = legendContainer;

filewrite_chart.events.on("datavalidated", resizeLegend);
filewrite_chart.events.on("maxsizechanged", resizeLegend);

filewrite_chart.legend.events.on("datavalidated", resizeLegend);
filewrite_chart.legend.events.on("maxsizechanged", resizeLegend);

function resizeLegend(ev) {
    document.getElementById("filewritelegenddiv").style.height = filewrite_chart.legend.contentHeight + "px";
}

// Set up export
// Set up export
filewrite_chart.exporting.menu = new am4core.ExportMenu();
filewrite_chart.exporting.extraSprites.push({
  "sprite": legendContainer,
  "position": "bottom",
  "marginTop": 20
});
var groupid = '<?php echo $groupid ?>';
var filename = new Date().toJSON().slice(0,10) + "_" + groupid + "_filewrite";

filewrite_chart.exporting.filePrefix = filename;
filewrite_chart.exporting.useWebFonts = true;
</script>

<script>
filewrite_chart.events.on('ready', () => {
    countcheck = countcheck + 1;

    //pass graph png to array,
    graphpng[4] = filewrite_chart.exporting.getImage("png");

    if (countcheck == countcheck_total) {
        $("#reportready").html("Report is ready!");
        $('#chooseprint').show();
        $('#reportdiv').show();
    }
    else {
        $("#reportready").html("Generating report...(" + countcheck + "/" + countcheck_total + ")");
    }
});
</script>