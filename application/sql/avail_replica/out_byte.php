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

  $outbyteSeries = '';
  $params = array(
    "output" => array("itemid", "name", "error"),
    "hostids" => $hostid,
    "search" => array("key_" => "(_Total)\\Bytes Sent to Replica/sec"), //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  foreach ($result as $item) {

    $itemid = $item["itemid"];
    $itemname = $item["name"];
    $itemerror = $item["error"];

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
    $outbyteSeries .= 'createSeries("value", "' . $itemname . '", [' . $chart_data . ']);';
  }


  if (!empty($itemerror)) {
    echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
  } else if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="outbytechart" style="width: auto;height: 400px;"></div>
    <div id="outbytelegenddiv"></div>
';
  }
  ?>

  <!-- SQL Stat graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var outbytechart = am4core.create("outbytechart", am4charts.XYChart);


    outbytechart.numberFormatter.numberFormat = "#.##b'/s'";

    var title = outbytechart.titles.create();
    title.text = 'OUT | Bytes Sent to Replica/sec';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = outbytechart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = outbytechart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
      var series = outbytechart.series.push(new am4charts.LineSeries());
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

    <?php print $outbyteSeries; ?>

    // Add a legend
    outbytechart.legend = new am4charts.Legend();
    outbytechart.legend.useDefaultMarker = true;
    outbytechart.legend.scrollable = true;
    outbytechart.legend.valueLabels.template.align = "right";
    outbytechart.legend.valueLabels.template.textAlign = "end";

    outbytechart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("outbytelegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = undefined;

    outbytechart.legend.parent = legendContainer;

    outbytechart.events.on("datavalidated", resizeLegend);
    outbytechart.events.on("maxsizechanged", resizeLegend);

    outbytechart.legend.events.on("datavalidated", resizeLegend);
    outbytechart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("outbytelegenddiv").style.height = outbytechart.legend.contentHeight + "px";
    }

    // Set up export
    outbytechart.exporting.menu = new am4core.ExportMenu();
    outbytechart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      outbytechart.series.each(function(series) {
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