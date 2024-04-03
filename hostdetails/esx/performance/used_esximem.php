<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    #esximemlegenddiv {
      height: 150px;
    }

    #esximemlegendwrapper {
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
        "search" => array("name" => "esxi memory used"), //seach id contains specific word
      );
      //call api method
      $result = $zbx->call('item.get', $params);
      foreach ($result as $item) {
        if (stripos($item["name"], "kilobyte") !== false) {
          continue;
        }
        else {
          $itemid = $item["itemid"];
          $itemname = ucwords($item["name"]);
        }
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
      $memSeries .= 'createSeries("value", "' . $hostname . '", [' . $chart_data . ']);';
    }

    if ($result == false) {
      echo '<div><h5 class="text-danger">No data available</h5></div>';
    } else {
      echo '
      <div id="esximemchart" style="width: auto; height: 400px;"></div>
      <div id="esximemlegendwrapper">
        <div id="esximemlegenddiv"></div>
      </div>
  ';
    }

  ?>



  <script>
    am4core.options.autoDispose = true;

    am4core.useTheme(am4themes_material);
    // Create chart instance
    var esximemchart = am4core.create("esximemchart", am4charts.XYChart);

    esximemchart.numberFormatter.numberFormat = "#.00 '%'";

    var title = esximemchart.titles.create();
    title.text = '<?php echo $itemname." (%)";?>';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Create axes
    var dateAxis = esximemchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";

    var valueAxis = esximemchart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name, data) {
      var series = esximemchart.series.push(new am4charts.LineSeries());
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
    esximemchart.legend = new am4charts.Legend();
    esximemchart.legend.useDefaultMarker = true;
    esximemchart.legend.scrollable = false;
    esximemchart.legend.valueLabels.template.align = "right";
    esximemchart.legend.valueLabels.template.textAlign = "end";

    esximemchart.cursor = new am4charts.XYCursor();

    let legendContainer = am4core.create("esximemlegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    esximemchart.legend.parent = legendContainer;

    esximemchart.events.on("datavalidated", resizeLegend);
    esximemchart.events.on("maxsizechanged", resizeLegend);

    esximemchart.legend.events.on("datavalidated", resizeLegend);
    esximemchart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
      document.getElementById("esximemlegenddiv").style.height = esximemchart.legend.contentHeight + "px";
    }

    // Set up export
    esximemchart.exporting.menu = new am4core.ExportMenu();
    esximemchart.exporting.extraSprites.push({
      "sprite": legendContainer,
      "position": "bottom",
      "marginTop": 20
    });
    esximemchart.exporting.adapter.add("data", function(data, target) {
      // Assemble data from series
      var data = [];
      esximemchart.series.each(function(series) {
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