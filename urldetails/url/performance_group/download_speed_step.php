<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//$groupid = $_GET["groupid"];
//$hostid = $_GET["hostid"];
$webgroup = $_GET['webgroup'];

if (empty($hostid)) {
    $params = array(
        "output" => array("hostid", "name"),
        "groupids" => $groupid
    );

    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $hostid[] = $host["hostid"];
    }
}

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

//display time format
$diff = $timetill - $timefrom;
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title></title>
        <style>
            #dwld_speed_scenariolegenddiv {
            height: 300px;
            }

            #dwld_speed_scenariolegendwrapper {
            max-height: 400px;
            overflow-x: none;
            overflow-y: auto;
            }
        </style>
    </head>

    <body>

        <?php
            //store itemid array
            $itemid = array();
            $count2 = 0;
            $series = array();
            $color = array();

            function random_color_part() {
                return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
            }

            function random_color() {
                return random_color_part() . random_color_part() . random_color_part();
            }
            $params = array(
                "output" => "extend",
                "selectSteps" => "extend",
                "selectTags" => "extend",
                "search" => array("name" => $webgroup)
            );

            $result = $zbx->call('httptest.get', $params);
            foreach($result as $httptest){

                $http_id = $httptest['httptestid'];
                $host_id = $httptest['hostid'];

                //get hostname for each web
                $params = array(
                "output" => "extend",
                "hostids" => $host_id
                );

                $res_host = $zbx->call('host.get', $params);
                if(!empty($res_host)){
                    foreach($res_host as $host){

                        $gethostname = $host['name'];
                    }

                    $params = array(
                        "output" => "extend",
                        "hostids" => $host_id,
                        "webitems" => true
                    );

                    $res_webitem = $zbx->call('item.get', $params);
                    foreach($res_webitem as $item){
                        if (strpos($item['name'], $webgroup) !== false){

                            //download speed
                            if (strpos($item['name'], "Download speed for step") !== false){
            
                                $item["name"] = str_replace(['"','.'],"",$item["name"]);
                                $itemid[] = array(
                                    "id" => $item["itemid"], 
                                    "name" => $item["name"],
                                    "hostname" => $gethostname,
                                    "lastvalue" => $item["lastvalue"]
                                );
                            }
                        }
                    }
                }
            }
            //sort array by lastvalue
            usort($itemid, function($a, $b) {
                return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
            });

            //slice the array to only top 5
            $itemid = array_slice($itemid, 0, 10);

            //print json_encode($itemid);

            $itemdata = array();
            $dwld_speed_scenarioSeries = "";

            foreach ($itemid as $item) {
                //perform trend.get if time range above 7 days
                if ($diff >= 604800) {
                    $count = 0;
                    $params = array(
                        "output" => array("clock", "value_max"),
                        "itemids" => $item["id"],
                        "sortfield" => "clock",
                        "sortorder" => "DESC",
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                    );

                    //call api history.get with params
                    $result = $zbx->call('trend.get', $params);
                    foreach ($result as $trend) {
                        $itemdata[$count] = array(
                            "clock" => $trend["clock"] * 1000, 
                            "value" => $trend["value_max"]
                        );
                        $count++;
                    }
                }
                else {
                    $count = 0;
                    $params = array(
                        "output" => array("clock", "value"),
                        "itemids" => $item["id"],
                        "sortfield" => "clock",
                        "sortorder" => "DESC",
                        "history" => 0,
                        "time_from" => $timefrom,
                        "time_till" => $timetill
                    );

                    //call api history.get with params
                    $result = $zbx->call('history.get', $params);
                    foreach (array_reverse($result) as $history) {
                        $clock = $history["clock"] * 1000;
                        $itemdata[$count] = array(
                            "clock" => $clock, 
                            "value" => $history["value"]
                        );
                        $count++;
                    }
                }
                //$itemdata = json_encode($itemdata);
                $series[$count2] = array(
                    "name" => $item["hostname"].": ".$item["name"],
                    "data" => $itemdata
                );
                $count2++;
                //echo $itemdata;
                if (empty($itemdata)) {
                continue;
                }
                else {
                $itemdata = json_encode($itemdata);
                $dwld_speed_scenarioSeries .= 'createSeries("value", "' .$item["hostname"].": ".$item["name"]. '", '.$itemdata.');';
                $itemdata = array();
                }
            }

            for ($i=0; $i < $count2; $i++) { 
                $color[] = "#".random_color();
            }

            //echo json_encode($color);
           //print $dwld_speed_scenarioSeries;

            if ($dwld_speed_scenarioSeries == "") {
            print '<h5 style="color: red;">No data available</h5>';
            }
            else {
            print '<div id="dwld_speed_scenariochart" style="width: auto;height: 400px;"></div>
                    <div id="dwld_speed_scenariolegendwrapper">
                        <div id="dwld_speed_scenariolegenddiv"></div>
                    </div>';
            }
        ?>

        <!-- <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
        <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
        <script src="https://cdn.amcharts.com/lib/4/themes/material.js"></script> -->

        <!-- CPU Temp graph -->
        <script type="text/javascript">

            am4core.options.autoDispose = true;
            //am4core.options.minPolylineStep = 5;
            //am4core.useTheme(am4themes_animated);
            //am4core.useTheme(am4themes_frozen);
            // Create chart instance
            var dwld_speed_scenario_chart = am4core.create("dwld_speed_scenariochart", am4charts.XYChart);


            dwld_speed_scenario_chart.numberFormatter.numberFormat = "#.##b'ps'";
            //dwld_speed_scenario_chart.fontSize = 12;

            var title = dwld_speed_scenario_chart.titles.create();
            title.text = 'Download Speed Graph';
            title.fontSize = 25;
            title.marginBottom = 30;
            title.align = 'center';

            // Create axes
            var dateAxis = dwld_speed_scenario_chart.xAxes.push(new am4charts.DateAxis());
            dateAxis.renderer.minGridDistance = 20;
            dateAxis.renderer.labels.template.rotation = -90;
            dateAxis.renderer.labels.template.verticalCenter = "middle";
            dateAxis.renderer.labels.template.horizontalCenter = "left";


            var valueAxis = dwld_speed_scenario_chart.yAxes.push(new am4charts.ValueAxis());
            valueAxis.min = 0;

            // Create series
            function createSeries(field, name, data) {
                var series = dwld_speed_scenario_chart.series.push(new am4charts.LineSeries());
                series.dataFields.valueY = field;
                series.dataFields.dateX = "clock";
                series.name = name;
                series.tooltipText = "{name}: [b]{valueY}[/]";
                //series.tooltip.fontSize = 12;
                series.fillOpacity = 0.1;
                series.strokeWidth = 2;
                series.data = data;
                series.legendSettings.valueText = '[[last: {valueY.close.formatNumber(\'#.##\')}]] [[min: {valueY.low.formatNumber(\'#.##\')}]] [[avg: {valueY.average.formatNumber(\'#.##\')}]] [[max: {valueY.high.formatNumber(\'#.##\')}]]';

                return series;
            }

            <?php print $dwld_speed_scenarioSeries; ?>

            // Add a legend
            dwld_speed_scenario_chart.legend = new am4charts.Legend();
            dwld_speed_scenario_chart.legend.useDefaultMarker = true;
            dwld_speed_scenario_chart.legend.scrollable = true;
            dwld_speed_scenario_chart.legend.valueLabels.template.align = "right";
            dwld_speed_scenario_chart.legend.valueLabels.template.textAlign = "end";
            //dwld_speed_scenario_chart.legend.fontSize = 12;

            dwld_speed_scenario_chart.cursor = new am4charts.XYCursor();

            var legendContainer = am4core.create("dwld_speed_scenariolegenddiv", am4core.Container);
            legendContainer.width = am4core.percent(100);
            legendContainer.height = am4core.percent(100);

            dwld_speed_scenario_chart.legend.parent = legendContainer;

            dwld_speed_scenario_chart.events.on("datavalidated", resizeLegend);
            dwld_speed_scenario_chart.events.on("maxsizechanged", resizeLegend);

            dwld_speed_scenario_chart.legend.events.on("datavalidated", resizeLegend);
            dwld_speed_scenario_chart.legend.events.on("maxsizechanged", resizeLegend);

            function resizeLegend(ev) {
                document.getElementById("dwld_speed_scenariolegenddiv").style.height = dwld_speed_scenario_chart.legend.contentHeight + "px";
            }

            // Set up export
            dwld_speed_scenario_chart.exporting.menu = new am4core.ExportMenu();
            dwld_speed_scenario_chart.exporting.adapter.add("data", function(data, target) {
                // Assemble data from series
                var data = [];
                dwld_speed_scenario_chart.series.each(function(series) {
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

