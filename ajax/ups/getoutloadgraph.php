<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Linux Overview</title>

  <style>
    #outloadlegenddiv {
      height: 150px;
    }

    #outloadtlegendwrapper {
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



  $outloadSeries = '';
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
        "search" => array("key_" => "upsAdvOutputLoad"), //seach id contains specific word
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
      $outloadSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
    }
  }

  ?>

  <div id="outloadchart" style="width: auto; height: 400px;"></div>
  <div id="outloadtlegendwrapper">
    <div id="outloadlegenddiv"></div>
  </div>

  <script>
    am4core.options.autoDispose = true;

    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var outloadchart = am4core.create("outloadchart", am4charts.XYChart);

    outloadchart.numberFormatter.numberFormat = "# '%'";

    var title = outloadchart.titles.create();
    title.text = "Output Load (%)";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = outloadchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = outloadchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = outloadchart.series.push(new am4charts.LineSeries());
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

    <?php echo $outloadSeries; ?>

    // Add a legend
    outloadchart.legend = new am4charts.Legend();
    outloadchart.legend.useDefaultMarker = true;
    outloadchart.legend.scrollable = false;
    outloadchart.legend.valueLabels.template.align = "right";
    outloadchart.legend.valueLabels.template.textAlign = "end";

    outloadchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("outloadlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    outloadchart.legend.parent = legendContainer;

    outloadchart.events.on("datavalidated", resizeLegend);
    outloadchart.events.on("maxsizechanged", resizeLegend);

    outloadchart.legend.events.on("datavalidated", resizeLegend);
    outloadchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("outloadlegenddiv").style.height = outloadchart.legend.contentHeight + "px";
    }

    // Set up export
    outloadchart.exporting.menu = new am4core.ExportMenu();
    outloadchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    outloadchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      outloadchart.series.each(function(series) {
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