<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
  <style>
    #frequencylegenddiv {
      height: 150px;
    }

    #frequencylegendwrapper {
      max-height: 200px;
      overflow-x: none;
      overflow-y: auto;
    }
  </style>
</head>

<body>
  <?php
  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"];
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;

  $frequencySeries = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "Input frequency"), //seach id contains specific word
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
      $frequency_data = '';
      foreach ($result as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $frequency_data .= "{clock: ";
        $frequency_data .= $row["clock"];
        $frequency_data .= ", ";
        $frequency_data .= "value: ";
        $frequency_data .= $row["value_max"];
        $frequency_data .= "},";
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
      $frequency_data = '';
      foreach (array_reverse($result) as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $frequency_data .= "{clock: ";
        $frequency_data .= $row["clock"];
        $frequency_data .= ", ";
        $frequency_data .= "value: ";
        $frequency_data .= $row["value"];
        $frequency_data .= "},";
      }
    }
    $frequency_data = substr($frequency_data, 0, -1);
    $frequencySeries .= 'createSeries("value", "' . $itemname . '", [' . $frequency_data . ']);';
  }

  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "Output frequency"), //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  foreach ($result as $item) {

    $itemid = $item["itemid"];
    $itemname = $item["name"];

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
      $frequency_data = '';
      foreach ($result as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $frequency_data .= "{clock: ";
        $frequency_data .= $row["clock"];
        $frequency_data .= ", ";
        $frequency_data .= "value: ";
        $frequency_data .= $row["value_max"];
        $frequency_data .= "},";
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
      $frequency_data = '';
      foreach (array_reverse($result) as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $frequency_data .= "{clock: ";
        $frequency_data .= $row["clock"];
        $frequency_data .= ", ";
        $frequency_data .= "value: ";
        $frequency_data .= $row["value"];
        $frequency_data .= "},";
      }
    }
    $frequency_data = substr($frequency_data, 0, -1);
    $frequencySeries .= 'createSeries("value", "' . $itemname . '", [' . $frequency_data . ']);';
  }
  
    if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
    <div id="frequencychart" style="width: auto;height: 400px;"></div>
    <div id="frequencylegendwrapper">
      <div id="frequencylegenddiv"></div>
    </div>';
  }
  ?>




  <!-- Network Interface graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var frequencychart = am4core.create("frequencychart", am4charts.XYChart);


    frequencychart.numberFormatter.numberFormat = "#Hz";

    var title = frequencychart.titles.create();
    title.text = 'Input & Output Frequency (Hz)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = frequencychart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = frequencychart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = frequencychart.series.push(new am4charts.LineSeries());
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

    <?php print $frequencySeries; ?>

    // Add a legend
    frequencychart.legend = new am4charts.Legend();
    frequencychart.legend.useDefaultMarker = true;
    frequencychart.legend.scrollable = true;
    frequencychart.legend.valueLabels.template.align = "right";
    frequencychart.legend.valueLabels.template.textAlign = "end";

    frequencychart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("frequencylegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    frequencychart.legend.parent = legendContainer;

    frequencychart.events.on("datavalidated", resizeLegend);
    frequencychart.events.on("maxsizechanged", resizeLegend);

    frequencychart.legend.events.on("datavalidated", resizeLegend);
    frequencychart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("frequencylegenddiv").style.height = frequencychart.legend.contentHeight + "px";
    }

    // Set up export
    frequencychart.exporting.menu = new am4core.ExportMenu();
    frequencychart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      frequencychart.series.each(function(series) {
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