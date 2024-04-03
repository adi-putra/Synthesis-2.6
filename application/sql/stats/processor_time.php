<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
  <style>
    #processorlegenddiv {
      height: 150px;
    }

    #processorlegendwrapper {
      max-height: 200px;
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



  $processorSeries = '';

  $params = array(
    "output" => array("name"),
    "hostids" => $hostid
  );
  //call api method
  $result = $zbx->call('host.get', $params);
  foreach ($result as $host) {

    $hostname = $host["name"];

    $params = array(
      "output" => array("itemid", "name", "error"),
      "hostids" => $hostid,
      "search" => array("name" => "Processor Time"), //seach id contains specific word
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
          "history" => 0,
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
      $processorSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
    }
  }

  if (!empty($itemerror)) {
    echo '<div><h5 class="text-danger">"' . $itemerror . '"</h5></div>';
  } else if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
    <div id="processorchart" style="width: auto; height: 400px;"></div>
    <div id="processorlegenddiv"></div>
';
  }

  ?>


  <script>
    am4core.options.autoDispose = true;

    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var processorchart = am4core.create("processorchart", am4charts.XYChart);

    processorchart.numberFormatter.numberFormat = "#'%'";

    var title = processorchart.titles.create();
    title.text = "Processor Time (%)";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = processorchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = processorchart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
      var series = processorchart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = "clock";
      series.name = name;
      series.tooltipText = "{name}: [b]{valueY.formatNumber(\'#.##\')}%}[/]";
      series.fillOpacity = 0.1;
      series.strokeWidth = 2;
      series.data = data;
      series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.##\')}%]] [[min: {valueY.low.formatNumber(\'#.##\')}%]] [[avg: {valueY.average.formatNumber(\'#.##\')}%]] [[max: {valueY.high.formatNumber(\'#.##\')}%]]';

      return series;
    }

    <?php echo $processorSeries; ?>

    // Add a legend
    processorchart.legend = new am4charts.Legend();
    processorchart.legend.useDefaultMarker = true;
    processorchart.legend.scrollable = false;
    processorchart.legend.valueLabels.template.align = "right";
    processorchart.legend.valueLabels.template.textAlign = "end";

    processorchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("processorlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    processorchart.legend.parent = legendContainer;

    processorchart.events.on("datavalidated", resizeLegend);
    processorchart.events.on("maxsizechanged", resizeLegend);

    processorchart.legend.events.on("datavalidated", resizeLegend);
    processorchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("processorlegenddiv").style.height = processorchart.legend.contentHeight + "px";
    }

    // Set up export
    processorchart.exporting.menu = new am4core.ExportMenu();
    processorchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    processorchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      processorchart.series.each(function(series) {
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