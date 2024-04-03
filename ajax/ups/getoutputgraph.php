<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Linux Overview</title>

  <style>
    #outputlegenddiv {
      height: 150px;
    }

    #outputlegendwrapper {
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
  $timetill = $_GET['timetill'] ?? strtotime("now");
  //display time format
  $diff = $timetill - $timefrom;



  $memSeries = '';
  foreach ($hostid as $hostID) {
    $params = array(
      "output" => array("name"),
      "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {

      $hostname = $host["name"];

      $params = array(
        "output" => array("itemid", "name"),
        "hostids" => $hostID,
        "search" => array("key_" => "upsAdvOutputVoltage"), //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        $itemid = $item["itemid"];
        $itemname = $item["name"];
      }

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
      $memSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
    }
  }

  ?>

  <div id="outputchart" style="width: auto; height: 400px;"></div>
  <div id="outputlegendwrapper">
    <div id="outputlegenddiv"></div>
  </div>

  <script>
    am4core.options.autoDispose = true;

    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var outputchart = am4core.create("outputchart", am4charts.XYChart);

    outputchart.numberFormatter.numberFormat = "# 'v'";

    var title = outputchart.titles.create();
    title.text = "Output Voltage (v)";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = outputchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = outputchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = outputchart.series.push(new am4charts.LineSeries());
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

    <?php echo $memSeries; ?>

    // Add a legend
    outputchart.legend = new am4charts.Legend();
    outputchart.legend.useDefaultMarker = true;
    outputchart.legend.scrollable = false;
    outputchart.legend.valueLabels.template.align = "right";
    outputchart.legend.valueLabels.template.textAlign = "end";

    outputchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("outputlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    outputchart.legend.parent = legendContainer;

    outputchart.events.on("datavalidated", resizeLegend);
    outputchart.events.on("maxsizechanged", resizeLegend);

    outputchart.legend.events.on("datavalidated", resizeLegend);
    outputchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("outputlegenddiv").style.height = outputchart.legend.contentHeight + "px";
    }

    // Set up export
    outputchart.exporting.menu = new am4core.ExportMenu();
    outputchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    outputchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      outputchart.series.each(function(series) {
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