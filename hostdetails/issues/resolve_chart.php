<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$objectid = $_GET["objectid"] ?? 121559;
$hostid = $_GET["hostid"] ?? 10700;
$limit = $_GET["limit"] ?? 10;

//echo $objectid;

$timeline = "";
$clock_value = array();

//get clock values and insert in array
$params = array(
"output" => array("name", "clock"),
"objectids" => $objectid,
"sortfield" => array("clock"),
"sortorder" => "DESC",
"value" => 0,
"limit" => $limit
);
//call api method
$result = $zbx->call('event.get', $params);
foreach (array_reverse($result) as $object) {
    $problemName = $object["name"];
    if (stripos($problemName, "job failed") !== false) {
        break;
    }
    
    $clock_value[] = date("Y-m-d H:i", $object["clock"]);  
    
}

//if the problem name is job failed, do new query and search for all job failed
$count = 0;
if (stripos($problemName, "job failed") !== false) {
    $params = array(
    "output" => array("name", "clock"),
    "hostids" => $hostid,
    "sortfield" => array("clock"),
    "sortorder" => "DESC",
    "search" => array("name" => "Job Failed"),
    "limit" => $limit
    );
    //call api method
    $jf_result = $zbx->call('event.get', $params);
    foreach (array_reverse($jf_result) as $jf) {
        if ($count % 2 == 0) {
            $clock_value[] = date("Y-m-d H:i", $jf["clock"]);
        }
        else {
            $clock_value[] = date("Y-m-d H:i", $jf["clock"] + 60);
        }
        $count++;
    }
}

//$clock_value = sort($clock_value);
//echo json_encode(sort($clock_value));
//echo json_encode($clock_value);

//get values
$count = 0;
$params = array(
"output" => "extend",
"objectids" => $objectid,
"sortfield" => array("clock"),
"sortorder" => "DESC",
"limit" => $limit
);
//call api method
$result = $zbx->call('event.get', $params);
foreach (array_reverse($result) as $object) {
    $eventid = $object["eventid"];
    $value = $object["value"];
    $problemName = $object["name"];

    if (stripos($problemName, "job failed") !== false) {
        break;
    }

    //get time values from previous array
    $start_time = $clock_value[$count];
    if ($clock_value[$count + 1] == "") {
        $end_time = strtotime($clock_value[$count]) + 60*60;
        $end_time = date("Y-m-d H:i", $end_time);
    }
    else {
        $end_time = $clock_value[$count + 1];
    }

    //configure status of event and color
    if ($value == 0) {
        $text = "OK";
        $color = 'am4core.color("green")';
    }
    else if ($value == 1) {
        $text = "PROBLEM";
        $color = 'am4core.color("red")';
    }

    $timeline .= '{"category": "", ';
    $timeline .= '"start": "'.$start_time.'", ';
    $timeline .= '"end": "'.$end_time.'", ';
    $timeline .= '"color": '.$color.', ';
    $timeline .= '"text": "'.$text.'", ';
    //$timeline .= '"text": "'.$start_time.'\n'."- ".'\n'.$end_time.'", ';
    $timeline .= '"textDisabled": false}, ';
    $count++;
}

if (stripos($problemName, "job failed") !== false) {
    $params = array(
    "output" => "extend",
    "hostids" => $hostid,
    "sortfield" => array("clock"),
    "sortorder" => "DESC",
    "search" => array("name" => "Job Failed"),
    "limit" => $limit
    );
    //call api method
    $jf_result = $zbx->call('event.get', $params);
    foreach (array_reverse($jf_result) as $jf) {
        $eventid = $jf["eventid"];
        $value = $jf["value"];
        $problemName = "Job Failed";
        $text = substr($jf["name"], 10);

        //get time values from previous array
        $start_time = $clock_value[$count];
        if ($clock_value[$count + 1] == "") {
            $end_time = strtotime($clock_value[$count]) + 60*60;
            $end_time = date("Y-m-d H:i", $end_time);
        }
        else {
            $end_time = $clock_value[$count + 1];
        }

        //configure status of event and color
        if ($value == 0) {
            $color = 'am4core.color("green")';
        }
        else if ($value == 1) {
            $color = 'am4core.color("red")';
        }

        $timeline .= '{"category": "", ';
        $timeline .= '"start": "'.$start_time.'", ';
        $timeline .= '"end": "'.$end_time.'", ';
        $timeline .= '"color": '.$color.', ';
        $timeline .= '"text": "'.$text.'", ';
        $timeline .= '"textDisabled": false}, ';
        $count++;
    }
}

//echo $timeline;
//$timeline_json = json_encode($timeline, JSON_NUMERIC_CHECK);
//echo $timeline_json;

