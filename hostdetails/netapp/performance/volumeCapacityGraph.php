<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10713");
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
    #volCapacitylegenddiv {
      height: 300px;
    }

    #volCapacitylegendwrapper {
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

    $chart_title = "Volume Used Data Capacity";
      
    $params = array(
      "output" => array("name","lastvalue"),
      "hostids" => $hostid
      );

    $app = $zbx->call('application.get',$params);

    foreach($app as $data){

      if($data['name'] == 'Volume API'){

      $applicationid = $data['applicationid'];

      }
    }

    $params = array(
      "output" => array("name","lastvalue"),
      "hostids" => $hostid
      );

    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {

        $gethostname = $host["name"];
      
        $params = array(
        "output" => array("itemid", "name", "error", "lastvalue"),
        "applicationids" => $applicationid,
        "search" => array("name" => "Volume Used Data Capacity"), //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        foreach ($result as $item) {
            $itemid[] = array(
                "id" => $item["itemid"], 
                "name" => $item["name"],
                "hostname" => $gethostname,
                "lastvalue" => $item["lastvalue"]
            );

        }
    }


//sort array by lastvalue
usort($itemid, function($a, $b) {
    return ($a["lastvalue"] > $b["lastvalue"])?-1:1;
});

//slice the array to only top 5
//$itemid = array_slice($itemid, 0, 5);

//print json_encode($itemid);

$itemdata = array();
$volCapacityrecSeries = "";

foreach ($itemid as $item) {

  if(strpos($item["name"], 'Backup') == false && strpos($item["name"], 'Rescan') == false ){
     
    // echo "<pre>";
    // print_r($item);
    // echo "</pre>";
    $item["name"] = substr($item["name"], 29,-1);

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

          $trend["value_max"] = number_format($trend["value_max"]/(1024*1024*1024),2);
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
            "history" => 0,
            "itemids" => $item["id"],
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $timefrom,
            "time_till" => $timetill
        );

        //call api history.get with params
        $result = $zbx->call('history.get', $params);

        foreach (array_reverse($result) as $history) {
         
          $history["value"] = $history["value"]/(1024*1024*1024);
          $clock = $history["clock"] * 1000;
          $itemdata[$count] = array(
              "name" =>$item["name"],
              "clock" => $clock, 
              "value" => $history["value"]
          );


          $count++;
        }
    }
    //$itemdata = json_encode($itemdata);
    $series[$count2] = array(
        "name" => $item["name"],
        "data" => $itemdata
    );
    $count2++;

   
    //echo $itemdata;
    $itemdata = json_encode($itemdata);
    $volCapacityrecSeries .= 'createSeries("value", "' .$item["name"]. '", '.$itemdata.');';
    $itemdata = array();


  }
}

for ($i=0; $i < $count2; $i++) { 
    $color[] = "#".random_color();
}

//print_r($itemdata);
//print $volCapacityrecSeries;
?>


<div id="volCapacityrecchart" style="width: auto;height: 400px;"></div>
<div id="volCapacitylegendwrapper">
    <div id="volCapacitylegenddiv"></div>
</div>
<!-- SQL Stat graph -->
<script type="text/javascript">

    am4core.options.autoDispose = true;
    var volCapacityrec_chart = am4core.create("volCapacityrecchart", am4charts.XYChart);


    volCapacityrec_chart.numberFormatter.numberFormat = "#.00 'GB'";
    //volCapacityrec_chart.fontSize = 12;

    var title = volCapacityrec_chart.titles.create();
    title.text = '<?php echo $chart_title; ?>';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = volCapacityrec_chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = -90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";


    var valueAxis = volCapacityrec_chart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    function createSeries(field, name, data) {
        var series = volCapacityrec_chart.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = field;
        series.dataFields.dateX = "clock";
        series.name = name;
        series.tooltipText = "{name}: [b]{valueY}[/]";
        //series.tooltip.fontSize = 12;
        series.fillOpacity = 0.1;
        series.strokeWidth = 2;
        series.data = data;
        series.legendSettings.valueText = '[[last: {valueY.close}]] [[min: {valueY.low}]] [[avg: {valueY.average]] [[max: {valueY.high}]]';

        return series;
    }

    <?php print $volCapacityrecSeries; ?>

    // Add a legend
    volCapacityrec_chart.legend = new am4charts.Legend();
    volCapacityrec_chart.legend.useDefaultMarker = true;
    volCapacityrec_chart.legend.scrollable = true;
    volCapacityrec_chart.legend.valueLabels.template.align = "right";
    volCapacityrec_chart.legend.valueLabels.template.textAlign = "end";
    //volCapacityrec_chart.legend.fontSize = 12;

    volCapacityrec_chart.cursor = new am4charts.XYCursor();

    var legendContainer = am4core.create("volCapacitylegenddiv", am4core.Container);
    legendContainer.width = am4core.percent(100);
    legendContainer.height = am4core.percent(100);

    volCapacityrec_chart.legend.parent = legendContainer;

    volCapacityrec_chart.events.on("datavalidated", resizeLegend);
    volCapacityrec_chart.events.on("maxsizechanged", resizeLegend);

    volCapacityrec_chart.legend.events.on("datavalidated", resizeLegend);
    volCapacityrec_chart.legend.events.on("maxsizechanged", resizeLegend);

    function resizeLegend(ev) {
        document.getElementById("volCapacitylegenddiv").style.height = volCapacityrec_chart.legend.contentHeight + "px";
    }

    // Set up export
    volCapacityrec_chart.exporting.menu = new am4core.ExportMenu();
    volCapacityrec_chart.exporting.adapter.add("data", function(data, target) {
        // Assemble data from series
        var data = [];
        volCapacityrec_chart.series.each(function(series) {
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
