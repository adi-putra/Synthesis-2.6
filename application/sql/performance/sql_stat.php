<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #sqlstatlegenddiv {
      height: 300px;
    }

    #sqlstatlegendwrapper {
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

  $sqlstatSeries = '';
  $params = array(
    "output" => array("itemid", "name", "error"),
    "hostids" => $hostid,
    "search" => array("name" => array("Batch Request / sec", "SQL Compilations / sec", "Logouts / sec", "Logins / sec", "SQL Re-Compilations / sec")), //seach id contains specific word
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
    $sqlstatSeries .= 'createSeries("value", "' . $itemname . '", [' . $traffic_data . ']);';
  }


  if (!empty($itemerror)) {
    echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
  } else if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="sqlstatchart" style="width: auto;height: 400px;"></div>
  <div id="sqlstatlegendwrapper">
    <div id="sqlstatlegenddiv"></div>
  </div>
';
  }
  ?>

  <!-- SQL Stat graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var sqlstatchart = am4core.create("sqlstatchart", am4charts.XYChart);


    sqlstatchart.numberFormatter.numberFormat = "#.##";

    var title = sqlstatchart.titles.create();
    title.text = 'SQL Stats';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = sqlstatchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = sqlstatchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = sqlstatchart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = "clock";
      series.name = name;
      series.tooltipText = "{name}: [b]{valueY}[/]";
      series.fillOpacity = 0.1;
      series.strokeWidth = 2;
      series.data = data;
      series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.\')}]] [[min: {valueY.low.formatNumber(\'#.\')}]] [[avg: {valueY.average.formatNumber(\'#.\')}]] [[max: {valueY.high.formatNumber(\'#.\')}]]';

      return series;
    }

    <?php print $sqlstatSeries; ?>

    // Add a legend
    sqlstatchart.legend = new am4charts.Legend();
    sqlstatchart.legend.useDefaultMarker = true;
    sqlstatchart.legend.scrollable = true;
    sqlstatchart.legend.valueLabels.template.align = "right";
    sqlstatchart.legend.valueLabels.template.textAlign = "end";

    sqlstatchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("sqlstatlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    sqlstatchart.legend.parent = legendContainer;

    sqlstatchart.events.on("datavalidated", resizeLegend);
    sqlstatchart.events.on("maxsizechanged", resizeLegend);

    sqlstatchart.legend.events.on("datavalidated", resizeLegend);
    sqlstatchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("sqlstatlegenddiv").style.height = sqlstatchart.legend.contentHeight + "px";
    }

    // Set up export
    sqlstatchart.exporting.menu = new am4core.ExportMenu();
    sqlstatchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      sqlstatchart.series.each(function(series) {
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