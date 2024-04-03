<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    /* #replicamirrorlegenddiv {
      height: 150px;
    }

    #replicamirrorlegendwrapper {
      max-height: 200px;
      overflow-x: none;
      overflow-y: auto;
    } */
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

  $replicamirrorSeries = '';
  $params = array(
    "output" => array("itemid", "name", "error"),
    "hostids" => $hostid,
    "search" => array("name" => array("Log Bytes Flushes / sec", "Bytes Received From Replica / sec", "Bytes Sent to Replica / sec", "Bytes Sent to Transport / sec", "Log Bytes Received / sec", "Redone Bytes / sec")), //seach id contains specific word
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
    $replicamirrorSeries .= 'createSeries("value", "' . $itemname . '", [' . $chart_data . ']);';
  }


  if (!empty($itemerror)) {
    echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
  } else if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="replicamirrorchart" style="width: auto;height: 400px;"></div>
 
    <div id="replicamirrorlegenddiv"></div>

';
  }
  ?>

  <!-- Network Interface graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var replicamirrorchart = am4core.create("replicamirrorchart", am4charts.XYChart);


    replicamirrorchart.numberFormatter.numberFormat = "#.#b";

    var title = replicamirrorchart.titles.create();
    title.text = "Replica/Mirroring Statistics";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = replicamirrorchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = replicamirrorchart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
      var series = replicamirrorchart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = "clock";
      series.name = name;
      series.tooltipText = "{name}: [b]{valueY}[/]";
      series.fillOpacity = 0.1;
      series.strokeWidth = 2;
      series.data = data;
      series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.#b\')}]] [[min: {valueY.low.formatNumber(\'#.#b\')}]] [[avg: {valueY.average.formatNumber(\'#.#b\')}]] [[max: {valueY.high.formatNumber(\'#.#b\')}]]';

      return series;
    }

    <?php print $replicamirrorSeries; ?>

    // Add a legend
    replicamirrorchart.legend = new am4charts.Legend();
    replicamirrorchart.legend.useDefaultMarker = true;
    replicamirrorchart.legend.scrollable = true;
    replicamirrorchart.legend.valueLabels.template.align = "right";
    replicamirrorchart.legend.valueLabels.template.textAlign = "end";

    replicamirrorchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("replicamirrorlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    replicamirrorchart.legend.parent = legendContainer;
    replicamirrorchart.legend.maxHeight =  undefined;
    replicamirrorchart.legend.scrollable = true;   

    replicamirrorchart.events.on("datavalidated", resizeLegend);
    replicamirrorchart.events.on("maxsizechanged", resizeLegend);

    replicamirrorchart.legend.events.on("datavalidated", resizeLegend);
    replicamirrorchart.legend.events.on("maxsizechanged", resizeLegend);

    // Set up export
    replicamirrorchart.exporting.menu = new am4core.ExportMenu();
    replicamirrorchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      replicamirrorchart.series.each(function(series) {
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