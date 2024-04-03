<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
</head>

<body>

  <?php

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? "10084";
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();

  //display time format
  $diff = $timetill - $timefrom;

  //declare used and free disk space array to store id
  $useddisk_id = "";

  //fetch diskpace data
  $count = 0;
  $params = array(
    "output" => array("itemid", "name", "error"),
    "hostids" => $hostid,
    "search" => array("name" => array("space utilization", "(percentage)")), //seach id contains specific word
    "searchByAny" => true
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  if (!empty($result)) {
    foreach ($result as $item) {
      //get only ids contain the name 'percentage'
      $itemid[] = $item["itemid"];
      $itemname[] = $item["name"];
      $itemerror[] = $item["error"];
      $count++;
    }
    
  } else {
    print '<div class="col-sm-12 text-danger"><p>No disk found</p></div>';
    return;
  }

  //store disk values and clock
  $temp = array();

  //store respective itemid values
  $diskspace = array();

  //fetch data
  $count2 = 0;
  $diskseries = "";
  $length = count($itemid);
  for ($i = 0; $i < $length; $i++) {
    //perform trend.get if time range above 7 days
    if ($diff >= 604800) {
      $params = array(
        "output" => array("clock", "value_max"),
        "itemids" => $itemid[$i],
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill,
      );
      //call api method
      //store in disk data array
      $result = $zbx->call('trend.get', $params);
      foreach ($result as $disk) {
        $disk["clock"] = $disk["clock"] * 1000;
        //store in temp array
        $temp[$count2] = array("clock" => $disk["clock"], "value" => $disk["value_max"]);
        $count2++;
      }
      $diskspace[$i] = json_encode($temp);
      //$cpuSeries .= 'createSeries("value", "' . $itemname[$i] . '", '.$diskspace[$i].');';
      $temp = array();
      $count2 = 0;
    } else {
      $params = array(
        "output" => array("clock", "value"),
        "history" => 0,
        "itemids" => $itemid[$i],
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill,
      );
      //call api method
      //store in disk data array
      $result = $zbx->call('history.get', $params);
      foreach (array_reverse($result) as $disk) {
        $disk["clock"] = $disk["clock"] * 1000;
        //store in temp array
        $temp[$count2] = array("clock" => $disk["clock"], "value" => $disk["value"]);
        $count2++;
      }
      $diskspace[$i] = json_encode($temp);
      //$cpuSeries .= 'createSeries("value", "' . $itemname[$i] . '", '.$diskspace[$i].');';
      $temp = array();
      $count2 = 0;
    }
  }

  //print graph divs
  for ($i = 0; $i < $length; $i++) {
    if (!empty($itemerror[$i])) {
      continue;
      /*print '
    <div class="col-md-12">
    <div id="disk' . $i . 'legenddiv"></div>
    <h3 class="text-center" style="color: #000;">' . $itemname[$i] . '</h3>
    <h5 class="text-center text-danger">' . $itemerror[$i] . '</h5>
    </div>';
    } else if($disk["value"] == 0 && $disk["value_max"] == 0){
      print '
      <div class="col-md-12">
      <div id="disk' . $i . 'legenddiv"></div>
      <h3 class="text-center" style="color: #000;">' . $itemname[$i] . '</h3>
      <h5 class="text-center text-danger">0% on ' . $itemname[$i] . '</h5>
      </div>';*/
    }
    else {
      print '<script>
        am4core.options.autoDispose = true;

        // am4core.useTheme(am4themes_material);
        // Create chart instance
        var disk' . $i . ' = am4core.create("disk' . $i . '", am4charts.XYChart);

        disk' . $i . '.numberFormatter.numberFormat = "# ' . "'%'" . '";

        var title = disk' . $i . '.titles.create();
        title.text = "' . $itemname[$i] . '";
        title.fontSize = 25;
        title.marginBottom = 30;
        title.align = "center";

        // Create axes
        var dateAxis = disk' . $i . '.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 20;
        dateAxis.renderer.labels.template.rotation = -90;
        dateAxis.renderer.labels.template.verticalCenter = "middle";
        dateAxis.renderer.labels.template.horizontalCenter = "left";

        var valueAxis = disk' . $i . '.yAxes.push(new am4charts.ValueAxis());
        valueAxis.min = 0;

        // Create series
        function createSeries(field, name, data) {
        var series = disk' . $i . '.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = field;
        series.dataFields.dateX = "clock";
        series.name = name;
        series.tooltipText = "{name}: [b]{valueY}[/]";
        series.fillOpacity = 0.1;
        series.strokeWidth = 2;
        series.data = data;
        series.legendSettings.valueText = "[[last: {valueY.close}]] [[min: {valueY.low}]] [[avg: {valueY.average}]] [[max: {valueY.high}]]";

        return series;
        }

        createSeries("value", "' . $itemname[$i] . '", ' . $diskspace[$i] . ');

        // Add a legend
        disk' . $i . '.legend = new am4charts.Legend();
        disk' . $i . '.legend.useDefaultMarker = true;
        disk' . $i . '.legend.scrollable = false;
        disk' . $i . '.legend.valueLabels.template.align = "right";
        disk' . $i . '.legend.valueLabels.template.textAlign = "end";

        disk' . $i . '.cursor = new am4charts.XYCursor();

        var legendContainer = am4core.create("disk' . $i . 'legenddiv", am4core.Container);
        legendContainer.width = am4core.percent(100);
        legendContainer.height = am4core.percent(100);

        disk' . $i . '.legend.parent = legendContainer;

        disk' . $i . '.events.on("datavalidated", resizeLegend);
        disk' . $i . '.events.on("maxsizechanged", resizeLegend);

        disk' . $i . '.legend.events.on("datavalidated", resizeLegend);
        disk' . $i . '.legend.events.on("maxsizechanged", resizeLegend);

        function resizeLegend(ev) {
        document.getElementById("disk' . $i . 'legenddiv").style.height = disk' . $i . '.legend.contentHeight + "px";
        }

        // Set up export
        disk' . $i . '.exporting.menu = new am4core.ExportMenu();
        disk' . $i . '.exporting.extraSprites.push({
        "sprite": legendContainer,
        "position": "bottom",
        "marginTop": 20
        });
        disk' . $i . '.exporting.adapter.add("data", function(data, target) {
        // Assemble data from series
        var data = [];
        disk' . $i . '.series.each(function(series) {
          for (var i = 0; i < series.data.length; i++) {
            series.data[i].name = series.name;
            data.push(series.data[i]);
          }
        });
        return {
          data: data
        };
        });
        </script>';

      print '<div class="col-md-12">
                  <div id="disk' . $i . '" style="width: auto; height: 400px;"></div>
                  <div id="disk' . $i . 'legenddiv"></div>
                </div>';
    }
  }

  //echo json_encode($disk_data);
  //echo $count2;
  ?>

</body>

</html>