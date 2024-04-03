<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
  <style>
    #iowaitlegenddiv {
      height: 150px;
    }

    #iowaitlegendwrapper {
      max-height: 200px;
      overflow-x: none;
      overflow-y: auto;
    }
  </style>
</head>

<body>
  <?php

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? array("10084");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;



  $iowaitSeries = '';

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
      "search" => array("name" => "CPU iowait time"), //seach id contains specific word
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
      $iowaitSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
    }
  }

  ?>

  <div id="iowaitchart" style="width: auto; height: 400px;"></div>
  <div id="iowaitlegenddiv"></div>
  <script>
    am4core.options.autoDispose = true;

    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var iowaitchart = am4core.create("iowaitchart", am4charts.XYChart);

    iowaitchart.numberFormatter.numberFormat = "# '%'";

    var title = iowaitchart.titles.create();
    title.text = "CPU IOWAIT Time (%)";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = iowaitchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = iowaitchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = iowaitchart.series.push(new am4charts.LineSeries());
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

    <?php echo $iowaitSeries; ?>

    // Add a legend
    iowaitchart.legend = new am4charts.Legend();
    iowaitchart.legend.useDefaultMarker = true;
    iowaitchart.legend.scrollable = false;
    iowaitchart.legend.valueLabels.template.align = "right";
    iowaitchart.legend.valueLabels.template.textAlign = "end";

    iowaitchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("iowaitlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    iowaitchart.legend.parent = legendContainer;

    iowaitchart.events.on("datavalidated", resizeLegend);
    iowaitchart.events.on("maxsizechanged", resizeLegend);

    iowaitchart.legend.events.on("datavalidated", resizeLegend);
    iowaitchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("iowaitlegenddiv").style.height = iowaitchart.legend.contentHeight + "px";
    }

    // Set up export
    iowaitchart.exporting.menu = new am4core.ExportMenu();
    iowaitchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    iowaitchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      iowaitchart.series.each(function(series) {
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