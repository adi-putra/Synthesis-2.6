<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>

<?php

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
  $color = array();

  function random_color_part() {
      return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
  }

  function random_color() {
      return random_color_part() . random_color_part() . random_color_part();
  }

  $hostid = $_GET["hostid"] ?? array("10361", "10324", "10346");
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? strtotime("now");
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
        //"search" => array("name" => "VMware: Free disk space on C:\ (percentage)"), //seach id contains specific word
        "search" => array("name" => array("VMware: Average write latency", "datastore"))
        //"searchByAny" => true
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
            //"history" => 0,
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
        $cpuSeries .= 'createSeries("value", "' . $hostname.': '.$itemname. '", [' . $chart_data . ']);';
      }
    }

    if ($result == false) {
      echo '<div><h5 class="text-danger">No data available</h5></div>';
    } else {
      echo '
      <div id="avgWriteLatDatastore" style="width: auto; height: 400px;"></div>
      <div id="avgWriteLegendwrapper">
        <div id="avgWriteLegenddiv"></div>
      </div>
  ';
    }

  ?>



  <script>
    am4core.options.autoDispose = true;

    // am4core.useTheme(am4themes_material);
    // Create chart instance
    var avgWriteLatDatastore = am4core.create("avgWriteLatDatastore", am4charts.XYChart);

    avgWriteLatDatastore.numberFormatter.numberFormat = "#";

    var title = avgWriteLatDatastore.titles.create();
    title.text = "Average Write Latency of Datastore";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = avgWriteLatDatastore.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = avgWriteLatDatastore.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
      var series = avgWriteLatDatastore.series.push(new am4charts.LineSeries());
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
    avgWriteLatDatastore.legend = new am4charts.Legend();
    avgWriteLatDatastore.legend.useDefaultMarker = true;
    avgWriteLatDatastore.legend.scrollable = false;
    avgWriteLatDatastore.legend.valueLabels.template.align = "right";
    avgWriteLatDatastore.legend.valueLabels.template.textAlign = "end";

    avgWriteLatDatastore.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("avgWriteLegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    avgWriteLatDatastore.legend.parent = legendContainer;

    avgWriteLatDatastore.events.on("datavalidated", resizeLegend);
    avgWriteLatDatastore.events.on("maxsizechanged", resizeLegend);

    avgWriteLatDatastore.legend.events.on("datavalidated", resizeLegend);
    avgWriteLatDatastore.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("avgWriteLegenddiv").style.height = avgWriteLatDatastore.legend.contentHeight + "px";
    }

    // Set up export
    avgWriteLatDatastore.exporting.menu = new am4core.ExportMenu();
    avgWriteLatDatastore.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    avgWriteLatDatastore.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      avgWriteLatDatastore.series.each(function(series) {
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