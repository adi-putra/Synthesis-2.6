<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #balloonmemlegenddiv {
      height: 150px;
    }

    #balloonmemlegendwrapper {
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
  $timefrom = $_GET['timefrom'] ?? time() - 600;
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;


  $memSeries = '';
  
    $params = array(
      "output" => array("name"),
      "hostids" => $hostid
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {

      $hostname = $host["name"];

      $params = array(
        "output" => array("itemid", "name"),
        "hostids" => $hostid,
        "search" => array("name" => "ballooned memory"), //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {  
        $itemid = $item["itemid"];
        $itemname = ucwords($item["name"]);   
      }

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
      $memSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
    }

    if ($result == false) {
      echo '<div><h5 class="text-danger">No data available</h5></div>';
    } else {
      echo '
      <div id="balloonmemchart" style="width: auto; height: 400px;"></div>
      <div id="balloonmemlegendwrapper">
        <div id="balloonmemlegenddiv"></div>
      </div>
  ';
    }

  ?>

  <script>
    am4core.options.autoDispose = true;

    am4core.useTheme(am4themes_material);
    // Create chart instance
    var balloonmemchart = am4core.create("balloonmemchart", am4charts.XYChart);

    balloonmemchart.numberFormatter.numberFormat = "#.00b ";

    var title = balloonmemchart.titles.create();
    title.text = '<?php echo $itemname;?>';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = balloonmemchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = balloonmemchart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
      var series = balloonmemchart.series.push(new am4charts.LineSeries());
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
    balloonmemchart.legend = new am4charts.Legend();
    balloonmemchart.legend.useDefaultMarker = true;
    balloonmemchart.legend.scrollable = false;
    balloonmemchart.legend.valueLabels.template.align = "right";
    balloonmemchart.legend.valueLabels.template.textAlign = "end";

    balloonmemchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("balloonmemlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    balloonmemchart.legend.parent = legendContainer;

    balloonmemchart.events.on("datavalidated", resizeLegend);
    balloonmemchart.events.on("maxsizechanged", resizeLegend);

    balloonmemchart.legend.events.on("datavalidated", resizeLegend);
    balloonmemchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("balloonmemlegenddiv").style.height = balloonmemchart.legend.contentHeight + "px";
    }

    // Set up export
    balloonmemchart.exporting.menu = new am4core.ExportMenu();
    balloonmemchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    balloonmemchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      balloonmemchart.series.each(function(series) {
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