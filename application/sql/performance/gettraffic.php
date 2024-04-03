<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET['hostid'] ?? "10084";
$timefrom = (int)$_GET['timefrom'] ?? strtotime("today");
$timetill = (int)$_GET['timetill'] ?? time();
//display time format
$diff = $timetill - $timefrom;

//declaring arrays
$ingraphname = array();
$outgraphname = array();
$itemin = array();
$itemout = array();
$intraffic = array();
$outtraffic = array();
$chartexp = ""; //to pass to js for chartsPDF()
$perf_gcount = 1; //to count total graph in performance
//////INCOMING
$params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "incoming network traffic"), //seach id contains specific word
);
//call api method
$result = $zbx->call('item.get', $params);

foreach ($result as $item) {
    $itemin[$perf_gcount] = $item["itemid"];
    if (strpos($item["name"], "[") !== false) {
        $chartcount = "netchart" . $perf_gcount;
        $chartexp .= $chartcount . ".exporting.getImage('png'),";
        $ingraphname[$perf_gcount] = str_replace("[", "[[", $item["name"]);
        $ingraphname[$perf_gcount] = str_replace("]", "]]", $ingraphname[$perf_gcount]);
    } else {
        $chartcount = "netchart" . $perf_gcount;
        $ingraphname[$perf_gcount] = $item["name"];
        $chartexp .= $chartcount . ".exporting.getImage('png'),";
    }
    $perf_gcount++;
}

$perf_gcount = 1; //to count total data
//////OUTGOING
$params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "outgoing network traffic"), //seach id contains specific word
);
//call api method
$result = $zbx->call('item.get', $params);

foreach ($result as $item) {
    $itemout[$perf_gcount] = $item["itemid"];
    if (strpos($item["name"], "[") !== false) {
        $outgraphname[$perf_gcount] = str_replace("[", "[[", $item["name"]);
        $outgraphname[$perf_gcount] = str_replace("]", "]]", $outgraphname[$perf_gcount]);
    } else {
        $outgraphname[$perf_gcount] = $item["name"];
    }
    $perf_gcount++;
}

for ($i = 1; $i < $perf_gcount; $i++) {

    if ($diff > 604800) { //if data is more than 20 hours, use trend.get
        $params = array(
            "output" => array("clock", "value_max"),
            "itemids" => $itemin[$i],
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill
        );

        //call api history.get with params
        $result = $zbx->call('trend.get', $params);
        $clock = array();
        $count2 = 1; //to count every value of in and out traffic
        foreach ($result as $row) {
            $clock[$count2] = $row["clock"] * 1000;
            $intraffic[$count2] = $row["value_max"] / 1000;
            $count2++;
        }

        $params = array(
            "output" => array("clock", "value_max"),
            "itemids" => $itemout[$i],
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill
        );

        //call api history.get with params
        $result = $zbx->call('trend.get', $params);
        $count2 = 1; //to count every value of in and out traffic
        foreach ($result as $row) {
            $outtraffic[$count2] = $row["value_max"] / 1000;
            $count2++;
        }
    } else {
        $params = array(
            "output" => array("clock", "value"),
            "itemids" => $itemin[$i],
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill
        );

        //call api history.get with params
        $result = $zbx->call('history.get', $params);
        $clock = array();
        $count2 = 1; //to count every value of in and out traffic
        foreach (array_reverse($result) as $row) {
            $clock[$count2] = $row["clock"] * 1000;
            $intraffic[$count2] = $row["value"] / 1000;
            $count2++;
        }

        $params = array(
            "output" => array("clock", "value"),
            "itemids" => $itemout[$i],
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill
        );

        //call api history.get with params
        $result = $zbx->call('history.get', $params);
        $count2 = 1; //to count every value of in and out traffic
        foreach (array_reverse($result) as $row) {
            $outtraffic[$count2] = $row["value"] / 1000;
            $count2++;
        }
    }

    //collect data from both arrays, in and out
    $traffic_data = "";
    for ($j = 1; $j < $count2; $j++) {
        if (isset($clock[$j]) == false) {
            break;
        } else {
            $traffic_data .= "{clock: ";
            $traffic_data .= $clock[$j];
            $traffic_data .= ", ";
            $traffic_data .= "value1: ";
            $traffic_data .= $intraffic[$j];
            $traffic_data .= ", ";
            $traffic_data .= "value2: ";
            $traffic_data .= $outtraffic[$j];
            $traffic_data .= "}, ";
        }
    }
}

