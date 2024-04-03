<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
  <style>
    #filewlegenddiv {
      height: 150px;
    }

    #filewlegendwrapper {
      max-height: 200px;
      overflow-x: none;
      overflow-y: auto;
    }
  </style>
</head>

<body>
  <?php

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? array("10361", "10324", "10346");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;


            $filerwSeries = '';
            $params = array(
            "output" => array("itemid", "name"),
            "hostids" => $hostid,
            "search" => array("name" => "file read"),//seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {
                $itemid = $item["itemid"];
                $itemname = $item["name"];
                //perform trend.get if time range above 7 days
            if ($diff >= 72000) {
              $params = array(
                    "output" => array("clock", "value_max"),
                    "itemids" => $itemid,
                    "sortfield" => "clock",
                    "sortorder" => "DESC",
                    "time_from" => $timefrom,
                    "time_till" => $timetill
                    );

              //call api history.get with params
              $result = $zbx->call('trend.get',$params);
              $filerw_data = '';
              foreach ($result as $row) {
                    //$row["clock"] = date(" H:i\ ", $row["clock"]);
                    $row["clock"] = $row["clock"] * 1000;
                    $filerw_data .= "{clock: ";
                    $filerw_data .= $row["clock"];
                    $filerw_data .= ", ";
                    $filerw_data .= "value: ";
                    $filerw_data .= $row["value_max"];
                    $filerw_data .= "},";
                } 
            }

            else {
            $params = array(
                    "output" => array("clock", "value"),
                    "itemids" => $itemid,
                    "history" => 0,
                    "sortfield" => "clock",
                    "sortorder" => "DESC",
                    "time_from" => $timefrom,
                    "time_till" => $timetill
                    );

            //call api history.get with params
            $result = $zbx->call('history.get',$params);
            $filerw_data = '';
            foreach (array_reverse($result) as $row) {
                  //$row["clock"] = date(" H:i\ ", $row["clock"]);
                  $row["clock"] = $row["clock"] * 1000;
                  $filerw_data .= "{clock: ";
                  $filerw_data .= $row["clock"];
                  $filerw_data .= ", ";
                  $filerw_data .= "value: ";
                  $filerw_data .= $row["value"];
                  $filerw_data .= "},";                                      
              } 

            }
            $filerw_data = substr($filerw_data, 0, -1);
            $filerwSeries .= 'createSeries("value", "'.$itemname.'", ['.$filerw_data.']);';
            }

            $params = array(
            "output" => array("itemid", "name"),
            "hostids" => $hostid,
            "search" => array("name" => "file write"),//seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {
                $itemid = $item["itemid"];
                $itemname = $item["name"];
                //perform trend.get if time range above 7 days
            if ($diff >= 604800) {
              $params = array(
                    "output" => array("clock", "value_max"),
                    "itemids" => $itemid,
                    "sortfield" => "clock",
                    "sortorder" => "DESC",
                    "time_from" => $timefrom,
                    "time_till" => $timetill
                    );

              //call api history.get with params
              $result = $zbx->call('trend.get',$params);
              $filerw_data = '';
              foreach ($result as $row) {
                    //$row["clock"] = date(" H:i\ ", $row["clock"]);
                    $row["clock"] = $row["clock"] * 1000;
                    $filerw_data .= "{clock: ";
                    $filerw_data .= $row["clock"];
                    $filerw_data .= ", ";
                    $filerw_data .= "value: ";
                    $filerw_data .= $row["value_max"];
                    $filerw_data .= "},";
                } 
            }

            else {
            $params = array(
                    "output" => array("clock", "value"),
                    "history" => 0,
                    "itemids" => $itemid,
                    "sortfield" => "clock",
                    "sortorder" => "DESC",
                    "time_from" => $timefrom,
                    "time_till" => $timetill
                    );

            //call api history.get with params
            $result = $zbx->call('history.get',$params);
            $filerw_data = '';
            foreach (array_reverse($result) as $row) {
                  //$row["clock"] = date(" H:i\ ", $row["clock"]);
                  $row["clock"] = $row["clock"] * 1000;
                  $filerw_data .= "{clock: ";
                  $filerw_data .= $row["clock"];
                  $filerw_data .= ", ";
                  $filerw_data .= "value: ";
                  $filerw_data .= $row["value"];
                  $filerw_data .= "},";                                      
              } 

            }
            $filerw_data = substr($filerw_data, 0, -1);
            $filerwSeries .= 'createSeries("value", "'.$itemname.'", ['.$filerw_data.']);';
            }
      ?>

  <div id="fileRWchart" style="width: auto;height: 400px;"></div>

  <!-- File read/write chart -->
    <script type="text/javascript">
// am4core.useTheme(am4themes_material);
// Create chart instance
var fileRWchart = am4core.create("fileRWchart", am4charts.XYChart);


fileRWchart.numberFormatter.numberFormat = "#.00b";

fileRWchart.colors.list = [
  am4core.color("#ffff00"),
  am4core.color("#e60000"),
];

//Add title
var title = fileRWchart.titles.create();
title.text = "File Read/Write";
title.fontSize = 25;
title.marginBottom = 30;
title.align = 'center';


// Create axes
var dateAxis = fileRWchart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 20;
dateAxis.renderer.labels.template.rotation = -90;
dateAxis.renderer.labels.template.verticalCenter = "middle";
dateAxis.renderer.labels.template.horizontalCenter = "left";

var valueAxis = fileRWchart.yAxes.push(new am4charts.ValueAxis());

// Create series
function createSeries(field, name, data) {
  var series = fileRWchart.series.push(new am4charts.LineSeries());
  series.dataFields.valueY = field;
  series.dataFields.dateX = "clock";
  series.name = name;
  series.tooltipText = "{name}: [b]{valueY}[/]";
  series.fillOpacity = 0.1;
  series.strokeWidth = 2;
  series.data = data;
  series.legendSettings.valueText = '[[last: {valueY.close}]] [[min: {valueY.low}]] [[avg: {valueY.average}]] [[max: {valueY.high}]]';
  
  return series;
}

<?php print $filerwSeries; ?>

// Add a legend
fileRWchart.legend = new am4charts.Legend();
fileRWchart.legend.useDefaultMarker = true;
fileRWchart.legend.valueLabels.template.align = "right";
fileRWchart.legend.valueLabels.template.textAlign = "end"; 
fileRWchart.legend.contentAlign = 'left';
fileRWchart.cursor = new am4charts.XYCursor();

// Set up export
fileRWchart.exporting.menu = new am4core.ExportMenu();
fileRWchart.exporting.adapter.add("data", function(data, target) {
  // Assemble data from series
  var data = [];
  fileRWchart.series.each(function(series) {
    for(var i = 0; i < series.data.length; i++) {
      series.data[i].name = series.name;
      data.push(series.data[i]);
    }
  });
  return { data: data };
});
</script>
</body>

</html>