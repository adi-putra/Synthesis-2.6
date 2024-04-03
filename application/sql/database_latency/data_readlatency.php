<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
  <style>
    #datareadlatencylegenddiv {
      height: 150px;
    }

    #datareadlatencylegendwrapper {
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
  

  $datareadlatencyfSeries = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "| Data Read Latency"), //seach id contains specific word
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
    $datareadlatencyfSeries .= 'createSeries("value", "' . $itemname . '", [' . $chart_data . ']);';
  }


  ?>


  <div id="datareadlatencyfic" style="width: auto;height: 400px;"></div>
  <div id="datareadlatencylegendwrapper">
    <div id="datareadlatencylegenddiv"></div>
  </div>



  <!-- Network Interface graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var datareadlatencychart = am4core.create("datareadlatencyfic", am4charts.XYChart);


    datareadlatencychart.numberFormatter.numberFormat = "#.##b '/s'";

    var title = datareadlatencychart.titles.create();
    title.text = 'DATA | Read Latency (ms)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = datareadlatencychart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = datareadlatencychart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = datareadlatencychart.series.push(new am4charts.LineSeries());
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

    <?php print $datareadlatencyfSeries; ?>

    // Add a legend
    datareadlatencychart.legend = new am4charts.Legend();
    datareadlatencychart.legend.useDefaultMarker = true;
    datareadlatencychart.legend.scrollable = true;
    datareadlatencychart.legend.valueLabels.template.align = "right";
    datareadlatencychart.legend.valueLabels.template.textAlign = "end";

    datareadlatencychart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("datareadlatencylegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    datareadlatencychart.legend.parent = legendContainer;

    datareadlatencychart.events.on("datavalidated", resizeLegend);
    datareadlatencychart.events.on("maxsizechanged", resizeLegend);

    datareadlatencychart.legend.events.on("datavalidated", resizeLegend);
    datareadlatencychart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("datareadlatencylegenddiv").style.height = datareadlatencychart.legend.contentHeight + "px";
    }

    // Set up export
    datareadlatencychart.exporting.menu = new am4core.ExportMenu();
    datareadlatencychart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      datareadlatencychart.series.each(function(series) {
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