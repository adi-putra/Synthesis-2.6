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
    #volumecaplegenddiv {
      height: 300px;
    }

    #volumecaplegendwrapper {
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
        $gethostname = str_replace("Zabbix", "Synthesis", $host["name"]);
    }

    $params = array(
    "output" => array("itemid", "name", "key_", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("key_" => "dfPerCentKBytesCapacityVol"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    foreach ($result as $item) {

        if ($item["error"] == "") {
            $itemname = str_replace("[","[[",$item["name"]);
            $itemname = str_replace("]","]]",$itemname);

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
usort($itemid, function($a, $b) {
    return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
});

//slice the array to only top 5
$itemid = array_slice($itemid, 0, 10);

//print json_encode($itemid);

$itemdata = array();
$volumecapSeries = "";

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
    }
    else {
        $count = 0;
        $params = array(
            "output" => array("clock", "value"),
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
        "name" => $item["hostname"].": ".$item["name"],
        "data" => $itemdata
    );
    $count2++;
    //echo $itemdata;
    $itemdata = json_encode($itemdata);
    $volumecapSeries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
    $itemdata = array();
}

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//echo json_encode($color);
//print $volumecapSeries;
?>


<div class="row no-print">
    <div class="col-xs-12">
        <div id="volumecapchart" style="width: auto;height: 400px;"></div>
    </div>
</div>
<div class="row no-print">
    <div class="col-xs-12">
        <div id="volumecaplegendwrapper">
            <div id="volumecaplegenddiv"></div>
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
var volumecap = am4core.create("volumecapchart", am4charts.XYChart);


volumecap.numberFormatter.numberFormat = "#.## '%'";
//volumecap.fontSize = 12;

var title = volumecap.titles.create();
title.text = 'Top 10 - Volume Used Capacity (%)';
title.fontSize = 25;
title.marginBottom = 30;
title.align = 'center';

// Create axes
var dateAxis = volumecap.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 20;
dateAxis.renderer.labels.template.rotation = -90;
dateAxis.renderer.labels.template.verticalCenter = "middle";
dateAxis.renderer.labels.template.horizontalCenter = "left";


var valueAxis = volumecap.yAxes.push(new am4charts.ValueAxis());

// Create series
function createSeries(field, name, data) {
    var series = volumecap.series.push(new am4charts.LineSeries());
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

<?php print $volumecapSeries; ?>

// Add a legend
volumecap.legend = new am4charts.Legend();
volumecap.legend.useDefaultMarker = true;
volumecap.legend.scrollable = false;
volumecap.legend.valueLabels.template.align = "right";
volumecap.legend.valueLabels.template.textAlign = "end";
//volumecap.legend.fontSize = 12;

volumecap.cursor = new am4charts.XYCursor();

var legendContainer = am4core.create("volumecaplegenddiv", am4core.Container);
legendContainer.width = am4core.percent(100);
legendContainer.height = am4core.percent(100);

volumecap.legend.parent = legendContainer;

volumecap.events.on("datavalidated", resizeLegend);
volumecap.events.on("maxsizechanged", resizeLegend);

volumecap.legend.events.on("datavalidated", resizeLegend);
volumecap.legend.events.on("maxsizechanged", resizeLegend);

function resizeLegend(ev) {
    document.getElementById("volumecaplegenddiv").style.height = volumecap.legend.contentHeight + "px";
}

// Set up export
// Set up export
volumecap.exporting.menu = new am4core.ExportMenu();
volumecap.exporting.extraSprites.push({
  "sprite": legendContainer,
  "position": "bottom",
  "marginTop": 20
});
var groupid = '<?php echo $groupid ?>';
var filename = new Date().toJSON().slice(0,10) + "_" + groupid + "_volumecap";

volumecap.exporting.filePrefix = filename;
volumecap.exporting.useWebFonts = true;
</script>

<script>
volumecap.events.on('ready', () => {
    countcheck = countcheck + 1;

    // pass graph png to array,
    // graphpng[0] = volumecap.exporting.pdfmake;
    // graphpng[1] = volumecap.exporting.getImage("png");

    graphpng[2] = volumecap.exporting.getImage("png");

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