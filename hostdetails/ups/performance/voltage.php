<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
  <style>
    #voltlegenddiv {
      height: 150px;
    }

    #voltlegendwrapper {
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

  $voltSeries = '';
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "Input voltage"), //seach id contains specific word
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
      $volt_data = '';
      foreach ($result as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $volt_data .= "{clock: ";
        $volt_data .= $row["clock"];
        $volt_data .= ", ";
        $volt_data .= "value: ";
        $volt_data .= $row["value_max"];
        $volt_data .= "},";
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
      $volt_data = '';
      foreach (array_reverse($result) as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $volt_data .= "{clock: ";
        $volt_data .= $row["clock"];
        $volt_data .= ", ";
        $volt_data .= "value: ";
        $volt_data .= $row["value"];
        $volt_data .= "},";
      }
    }
    $volt_data = substr($volt_data, 0, -1);
    $voltSeries .= 'createSeries("value", "' . $itemname . '", [' . $volt_data . ']);';
  }

  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "Output voltage"), //seach id contains specific word
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
      $volt_data = '';
      foreach ($result as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $volt_data .= "{clock: ";
        $volt_data .= $row["clock"];
        $volt_data .= ", ";
        $volt_data .= "value: ";
        $volt_data .= $row["value_max"];
        $volt_data .= "},";
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
      $volt_data = '';
      foreach (array_reverse($result) as $row) {
        //$row["clock"] = date(" H:i\ ", $row["clock"]);
        $row["clock"] = $row["clock"] * 1000;
        $volt_data .= "{clock: ";
        $volt_data .= $row["clock"];
        $volt_data .= ", ";
        $volt_data .= "value: ";
        $volt_data .= $row["value"];
        $volt_data .= "},";
      }
    }
    $volt_data = substr($volt_data, 0, -1);
    $voltSeries .= 'createSeries("value", "' . $itemname . '", [' . $volt_data . ']);';
  }
  
    if ($result == false) {
    echo '<div><h5 class="text-danger">No data available</h5></div>';
  } else {
    echo '
    <div id="voltchart" style="width: auto;height: 400px;"></div>
    <div id="voltlegendwrapper">
      <div id="voltlegenddiv"></div>  
    </div>';
  }
  ?>




  <!-- Network Interface graph -->
  <script type="text/javascript">
    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var voltchart = am4core.create("voltchart", am4charts.XYChart);


    voltchart.numberFormatter.numberFormat = "#v";

    var title = voltchart.titles.create();
    title.text = 'Input & Output Voltage (V)';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = voltchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = voltchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = voltchart.series.push(new am4charts.LineSeries());
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

    <?php print $voltSeries; ?>

    // Add a legend
    voltchart.legend = new am4charts.Legend();
    voltchart.legend.useDefaultMarker = true;
    voltchart.legend.scrollable = true;
    voltchart.legend.valueLabels.template.align = "right";
    voltchart.legend.valueLabels.template.textAlign = "end";

    voltchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("voltlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    voltchart.legend.parent = legendContainer;

    voltchart.events.on("datavalidated", resizeLegend);
    voltchart.events.on("maxsizechanged", resizeLegend);

    voltchart.legend.events.on("datavalidated", resizeLegend);
    voltchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("voltlegenddiv").style.height = voltchart.legend.contentHeight + "px";
    }

    // Set up export
    voltchart.exporting.menu = new am4core.ExportMenu();
    voltchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      voltchart.series.each(function(series) {
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