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

function get_threshold($expression)
{
$specialchar = "<>=";
$threshold = strpbrk($expression, $specialchar);
$threshold = preg_replace('/[^A-Za-z0-9\-]/', '', $threshold);
return $threshold;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title></title>
  <style>
    #memoryutillegenddiv {
      height: 300px;
    }

    #memoryutillegendwrapper {
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

        $params = array(
        "output" => array("itemid", "name", "error", "lastvalue"),
        "hostids" => $hostID,
        "search" => array("name" => "used memory in %"), //seach id contains specific word
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

//print json_encode($itemid);

$itemdata = array();
$category_data = array();
$memoryutilSeries = "";
$thresholdSeries = "";
$triggerdata = array();

foreach ($itemid as $item) {

    // get universal trigger
    if ($item["lastvalue"] != 0) {
      $params = array(
        "output" => array("triggerid", "expression","description"),
        "itemids" => $item["id"],
        "expandExpression" => true
      );
      //call api method
      $result = $zbx->call('trigger.get', $params);
      foreach ($result as $trigg) {

        // print $trigg["description"];

        $expression = $trigg["expression"];
        $threshold = get_threshold($expression);
        // $threshold_name = substr($trigg["description"], 0, 8);
        $threshold_name = "High memory threshold";
        $triggerdata[] = $threshold;
      }
      //print_r($triggerdata);
    }

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
    $series[$count2] = array(
        "name" => $item["hostname"].": ".$item["name"],
        "data" => $itemdata
    );
    $count2++;
    //echo $itemdata;
    $itemdata = json_encode($itemdata);
    $memoryutilSeries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
    $itemdata = array();
}

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//echo json_encode($color);
//print $memoryutilSeries;
?>


<div class="row no-print">
    <div class="col-xs-12">
        <div id="memoryutilchart" style="width: auto;height: 400px;"></div>
    </div>
</div>
<div class="row no-print">
    <div class="col-xs-12">
        <div id="memoryutillegendwrapper">
            <div id="memoryutillegenddiv"></div>
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
var memoryutil_chart = am4core.create("memoryutilchart", am4charts.XYChart);


memoryutil_chart.numberFormatter.numberFormat = "#.## '%'";
//memoryutil_chart.fontSize = 12;

var title = memoryutil_chart.titles.create();
title.text = 'Top 10 - Used Memory (%)';
title.fontSize = 25;
title.marginBottom = 30;
title.align = 'center';

// Create axes
var dateAxis = memoryutil_chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 20;
dateAxis.renderer.labels.template.rotation = -90;
dateAxis.renderer.labels.template.verticalCenter = "middle";
dateAxis.renderer.labels.template.horizontalCenter = "left";


var valueAxis = memoryutil_chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.min = 0;
valueAxis.max = 100;

// Create series
function createSeries(field, name, data) {
    var series = memoryutil_chart.series.push(new am4charts.LineSeries());
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

<?php print $memoryutilSeries; ?>

// Add a legend
memoryutil_chart.legend = new am4charts.Legend();
memoryutil_chart.legend.useDefaultMarker = true;
memoryutil_chart.legend.scrollable = false;
memoryutil_chart.legend.valueLabels.template.align = "right";
memoryutil_chart.legend.valueLabels.template.textAlign = "end";
//memoryutil_chart.legend.fontSize = 12;

memoryutil_chart.cursor = new am4charts.XYCursor();

var legendContainer = am4core.create("memoryutillegenddiv", am4core.Container);
legendContainer.width = am4core.percent(100);
legendContainer.height = am4core.percent(100);

memoryutil_chart.legend.parent = legendContainer;

// Add series range
// Create axis ranges
function createRange(value, color) {

    var thresholdname = "<?php echo $threshold_name; ?>";

    var range = valueAxis.axisRanges.create();
    range.value = value;
    range.grid.stroke = color;
    range.grid.strokeWidth = 2;
    range.grid.strokeOpacity = 1;
    range.label.inside = true;
    range.label.text = thresholdname + " - " + value + "%",
    range.label.fill = range.grid.stroke;
    range.label.align = "center";
    range.label.verticalCenter = "bottom";
    range.grid.above = true;
}

var trigger = <?php echo json_encode($triggerdata); ?>;
var value = "";
var thresh = "";
for (let value in trigger) {
    thresh = trigger[value];
    createRange(thresh, am4core.color("#A96478"));
}

memoryutil_chart.events.on("datavalidated", resizeLegend);
memoryutil_chart.events.on("maxsizechanged", resizeLegend);

memoryutil_chart.legend.events.on("datavalidated", resizeLegend);
memoryutil_chart.legend.events.on("maxsizechanged", resizeLegend);

function resizeLegend(ev) {
    document.getElementById("memoryutillegenddiv").style.height = memoryutil_chart.legend.contentHeight + "px";
}

// Set up export
// Set up export
memoryutil_chart.exporting.menu = new am4core.ExportMenu();
memoryutil_chart.exporting.extraSprites.push({
  "sprite": legendContainer,
  "position": "bottom",
  "marginTop": 20
});
var groupid = '<?php echo $groupid ?>';
var filename = new Date().toJSON().slice(0,10) + "_" + groupid + "_memoryutil";

memoryutil_chart.exporting.filePrefix = filename;
memoryutil_chart.exporting.useWebFonts = true;
</script>

<script>
memoryutil_chart.events.on('ready', () => {
    countcheck = countcheck + 1;

    // pass graph png to array,
    // graphpng[0] = memoryutil_chart.exporting.pdfmake;
    // graphpng[1] = memoryutil_chart.exporting.getImage("png");

    graphpng[2] = memoryutil_chart.exporting.getImage("png");

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