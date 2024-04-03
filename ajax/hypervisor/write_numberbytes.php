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
    #writebyteslegenddiv {
      height: 300px;
    }

    #writebyteslegendwrapper {
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
        "search" => array("name" => "vmware: average read latency"), //seach id contains specific word
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

        $params = array(
        "output" => array("itemid", "name", "error", "lastvalue"),
        "hostids" => $hostID,
        "search" => array("name" => "Average number of bytes written to the disk"), //seach id contains specific word
        "limit" => 1
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
//$itemid = array_slice($itemid, 0, 5);

//print json_encode($itemid);

$itemdata = array();
$writebytesSeries = "";

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
    if (empty($itemdata)) {
      continue;
    }
    else {
      $itemdata = json_encode($itemdata);
      $writebytesSeries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
      $itemdata = array();
    }
}

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//echo json_encode($color);
//print $writebytesSeries;

if ($writebytesSeries == "") {
  print '<h5 style="color: red;">No data available</h5>';
}
else {
  print '<div id="writebyteschart" style="width: auto;height: 400px;"></div>
          <div id="writebyteslegendwrapper">
              <div id="writebyteslegenddiv"></div>
          </div>';
}
?>



    

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
var writebytes_chart = am4core.create("writebyteschart", am4charts.XYChart);


writebytes_chart.numberFormatter.numberFormat = "#.##b";
//writebytes_chart.fontSize = 12;

var title = writebytes_chart.titles.create();
title.text = 'VMWare: Average number of bytes write from the disk';
title.fontSize = 25;
title.marginBottom = 30;
title.align = 'center';

// Create axes
var dateAxis = writebytes_chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 20;
dateAxis.renderer.labels.template.rotation = -90;
dateAxis.renderer.labels.template.verticalCenter = "middle";
dateAxis.renderer.labels.template.horizontalCenter = "left";


var valueAxis = writebytes_chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.min = 0;

// Create series
function createSeries(field, name, data) {
    var series = writebytes_chart.series.push(new am4charts.LineSeries());
    series.dataFields.valueY = field;
    series.dataFields.dateX = "clock";
    series.name = name;
    series.tooltipText = "{name}: [b]{valueY}[/]";
    //series.tooltip.fontSize = 12;
    series.fillOpacity = 0.1;
    series.strokeWidth = 2;
    series.data = data;
    series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.##b\')}]] [[min: {valueY.low.formatNumber(\'#.##b\')}]] [[avg: {valueY.average.formatNumber(\'#.##b\')}]] [[max: {valueY.high.formatNumber(\'#.##b\')}]]';

    return series;
}

<?php print $writebytesSeries; ?>

// Add a legend
writebytes_chart.legend = new am4charts.Legend();
writebytes_chart.legend.useDefaultMarker = true;
writebytes_chart.legend.scrollable = true;
writebytes_chart.legend.valueLabels.template.align = "right";
writebytes_chart.legend.valueLabels.template.textAlign = "end";
//writebytes_chart.legend.fontSize = 12;

writebytes_chart.cursor = new am4charts.XYCursor();

var legendContainer = am4core.create("writebyteslegenddiv", am4core.Container);
legendContainer.width = am4core.percent(100);
legendContainer.height = am4core.percent(100);

writebytes_chart.legend.parent = legendContainer;

writebytes_chart.events.on("datavalidated", resizeLegend);
writebytes_chart.events.on("maxsizechanged", resizeLegend);

writebytes_chart.legend.events.on("datavalidated", resizeLegend);
writebytes_chart.legend.events.on("maxsizechanged", resizeLegend);

function resizeLegend(ev) {
    document.getElementById("writebyteslegenddiv").style.height = writebytes_chart.legend.contentHeight + "px";
}

// Set up export
writebytes_chart.exporting.menu = new am4core.ExportMenu();
writebytes_chart.exporting.adapter.add("data", function(data, target) {
    // Assemble data from series
    var data = [];
    writebytes_chart.series.each(function(series) {
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