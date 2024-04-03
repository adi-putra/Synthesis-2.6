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
    #chart4625_div {
      height: 700px;
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
        "time_till" => $timetill
    );

    //call api history.get with params
    $result = $zbx->call('history.get', $params);
    if (!empty($result)) {
        $counthost++;

        foreach (array_reverse($result) as $history) {
            $clock_start = ($history["timestamp"] * 1000);
            $clock_end = ($history["clock"] + 180) * 1000;
            $itemdata[] = array(
                "category" => $item["hostname"],
                "start" => $clock_start,
                "end" => $clock_end,
                "color" => "#FF0000",
                "task" => $history["value"]
            );
        }
    }
    
    //$itemdata = json_encode($itemdata);
    $series[$count2] = array(
        "name" => $item["hostname"].": ".$item["name"],
        "data" => $itemdata
    );
    $count2++;
    //echo $itemdata;
    //$itemdata = json_encode($itemdata);
    //$itemseries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
    //$itemseries .= $itemdata;
    //$itemdata = array();
}

$itemdata = json_encode($itemdata);
$itemseries .= $itemdata;

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//print $counthost;

//set size for chart based on num of host retreive data
if ($counthost < 4) {
    $cellsize = 1;
    $chart_style = "height: 300px;";
}
else if ($counthost >= 4 && $counthost < 10) {
    $cellsize = 2;
    $chart_style = "height: 500px;";
}
else if ($counthost >= 10 && $counthost < 14) {
    $cellsize = 3;
    $chart_style = "height: 700px;";
}
else if ($counthost >= 14 && $counthost < 20) {
    $cellsize = 5;
    $chart_style = "height: 800px;";
}
else if ($counthost >= 20) {
    $cellsize = 5;
    $chart_style = "height: 900px;";
}

//echo json_encode($itemseries);
//print $itemseries;
?>


<!-- HTML -->
<div id="chart4625_div" style="<?php echo $chart_style;?>"></div>
    

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

var chart_4625 = am4core.create("chart4625_div", am4charts.XYChart);
chart_4625.hiddenState.properties.opacity = 0; // this creates initial fade-in
//chart_4625.fontSize = 10;

/*var title = chart_4625.titles.create();
title.text = '<?php echo $chart_title; ?>';
title.fontSize = 25;
title.marginBottom = 30;
title.align = 'center';*/

chart_4625.paddingRight = 30;
chart_4625.dateFormatter.inputDateFormat = "dd-MM-yyyy HH:mm:ss a";

chart_4625.dateFormatter.dateFormat = "dd-MM-yyyy HH:mm:ss a";
chart_4625.dateFormatter.inputDateFormat = "dd-MM-yyyy HH:mm:ss a";

var categoryAxis = chart_4625.yAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "category";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.inversed = true;

var dateAxis = chart_4625.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 60;
//dateAxis.baseInterval = { count: 1, timeUnit: "day" };
// dateAxis.max = new Date(2018, 0, 1, 24, 0, 0, 0).getTime();
//dateAxis.strictMinMax = true;
dateAxis.renderer.tooltipLocation = 0;

//disable horizontal lines    
//dateAxis.renderer.grid.template.strokeWidth = 0;

//disable vertical lines
categoryAxis.renderer.grid.template.strokeWidth = 0;

chart_4625.data = <?php print $itemseries; ?>

var series1 = chart_4625.series.push(new am4charts.ColumnSeries());
series1.columns.template.height = am4core.percent(70);
series1.columns.template.tooltipText = "[bold]{openDateX}[/]\n Host: {category}\n Value: {task}\n";
series1.dataFields.openDateX = "start";
series1.dataFields.dateX = "end";
series1.dataFields.categoryY = "category";
series1.columns.template.propertyFields.fill = "color"; // get color from data
series1.columns.template.propertyFields.stroke = "color";
series1.columns.template.strokeOpacity = 1;
series1.tooltip.getFillFromObject = false;
series1.tooltip.background.fill = am4core.color("#FFFFFF");
series1.tooltip.label.fill = am4core.color("#000000");

chart_4625.scrollbarX = new am4core.Scrollbar();




</script>