?>
<html>
    <head>
        <style>
            #chartdiv {
                width: 100%;
                height: 400px;
            }
        </style>
    </head>
    <body>  
            <!-- Problem's Timeline Limit Dropdown -->
            <?php
            if ($limit == "") {
                $select_text = "-- Select Number of Results --";
            }
            else {
                $select_text = $limit;
            }
            ?>
            <label for="cars" style="font-weight: normal; margin: 10px;"><p>Show num. result: </p></label>
            <select id="selectLimit" onchange="selectLimit()">
                <option value="" selected disabled hidden><?php echo $select_text; ?></option>
                <option value="10">10</option>
                <option value="20">20</option>
            </select>

            <!-- selectLimit function -->
            <script>
                function selectLimit() {
                    //get values from dropdown
                    var limit = document.getElementById("selectLimit").value;

                    //get current values
                    var objectid = '<?php echo $objectid; ?>';
                    var hostid = '<?php echo $hostid; ?>';

                    $("#resolve_chart").load("/synthesis/problems/resolve_chart.php?objectid=" + objectid + "&hostid=" + hostid + "&limit=" + limit);
                }
            </script>

            <h3 align="center"><?php echo $problemName; ?></h3>
            <div id="chartdiv"></div>
        
    </body>
</html>

<script>
    /**
 * ---------------------------------------
 * This demo was created using amCharts 4.
 * 
 * For more information visit:
 * https://www.amcharts.com/
 * 
 * Documentation is available at:
 * https://www.amcharts.com/docs/v4/
 * ---------------------------------------
 */

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end

var chart = am4core.create("chartdiv", am4plugins_timeline.SerpentineChart);
am4core.options.autoDispose = true;
chart.curveContainer.padding(100, 20, 50, 20);
chart.levelCount = 2;
chart.yAxisRadius = am4core.percent(20);
chart.yAxisInnerRadius = am4core.percent(2);
chart.maskBullets = false;

var colorSet = new am4core.ColorSet();

chart.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm";
chart.dateFormatter.dateFormat = "HH";

chart.data = [<?php echo $timeline;?>];

chart.fontSize = 10;
chart.tooltipContainer.fontSize = 15;

var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "category";
categoryAxis.renderer.grid.template.disabled = true;
categoryAxis.renderer.labels.template.paddingRight = 25;
categoryAxis.renderer.minGridDistance = 10;

var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.minGridDistance = 70;
dateAxis.baseInterval = { count: 1, timeUnit: "minute" };
dateAxis.tooltipDateFormat = "yyyy-MM-dd H:mm a";
dateAxis.renderer.tooltipLocation = 0;
dateAxis.renderer.line.strokeDasharray = "1,4";
dateAxis.renderer.line.strokeOpacity = 0.5;
dateAxis.tooltip.background.fillOpacity = 0.2;
dateAxis.tooltip.background.cornerRadius = 5;
dateAxis.tooltip.label.fill = new am4core.InterfaceColorSet().getFor("alternativeBackground");
dateAxis.tooltip.label.paddingTop = 7;
dateAxis.endLocation = 0;
dateAxis.startLocation = -0.5;

var labelTemplate = dateAxis.renderer.labels.template;
labelTemplate.verticalCenter = "middle";
labelTemplate.fillOpacity = 0.4;
labelTemplate.background.fill = new am4core.InterfaceColorSet().getFor("background");
labelTemplate.background.fillOpacity = 1;
labelTemplate.padding(7, 7, 7, 7);

var series = chart.series.push(new am4plugins_timeline.CurveColumnSeries());
series.columns.template.height = am4core.percent(15);

series.dataFields.openDateX = "start";
series.dataFields.dateX = "end";
series.dataFields.categoryY = "category";
series.baseAxis = categoryAxis;
series.columns.template.propertyFields.fill = "color"; // get color from data
series.columns.template.propertyFields.stroke = "color";
series.columns.template.strokeOpacity = 0;
series.columns.template.fillOpacity = 0.6;

var imageBullet1 = series.bullets.push(new am4plugins_bullets.PinBullet());
imageBullet1.locationX = 1;
imageBullet1.propertyFields.stroke = "color";
imageBullet1.background.propertyFields.fill = "color";
imageBullet1.image = new am4core.Image();
imageBullet1.image.propertyFields.href = "icon";
imageBullet1.image.scale = 0.5;
imageBullet1.circle.radius = am4core.percent(100);
imageBullet1.dy = -5;

var textBullet = series.bullets.push(new am4charts.LabelBullet());
textBullet.label.propertyFields.text = "text";
textBullet.disabled = true;
textBullet.propertyFields.disabled = "textDisabled";
textBullet.label.strokeOpacity = 0;
textBullet.locationX = 1;
textBullet.dy = - 100;
textBullet.label.textAlign = "middle";

var cursor = new am4plugins_timeline.CurveCursor();
chart.cursor = cursor;
cursor.xAxis = dateAxis;
cursor.yAxis = categoryAxis;
cursor.lineY.disabled = true;
cursor.lineX.strokeDasharray = "1,4";
cursor.lineX.strokeOpacity = 1;

var label = chart.createChild(am4core.Label);
label.text = '<?php echo "Result: ".$count ;?>';
label.fontSize = 20;
label.align = "right";

dateAxis.renderer.tooltipLocation2 = 0;
categoryAxis.cursorTooltipEnabled = false;



</script>