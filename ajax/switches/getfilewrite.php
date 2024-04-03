<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
</head>
<body>
                                <?php

                                include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

                                $hostid = $_GET["hostid"] ?? array("10361", "10324", "10346");
                                $timefrom = $_GET['timefrom'] ?? strtotime("today");
                                $timetill = $_GET['timetill'] ?? strtotime("now");
                                //display time format
                                $diff = $timetill - $timefrom;



                                $fileWriteSeries = '';
                                foreach ($hostid as $hostID) {
                                $params = array(
                                "output" => array("name"),
                                "hostids" => $hostID
                                );
                                //call api method
                                $result = $zbx->call('host.get',$params);
                                foreach ($result as $host) {

                                $hostname = $host["name"];

                                $params = array(
                                "output" => array("itemid", "name"),
                                "hostids" => $hostID,
                                "search" => array("key_" => 'perf_counter[\2\18]'),//seach id contains specific word
                                );
                                //call api method
                                $result = $zbx->call('item.get',$params);
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
                                  $result = $zbx->call('trend.get',$params);
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
                                }

                                else {
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
                                $result = $zbx->call('history.get',$params);
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
                                $fileWriteSeries .= 'createSeries("value", "'.$hostname.'", ['.$chart_data.']);';
                                  }
                                  }
                                }

                                ?>

                                <div id="filewchart" style="width: auto; height: 400px;"></div>
                                <div id="filewlegenddiv"></div>

                                <script>
                                am4core.options.autoDispose = true;
                               
                                // am4core.useTheme(am4themes_material);
                                // Create chart instance
                                var filewchart = am4core.create("filewchart", am4charts.XYChart);
      
                                filewchart.numberFormatter.numberFormat = "#.00b";

                                var title = filewchart.titles.create();
                                title.text = "<?php echo $itemname ?>";
                                title.fontSize = 25;
                                title.marginBottom = 30;
                                title.align = "center";

                                // Create axes
                                var dateAxis = filewchart.xAxes.push(new am4charts.DateAxis());
                                dateAxis.renderer.minGridDistance = 20;
                                dateAxis.renderer.labels.template.rotation = -90;
                                dateAxis.renderer.labels.template.verticalCenter = "middle";
                                dateAxis.renderer.labels.template.horizontalCenter = "left";

                                var valueAxis = filewchart.yAxes.push(new am4charts.ValueAxis());

                                // Create series
                                function createSeries(field, name, data) {
                                  var series = filewchart.series.push(new am4charts.LineSeries());
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

                                <?php echo $fileWriteSeries; ?>

                                // Add a legend
                                filewchart.legend = new am4charts.Legend();
                                filewchart.legend.useDefaultMarker = true;
                                filewchart.legend.scrollable = false;
                                filewchart.legend.valueLabels.template.align = "right";
                                filewchart.legend.valueLabels.template.textAlign = "end"; 

                                filewchart.cursor = new am4charts.XYCursor();

                                let legendContainer = am4core.create("filewlegenddiv", am4core.Container);
                                legendContainer.width = am4core.percent(100);
                                legendContainer.height = am4core.percent(100);

                                filewchart.legend.parent = legendContainer;

                                filewchart.events.on("datavalidated", resizeLegend);
                                filewchart.events.on("maxsizechanged", resizeLegend);

                                filewchart.legend.events.on("datavalidated", resizeLegend);
                                filewchart.legend.events.on("maxsizechanged", resizeLegend);

                                function resizeLegend(ev) {
                                  document.getElementById("filewlegenddiv").style.height = filewchart.legend.contentHeight + "px";
                                }

                                // Set up export
                                filewchart.exporting.menu = new am4core.ExportMenu();
                                filewchart.exporting.extraSprites.push({
                                    "sprite": legendContainer,
                                    "position": "bottom",
                                    "marginTop": 20
                                  });
                                filewchart.exporting.adapter.add("data", function(data, target) {
                                  // Assemble data from series
                                  var data = [];
                                  filewchart.series.each(function(series) {
                                    for(var i = 0; i < series.data.length; i++) {
                                      series.data[i].name = series.name;
                                      data.push(series.data[i]);
                                    }
                                  });
                                  return { data: data };
                                });
                                </script>
</body>
</html>
                                


                                




    