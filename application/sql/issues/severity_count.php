<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"] ?? "10084";
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;


$problem_data = array();
$count = 0;
//start fetch data
//count severity = Info
$params = array(
"output" => array("severity"),
"hostids" => array($hostid),
"severities" => "1",
"time_from" => $timefrom,
"time_till" => $timetill,
"countOutput" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get',$params);

$problem_data[$count] = array("y" => "Info", "Status" => $result);
$count++;

//count severity = Warning
$params = array(
"output" => array("severity"),
"hostids" => array($hostid),
"severities" => "2",
"time_from" => $timefrom,
"time_till" => $timetill,
"countOutput" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get',$params);

$problem_data[$count] = array("y" => "Warning", "Status" => $result);
$count++;
//$problem_data .= '{"y": ';
//$problem_data .= '"Warning", ';
//$problem_data .= '"Status": ';
//$problem_data .= $result;
//$problem_data .= "},";

//count severity = Average
$params = array(
"output" => array("severity"),
"hostids" => array($hostid),
"severities" => "3",
"time_from" => $timefrom,
"time_till" => $timetill,
"countOutput" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get',$params);

$problem_data[$count] = array("y" => "Average", "Status" => $result);
$count++;

//count severity = High
$params = array(
"output" => array("severity"),
"hostids" => array($hostid),
"severities" => "4",
"time_from" => $timefrom,
"time_till" => $timetill,
"countOutput" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get',$params);

$problem_data[$count] = array("y" => "High", "Status" => $result);
$count++;

//count severity = Disaster
$params = array(
"output" => array("severity"),
"hostids" => array($hostid),
"severities" => "5",
"time_from" => $timefrom,
"time_till" => $timetill,
"countOutput" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get',$params);

$problem_data[$count] = array("y" => "Disaster", "Status" => $result);
$count++;

$problem_data = json_encode($problem_data);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
</head>
<body>

    <div id="problemsbar" style="width: auto;height: 250px;"></div>

</body>
</html>

<!-- Problems Bar Graph -->
<script>

// Themes begin

// am4core.useTheme(am4themes_material);
// Themes end

// Create chart instance
let chart = am4core.create("problemsbar", am4charts.XYChart);

// Add data
chart.data = <?php echo $problem_data; ?>;


// Create axes

var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "y";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.minGridDistance = 60;


var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.maxPrecision = 0;
valueAxis.min = 0;

// Create series
var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.valueY = "Status";
series.dataFields.categoryX = "y";
series.name = "Status";
series.columns.template.width = am4core.percent(60);
series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
series.columns.template.fillOpacity = .8;

var columnTemplate = series.columns.template;
columnTemplate.strokeWidth = 2;
columnTemplate.strokeOpacity = 1;

chart.exporting.title = 'Problems';
chart.exporting.filePrefix = 'Problems_'+ date1;
chart.exporting.menu = new am4core.ExportMenu();

</script>