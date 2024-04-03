<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>

<?php

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? array("10361", "10324", "10346");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;

  $cpuSeries = '';

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
        "search" => array("name" => "VMware: CPU ready"), //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        $itemid = $item["itemid"];
        $item["name"] = str_replace("VMware:","",$item["name"]);
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
        $cpuSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
      }
    }

    if ($result == false) {
      echo '<div><h5 class="text-danger">No data available</h5></div>';
    } else {
      echo '
      <div id="cpuchart" style="width: auto; height: 400px;"></div>
      <div id="cpulegendwrapper">
        <div id="cpulegenddiv"></div>
      </div>
  ';
    }

  ?>



  <script>
    am4core.options.autoDispose = true;

    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var cpuchart = am4core.create("cpuchart", am4charts.XYChart);

    cpuchart.numberFormatter.numberFormat = "# '%'";

    var title = cpuchart.titles.create();
    title.text = "CPU Latency (%)";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = cpuchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = cpuchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = cpuchart.series.push(new am4charts.LineSeries());
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

    <?php echo $cpuSeries; ?>

    // Add a legend
    cpuchart.legend = new am4charts.Legend();
    cpuchart.legend.useDefaultMarker = true;
    cpuchart.legend.scrollable = false;
    cpuchart.legend.valueLabels.template.align = "right";
    cpuchart.legend.valueLabels.template.textAlign = "end";

    cpuchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("cpulegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    cpuchart.legend.parent = legendContainer;

    cpuchart.events.on("datavalidated", resizeLegend);
    cpuchart.events.on("maxsizechanged", resizeLegend);

    cpuchart.legend.events.on("datavalidated", resizeLegend);
    cpuchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("cpulegenddiv").style.height = cpuchart.legend.contentHeight + "px";
    }

    // Set up export
    cpuchart.exporting.menu = new am4core.ExportMenu();
    cpuchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    cpuchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      cpuchart.series.each(function(series) {
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