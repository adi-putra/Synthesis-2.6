<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
	);
//call api method
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2) { 
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

	$bytes = max($bytes, 0); 
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	$pow = min($pow, count($units) - 1); 

	// Uncomment one of the following alternatives
	$bytes /= pow(1024, $pow);
	// $bytes /= (1 << (10 * $pow)); 

	return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>LUN Graph</title>

        <style>
            #chartdiv {
                width: 100%;
                height: 250px;
            }
        </style>
    </head>
    <body>

        <script src="//cdn.amcharts.com/lib/4/core.js"></script>
        <script src="//cdn.amcharts.com/lib/4/charts.js"></script>
        <script src="//cdn.amcharts.com/lib/4/themes/animated.js"></script>
                
        <div id="chartdiv" style="width: 100%; height: 400px;"></div>

        <script>
            // Set theme
            am4core.useTheme(am4themes_animated);

            // Create chart
            var chart = am4core.create("chartdiv", am4charts.GaugeChart);

            // Create axis
            var axis = chart.xAxes.push(new am4charts.ValueAxis());
            axis.min = 0;
            axis.max = 100;
            axis.strictMinMax = true;

            // Set inner radius
            chart.innerRadius = -20;

            // Add ranges
            var range = axis.axisRanges.create();
            range.value = 0;
            range.endValue = 70;
            range.axisFill.fillOpacity = 1;
            range.axisFill.fill = am4core.color("#88AB75");
            range.axisFill.zIndex = - 1;

            var range2 = axis.axisRanges.create();
            range2.value = 70;
            range2.endValue = 90;
            range2.axisFill.fillOpacity = 1;
            range2.axisFill.fill = am4core.color("#DBD56E");
            range2.axisFill.zIndex = - 1;

            var range3 = axis.axisRanges.create();
            range3.value = 90;
            range3.endValue = 100;
            range3.axisFill.fillOpacity = 1;
            range3.axisFill.fill = am4core.color("#DE8F6E");
            range3.axisFill.zIndex = - 1;

            // Add hand
            var hand = chart.hands.push(new am4charts.ClockHand());
            hand.value = 65;
            hand.pin.disabled = true;
            hand.fill = am4core.color("#2D93AD");
            hand.stroke = am4core.color("#2D93AD");
            hand.innerRadius = am4core.percent(50);
            hand.radius = am4core.percent(80);
            hand.startWidth = 15;

            var hand2 = chart.hands.push(new am4charts.ClockHand());
            hand2.value = 22;
            hand2.pin.disabled = true;
            hand2.fill = am4core.color("#7D7C84");
            hand2.stroke = am4core.color("#7D7C84");
            hand2.innerRadius = am4core.percent(50);
            hand2.radius = am4core.percent(80);
            hand2.startWidth = 15;

            // Animate
            setInterval(function() {
            hand.showValue(Math.random() * 100, 1000, am4core.ease.cubicOut);
            hand2.showValue(Math.random() * 100, 1000, am4core.ease.cubicOut);
            }, 2000);
        </script>

   </body>
</html>