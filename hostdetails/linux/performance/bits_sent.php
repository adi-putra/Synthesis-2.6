<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #bitsentlegenddiv {
      height: 150px;
    }

    #bitsentlegendwrapper {
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

  $bitsentSeries = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => array("bits sent")), //seach id contains specific word
    "searchByAny" => true
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
    $bitsentSeries .= 'createSeries("value", "' . $itemname . '", [' . $traffic_data . ']);';
  }

  if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="bitsentic" style="width: auto;height: 400px;"></div>
  <div id="bitsentlegendwrapper">
    <div id="bitsentlegenddiv"></div>
  </div>
';
  }
  ?>

  <!-- Network Interface graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var bitsentchart = am4core.create("bitsentic", am4charts.XYChart);


    bitsentchart.numberFormatter.numberFormat = "#.00b";

    var title = bitsentchart.titles.create();
    title.text = 'Bits Sent';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = bitsentchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = bitsentchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = bitsentchart.series.push(new am4charts.LineSeries());
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

    <?php print $bitsentSeries; ?>

    // Add a legend
    bitsentchart.legend = new am4charts.Legend();
    bitsentchart.legend.useDefaultMarker = true;
    bitsentchart.legend.scrollable = true;
    bitsentchart.legend.valueLabels.template.align = "right";
    bitsentchart.legend.valueLabels.template.textAlign = "end";

    bitsentchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("bitsentlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    bitsentchart.legend.parent = legendContainer;

    bitsentchart.events.on("datavalidated", resizeLegend);
    bitsentchart.events.on("maxsizechanged", resizeLegend);

    bitsentchart.legend.events.on("datavalidated", resizeLegend);
    bitsentchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("bitsentlegenddiv").style.height = bitsentchart.legend.contentHeight + "px";
    }

    // Set up export
    bitsentchart.exporting.menu = new am4core.ExportMenu();
    bitsentchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      bitsentchart.series.each(function(series) {
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