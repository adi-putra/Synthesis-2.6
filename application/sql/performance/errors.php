<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #errorslegenddiv {
      height: 300px;
    }

    #errorslegendwrapper {
      max-height: 400px;
      overflow-x: none;
      overflow-y: auto;
    }
  </style>
</head>

<body>
  <?php
  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? array("10713");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;

  $errorsSeries = '';
  $params = array(
    "output" => array("itemid", "name", "error"),
    "hostids" => $hostid,
    "search" => array("key_" => array("(_Total)\\Errors/sec", "(Info Errors)\\Errors/sec", "(User Errors)\\Errors/sec", "(Kill Connection Errors)\\Errors/sec", "(DB Offline Errors)\\Errors/sec")), //seach id contains specific word
    "searchByAny" => true
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
      $traffic_data = '';
      foreach ($result as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $traffic_data .= "{clock: ";
        $traffic_data .= $row["clock"];
        $traffic_data .= ", ";
        $traffic_data .= "value: ";
        $traffic_data .= $row["value_max"];
        $traffic_data .= "},";
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
      $traffic_data = '';
      foreach (array_reverse($result) as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $traffic_data .= "{clock: ";
        $traffic_data .= $row["clock"];
        $traffic_data .= ", ";
        $traffic_data .= "value: ";
        $traffic_data .= $row["value"];
        $traffic_data .= "},";
      }
    }
    $traffic_data = substr($traffic_data, 0, -1);
    $errorsSeries .= 'createSeries("value", "' . $itemname . '", [' . $traffic_data . ']);';
  }


  if (!empty($itemerror)) {
    echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
  } else if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="errorschart" style="width: auto;height: 400px;"></div>
  <div id="errorslegendwrapper">
    <div id="errorslegenddiv"></div>
  </div>
';
  }
  ?>

  <!-- SQL Stat graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var errorschart = am4core.create("errorschart", am4charts.XYChart);


    errorschart.numberFormatter.numberFormat = "#";

    var title = errorschart.titles.create();
    title.text = 'Error/sec';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = errorschart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = errorschart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = errorschart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = "clock";
      series.name = name;
      series.tooltipText = "{name}: [b]{valueY}[/]";
      series.fillOpacity = 0.1;
      series.strokeWidth = 2;
      series.data = data;
      series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.#\')}]] [[min: {valueY.low.formatNumber(\'#.#\')}]] [[avg: {valueY.average.formatNumber(\'#.#\')}]] [[max: {valueY.high.formatNumber(\'#.#\')}]]';

      return series;
    }

    <?php print $errorsSeries; ?>

    // Add a legend
    errorschart.legend = new am4charts.Legend();
    errorschart.legend.useDefaultMarker = true;
    errorschart.legend.scrollable = true;
    errorschart.legend.valueLabels.template.align = "right";
    errorschart.legend.valueLabels.template.textAlign = "end";

    errorschart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("errorslegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    errorschart.legend.parent = legendContainer;

    errorschart.events.on("datavalidated", resizeLegend);
    errorschart.events.on("maxsizechanged", resizeLegend);

    errorschart.legend.events.on("datavalidated", resizeLegend);
    errorschart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("errorslegenddiv").style.height = errorschart.legend.contentHeight + "px";
    }

    // Set up export
    errorschart.exporting.menu = new am4core.ExportMenu();
    errorschart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      errorschart.series.each(function(series) {
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