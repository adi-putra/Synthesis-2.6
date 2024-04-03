<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10611", "10615", "10619");
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
  <!-- Styles -->
  <style>
    #total4625_div {
      height: 500px;
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

//$chart_title = "IIS: Bytes Sent/Received per second";

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
        "search" => array("name" => "Event ID 4625 : Failed Logon"), //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (!empty($result)) {
            foreach ($result as $item) {
                $chart_title = $item["name"];
                $itemid[] = array(
                    "id" => $item["itemid"], 
                    "name" => $item["name"],
                    "hostname" => $gethostname,
                    "lastvalue" => $item["lastvalue"]
                );
            }
        }
    }
}

//slice the array to only top 5
//$itemid = array_slice($itemid, 0, 5);

//print json_encode($itemid);

$itemdata = array();
$itemseries = "";

$counthost = 0;

foreach ($itemid as $item) {
    
    $params = array(
        "output" => "extend",
        "history" => 2,
        "itemids" => $item["id"],
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill,
        "countOutput" => true
    );

    //call api history.get with params
    $result = $zbx->call('history.get', $params);
    if (!empty($result)) {
        $counthost++;

        $itemdata[] = array(
            "category" => $item["hostname"],
            "total" => $result
        );
    }
    
    //$itemdata = json_encode($itemdata);
    // $series[$count2] = array(
    //     "name" => $item["hostname"].": ".$item["name"],
    //     "data" => $itemdata
    // );
    // $count2++;
    //echo $itemdata;
    //$itemdata = json_encode($itemdata);
    //$itemseries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
    //$itemseries .= $itemdata;
    //$itemdata = array();
}

//sort array by lastvalue
usort($itemdata, function($a, $b) {
    return ($a["total"] > $b["total"])?-1:1;
});

$itemdata = json_encode($itemdata);
$itemseries .= $itemdata;

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//print $counthost;

//echo json_encode($itemseries);
// print $itemseries;
?>


<!-- HTML -->
<div id="total4625_div"></div>
    

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

<!-- Chart code -->
<script>

// Themes begin
//am4core.useTheme(am4themes_animated);
// Themes end

am4core.options.autoDispose = true;

// Themes begin
// am4core.useTheme(am4themes_animated);
// Themes end

// Create chart instance
var total4625 = am4core.create("total4625_div", am4charts.XYChart);
total4625.scrollbarX = new am4core.Scrollbar();

// Add data
total4625.data = <?php echo $itemseries; ?>;

// Create axes
var categoryAxis = total4625.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "category";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.minGridDistance = 30;
categoryAxis.renderer.labels.template.horizontalCenter = "right";
categoryAxis.renderer.labels.template.verticalCenter = "middle";
categoryAxis.renderer.labels.template.rotation = 270;
categoryAxis.tooltip.disabled = true;
categoryAxis.renderer.minHeight = 110;

var valueAxis = total4625.yAxes.push(new am4charts.ValueAxis());
valueAxis.renderer.minWidth = 50;

// Create series
var series = total4625.series.push(new am4charts.ColumnSeries());
series.sequencedInterpolation = true;
series.dataFields.valueY = "total";
series.dataFields.categoryX = "category";
series.tooltipText = "[{categoryX}: bold]{valueY}[/]";
series.columns.template.strokeWidth = 0;

series.tooltip.pointerOrientation = "vertical";

series.columns.template.column.cornerRadiusTopLeft = 10;
series.columns.template.column.cornerRadiusTopRight = 10;
series.columns.template.column.fillOpacity = 0.8;

// on hover, make corner radiuses bigger
var hoverState = series.columns.template.column.states.create("hover");
hoverState.properties.cornerRadiusTopLeft = 0;
hoverState.properties.cornerRadiusTopRight = 0;
hoverState.properties.fillOpacity = 1;

series.columns.template.adapter.add("fill", function(fill, target) {
  return total4625.colors.getIndex(target.dataItem.index);
});

// Cursor
total4625.cursor = new am4charts.XYCursor();

</script>