<?php
include 'session.php';

$hostid = $_GET['hostid'] ?? "10434";

if (isset($_POST['submit']))
  {
  // Execute this code if the submit button is pressed.
  $timefrom1 = $_POST['timefrom'];
  $timefrom = strtotime($timefrom1);
  $timetill1 = $_POST['timetill'];
  $timetill = strtotime($timetill1);
  }
  else {
  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();
  }

  //display time format
  $diff = $timetill - $timefrom;
  if ($diff == 3600) {
    $status = "Last 1 hour";
  }
  else if ($diff < 86400) {
    $status = "Today";
  }
  else if ($diff == 86400) {
    $status = "Last 1 day";
  }
  else if ($diff == 172800) {
    $status = "Last 2 days";
  }
  else if ($diff == 604800) {
    $status = "Last 7 days";
  }
  else if ($diff == 2592000) {
    $status = "Last 30 days";
  }

  function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
  }

  function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
?>

<!DOCTYPE html>
<html>
<head>
  <!-- Resources -->
  <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
  <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
  <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

  <style>
  #chartdiv {
    width: 100%;
    height: 800px;
  }
  </style>
</head>
<body>
                                <?php
                                $chart_data = '';
                                $params = array(
                                  "output" => array("itemid","name"),
                                  "hostids" => $hostid,
                                  "search" => array("key_" => "icmpping")//seach id contains specific word
                                );
                                //call api method
                                $result = $zbx->call('item.get',$params);

                                foreach ($result as $item) {
                                //if item name not equal to icmp
                                  if (stripos($item["name"], "icmp") !== false) {
                                    continue;
                                  }

                                  else {
                                    $itemid = $item["itemid"];
                                    $itemname = $item["name"];

                                    if (strpos($itemname, "[") !== false) {
                                      $titlegraph = str_replace("[","[[", $itemname);
                                      $titlegraph = str_replace("]","]]", $titlegraph);
                                    }
                                    else {
                                      $titlegraph = $itemname;
                                    }

                                    $params = array(
                                        "output" => array("clock", "value"),
                                        "itemids" => $itemid,
                                        "sortfield" => "clock",
                                        "sortorder" => "DESC",
                                        "time_from" => $timefrom,
                                        "time_till" => $timetill
                                        );

                                    //call api history.get with params
                                    $result = $zbx->call('history.get',$params);
                                    foreach (array_reverse($result) as $row) {
                                      $row["clock"] = $row["clock"] * 1000;
                                      $startdate = $row["clock"];
                                      $enddate = $row["clock"] + 6000;
                                      if ($row["value"] == 1) {
                                        $color = 'am4core.color("#1FFF00")';
                                      }
                                      else {
                                        $color = 'am4core.color("#FF0000")';
                                      }
                                      $chart_data .= "{category: ";
                                      $chart_data .= '"'.$titlegraph.'"';
                                      $chart_data .= ", ";
                                      $chart_data .= "start: ";
                                      $chart_data .= $startdate;
                                      $chart_data .= ", ";  
                                      $chart_data .= "end: ";
                                      $chart_data .= $enddate;
                                      $chart_data .= ", ";
                                      $chart_data .= "color: ";
                                      $chart_data .= $color;
                                      $chart_data .= ", ";
                                      $chart_data .= "task: ";
                                      $chart_data .= '"'.$titlegraph.'"';
                                      $chart_data .= "},";
                                    }
                                  }
                                }

                                $chart_data = substr($chart_data, 0, -1);
                                
                                ?>  
<!-- HTML -->
<div id="chartdiv"></div>

</body>
</html>

<!-- Chart code -->
<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end

var chart = am4core.create("chartdiv", am4charts.XYChart);
chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

chart.paddingRight = 30;
chart.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm";

var colorSet = new am4core.ColorSet();
colorSet.saturation = 0.4;

chart.data = [<?php echo $chart_data; ?>];

chart.dateFormatter.dateFormat = "HH:mm";
chart.dateFormatter.inputDateFormat = "HH:mm";

var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "category";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.inversed = true;

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 70;
// dateAxis.max = new Date(2018, 0, 1, 24, 0, 0, 0).getTime();
//dateAxis.strictMinMax = true;
dateAxis.renderer.tooltipLocation = 0;

var series1 = chart.series.push(new am4charts.ColumnSeries());
series1.columns.template.height = am4core.percent(70);
series1.columns.template.tooltipText = "{task}: [bold]{openDateX}[/]";

series1.dataFields.openDateX = "start";
series1.dataFields.dateX = "end";
series1.dataFields.categoryY = "category";
series1.columns.template.propertyFields.fill = "color"; // get color from data
series1.columns.template.propertyFields.stroke = "color";
series1.columns.template.strokeOpacity = 1;

chart.scrollbarX = new am4core.Scrollbar();

// Add cursor
chart.cursor = new am4charts.XYCursor();
chart.cursor.xAxis = dateAxis;
chart.cursor.snapToSeries = series1;

}); // end am4core.ready()
</script>