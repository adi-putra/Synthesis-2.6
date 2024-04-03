<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
</head>

<body>
  
  <?php
  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? array("10713");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;

  $datawritelatencySeries = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "| Data Write Latency"), //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
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
      $result = $zbx->call('trend.get', $params);
      $chart_data = '';
      foreach ($result as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $chart_data .= "{clock: ";
        $chart_data .= $row["clock"];
        $chart_data .= ", ";
        $chart_data .= "value: ";
        $chart_data .= $row["value_max"];
        $chart_data .= "},";
      }
    } else {
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
      $result = $zbx->call('history.get', $params);
      $chart_data = '';
      foreach (array_reverse($result) as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $chart_data .= "{clock: ";
        $chart_data .= $row["clock"];
        $chart_data .= ", ";
        $chart_data .= "value: ";
        $chart_data .= $row["value"];
        $chart_data .= "},";
      }
    }
    $chart_data = substr($chart_data, 0, -1);
    $datawritelatencySeries .= 'createSeries("value", "' . $itemname . '", [' . $chart_data . ']);';
  }


  if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="datawritelatencychart" style="width: auto;height: 400px;"></div>
    <div id="datawritelatencylegenddiv"></div>
';
  }
  ?>

  <!-- SQL Stat graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var datawritelatencychart = am4core.create("datawritelatencychart", am4charts.XYChart);


    datawritelatencychart.numberFormatter.numberFormat = "#.##b'/s'";

    var title = datawritelatencychart.titles.create();
    title.text = 'DATA | Write Latency (ms)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = datawritelatencychart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = datawritelatencychart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = datawritelatencychart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = "clock";
      series.name = name;
      series.tooltipText = "{name}: [b]{valueY}[/]";
      series.fillOpacity = 0.1;
      series.strokeWidth = 2;
      series.data = data;
      series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.##b\')}]] [[min: {valueY.low.formatNumber(\'#.##b\')}]] [[avg: {valueY.average.formatNumber(\'#.##b\')}]] [[max: {valueY.high.formatNumber(\'#.##b\')}]]';

      return series;
    }

    <?php print $datawritelatencySeries; ?>

    // Add a legend
    datawritelatencychart.legend = new am4charts.Legend();
    datawritelatencychart.legend.useDefaultMarker = true;
    datawritelatencychart.legend.scrollable = true;
    datawritelatencychart.legend.valueLabels.template.align = "right";
    datawritelatencychart.legend.valueLabels.template.textAlign = "end";

    datawritelatencychart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("datawritelatencylegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    datawritelatencychart.legend.parent = legendContainer;

    legendContainer.legend.maxHeight = 200;
    legendContainer.legend.height = undefined;
    legendContainer.legend.scrollable = true;   

    datawritelatencychart.events.on("datavalidated", resizeLegend);
    datawritelatencychart.events.on("maxsizechanged", resizeLegend);

    datawritelatencychart.legend.events.on("datavalidated", resizeLegend);
    datawritelatencychart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("datawritelatencylegenddiv").style.height = datawritelatencychart.legend.contentHeight + "px";
    }

    // Set up export
    datawritelatencychart.exporting.menu = new am4core.ExportMenu();
    datawritelatencychart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      datawritelatencychart.series.each(function(series) {
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
</body>

</html>