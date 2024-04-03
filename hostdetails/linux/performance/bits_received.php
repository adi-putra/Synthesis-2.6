<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #bitreclegenddiv {
      height: 150px;
    }

    #bitreclegendwrapper {
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

  $bitrecSeries = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => array("bits received")), //seach id contains specific word
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
    $bitrecSeries .= 'createSeries("value", "' . $itemname . '", [' . $traffic_data . ']);';
  }

  if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
  <div id="bitrecic" style="width: auto;height: 400px;"></div>
  <div id="bitreclegendwrapper">
    <div id="bitreclegenddiv"></div>
  </div>
';
  }
  ?>

  <!-- Network Interface graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var bitrecchart = am4core.create("bitrecic", am4charts.XYChart);


    bitrecchart.numberFormatter.numberFormat = "#.00b";

    var title = bitrecchart.titles.create();
    title.text = 'Bits Received';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = bitrecchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = bitrecchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = bitrecchart.series.push(new am4charts.LineSeries());
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

    <?php print $bitrecSeries; ?>

    // Add a legend
    bitrecchart.legend = new am4charts.Legend();
    bitrecchart.legend.useDefaultMarker = true;
    bitrecchart.legend.scrollable = true;
    bitrecchart.legend.valueLabels.template.align = "right";
    bitrecchart.legend.valueLabels.template.textAlign = "end";

    bitrecchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("bitreclegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    bitrecchart.legend.parent = legendContainer;

    bitrecchart.events.on("datavalidated", resizeLegend);
    bitrecchart.events.on("maxsizechanged", resizeLegend);

    bitrecchart.legend.events.on("datavalidated", resizeLegend);
    bitrecchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("bitreclegenddiv").style.height = bitrecchart.legend.contentHeight + "px";
    }

    // Set up export
    bitrecchart.exporting.menu = new am4core.ExportMenu();
    bitrecchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      bitrecchart.series.each(function(series) {
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