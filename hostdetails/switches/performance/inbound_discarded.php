<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #inbounddisclegenddiv {
      height: 150px;
    }

    #inbounddisclegendwrapper {
      max-height: 200px;
      overflow-x: none;
      overflow-y: auto;
    }
  </style>
</head>

<body>
  <?php
  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? array("10334");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;

  $inbound_disc_series = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("key_" => "net.if.in.discards[ifInDiscards"), //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  foreach ($result as $item) {

    $itemid = $item["itemid"];
    $itemname = str_replace("[", "[[", $item["name"]);
    $itemname = str_replace("]", "]]", $itemname);

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
    $inbound_disc_series .= 'createSeries("value", "' . $itemname . '", [' . $chart_data . ']);';
  }

  if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
    <div id="inbounddisc_chart" style="width: auto;height: 400px;"></div>
    <div id="inbounddisclegendwrapper">
      <div id="inbounddisclegenddiv"></div>
    </div>
';}
  ?>

  <!-- Network Interface graph -->
  <script type="text/javascript">
    am4core.options.autoDispose = true;
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var inbounddisc_chart = am4core.create("inbounddisc_chart", am4charts.XYChart);


    inbounddisc_chart.numberFormatter.numberFormat = "#.00b";

    var title = inbounddisc_chart.titles.create();
    title.text = 'Inbound Packets Discarded (b)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = inbounddisc_chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = inbounddisc_chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
      var series = inbounddisc_chart.series.push(new am4charts.LineSeries());
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

    <?php print $inbound_disc_series; ?>

    // Add a legend
    inbounddisc_chart.legend = new am4charts.Legend();
    inbounddisc_chart.legend.useDefaultMarker = true;
    inbounddisc_chart.legend.scrollable = true;
    inbounddisc_chart.legend.valueLabels.template.align = "right";
    inbounddisc_chart.legend.valueLabels.template.textAlign = "end";

    inbounddisc_chart.cursor = new am4charts.XYCursor();

    var legendContainer = am4core.create("inbounddisclegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    inbounddisc_chart.legend.parent = legendContainer;

    inbounddisc_chart.events.on("datavalidated", resizeLegend);
    inbounddisc_chart.events.on("maxsizechanged", resizeLegend);

    inbounddisc_chart.legend.events.on("datavalidated", resizeLegend);
    inbounddisc_chart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("inbounddisclegenddiv").style.height = inbounddisc_chart.legend.contentHeight + "px";
    }

    // Set up export
    inbounddisc_chart.exporting.menu = new am4core.ExportMenu();
    inbounddisc_chart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      inbounddisc_chart.series.each(function(series) {
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