?>
<div id="trafficchart" style="width: auto; height: 400px;"><? echo  $timetill . '-' . $timefrom ?></div>

<div id="trafficlegenddiv"></div>


<script>
    var date1 = Date.now();
    
    function generateChartData() {
        var chartData = [<?php echo substr($ingraphname[$i], 9) ?>];
        $graphname = ucfirst($graphname1);

        $chartcount = "netchart".$i;

        return chartData;
    }

    // Themes begin
    // am4core.useTheme(am4themes_material);
    // Themes end

    var chartcount = am4core.create('trafficchart', am4charts.XYChart);

    //show in bytes
    chartcount.numberFormatter.numberFormat = '#.00b';

    // Add data
    chartcount.data = generateChartData();

    var title = chartcount.titles.create();
    title.text = '$graphname';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';


    chartcount.dateFormatter.inputDateFormat = 'dd-mm-yyyy';
    var dateAxis = chartcount.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 60;
    dateAxis.startLocation = 0.5;
    dateAxis.endLocation = 0.5;

    var valueAxis = chartcount.yAxes.push(new am4charts.ValueAxis());
    valueAxis.tooltip.disabled = true;
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = 'middle';
    dateAxis.renderer.labels.template.horizontalCenter = 'left';

    var series = chartcount.series.push(new am4charts.LineSeries());
    series.dataFields.dateX = 'clock';
    series.name = '<? echo $ingraphname[$i] ?>';
    series.dataFields.valueY = 'value1';
    series.tooltip.background.fill = am4core.color('#FFF');
    series.tooltipText = '[#000]{valueY.value}[/]';
    series.tooltip.getStrokeFromObject = true;
    series.tooltip.background.strokeWidth = 3;
    series.tooltip.getFillFromObject = false;
    series.fillOpacity = 0.6;
    series.strokeWidth = 2;
    series.stacked = false;
    series.fill = am4core.color('#28BB0D');
    series.stroke = am4core.color('#28BB0D');
    series.legendSettings.valueText = '[[last: {valueY.close}]] [[min: {valueY.low}]] [[avg: {valueY.average}]] [[max: {valueY.high}]]';


    var series2 = chartcount.series.push(new am4charts.LineSeries());
    series2.name = '<? echo $outgraphname[$i] ?>';
    series2.dataFields.dateX = 'clock';
    series2.dataFields.valueY = 'value2';
    series2.tooltip.background.fill = am4core.color('#FFF');
    series2.tooltipText = '[#000]{valueY.value}[/]';
    series2.tooltip.getFillFromObject = false;
    series2.tooltip.getStrokeFromObject = true;
    series2.tooltip.background.strokeWidth = 3;
    series2.sequencedInterpolation = true;
    series2.fillOpacity = 0.6;
    series2.stacked = false;
    series2.strokeWidth = 2;
    series2.fill = am4core.color('#236CF5');
    series2.stroke = am4core.color('#236CF5');
    series2.legendSettings.valueText = '[[last: {valueY.close}]] [[min: {valueY.low}]] [[avg: {valueY.average}]] [[max: {valueY.high}]]';

    chartcount.cursor = new am4charts.XYCursor();
    chartcount.cursor.xAxis = dateAxis;
    chartcount.scrollbarX = new am4core.Scrollbar();

    // Add a legend
    chartcount.legend = new am4charts.Legend();
    chartcount.legend.useDefaultMarker = true;
    chartcount.legend.position = 'bottom';
    chartcount.legend.contentAlign = 'left';

    // axis ranges
    var range = dateAxis.axisRanges.create();
    range.date = new Date(2001, 0, 1);
    range.endDate = new Date(2003, 0, 1);
    range.axisFill.fill = chartcount.colors.getIndex(7);
    range.axisFill.fillOpacity = 0.2;

    var range2 = dateAxis.axisRanges.create();
    range2.date = new Date(2007, 0, 1);
    range2.grid.stroke = chartcount.colors.getIndex(7);
    range2.grid.strokeOpacity = 0.6;
    range2.grid.strokeDasharray = '5,2';



    chartcount.exporting.title = 'Traffic';
    chartcount.exporting.filePrefix = 'Traffic' + '_' + date1;
    chartcount.exporting.menu = new am4core.ExportMenu();
</script>