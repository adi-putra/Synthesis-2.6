<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
</head>

<body>

  <!-- Resources -->

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
  }else{
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
    $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid[$i],
    );
    //call api method
    //store in disk data array
    $result = $zbx->call('item.get', $params);
    foreach ($result as $disk) {
      //if current value is 0, then used value also 0

      if (stripos($item["name"], "space utilization") !== false) {
        if ($disk["lastvalue"] == 0) {
          $freevalue = 100;
          $usedvalue = 0;
        }
        else {
          $freevalue = 100 - $disk["lastvalue"];
          $usedvalue = $disk["lastvalue"];
        }
      }
      else {
        if ($disk["lastvalue"] == 0) {
          $usedvalue = 100;
          $freevalue = 0;
        }
        else {
          $usedvalue = 100 - $disk["lastvalue"];
          $freevalue = $disk["lastvalue"];
        }
      }
      //store in temp array
      
      $temp[$count2] = array("category" => "Used", "value" => "$usedvalue");
      $count2++;
      $temp[$count2] = array("category" => "Free", "value" => "$freevalue");
      $count2++;
    }
    $diskspace[$i] = json_encode($temp);
    //$cpuSeries .= 'createSeries("value", "' . $itemname[$i] . '", '.$diskspace[$i].');';
    $temp = array();
    $count2 = 0;
  }

   //print graph divs
   for ($i = 0; $i < $length; $i++) {
    if (!empty($itemerror[$i])) {

      continue;
      
      /*print '<!-- Chart code -->
      <script>
      // Themes begin
      // am4core.useTheme(am4themes_material);
      // Themes end

      // Create chart instance
      var diskpie' . $i . ' = am4core.create("diskpie' . $i . '", am4charts.PieChart);

      // Add dummy data and do not display value 
      diskpie' . $i . '.data = [{
        "disabled": true,
        "empty": "Empty",
        "value": "100" 
      }];


      var title = diskpie' . $i . '.titles.create();
      title.text = "' . $itemname[$i] . '";
      title.fontSize = 25;
      title.marginBottom = 30;
      title.align = "center";

      // Add and configure Series
      var pieSeries = diskpie' . $i . '.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "value";
      pieSeries.alignLabels = false;
      pieSeries.slices.template.stroke = am4core.color("#000");
      pieSeries.slices.template.strokeOpacity = 1;
      //Remove Labels
      pieSeries.labels.template.propertyFields.disabled = "disabled";
      pieSeries.ticks.template.propertyFields.disabled = "disabled";
      pieSeries.dataFields.hiddenInLegend = "disabled";
      pieSeries.tooltip.disabled = true;

      let label = pieSeries.createChild(am4core.Label);
      label.text = "' . $itemerror[$i] . '";
      label.horizontalCenter = "middle";
      label.verticalCenter = "middle";
      label.fontSize = 16;
      label.fill = "#a94442";
      label.wrap = true;
      label.maxWidth = 120;


      pieSeries.colors.list = [
      am4core.color("#fff")
    ];

      // This creates initial animation
      pieSeries.hiddenState.properties.opacity = 1;
      pieSeries.hiddenState.properties.endAngle = -90;
      pieSeries.hiddenState.properties.startAngle = -90;
      diskpie' . $i . '.hiddenState.properties.radius = am4core.percent(0);
      </script>';*/

 
    } 
    
    else {
      print '<!-- Chart code -->
          <script>
          am4core.options.autoDispose = true;

          // Themes begin
          // am4core.useTheme(am4themes_material);
          // Themes end

          // Create chart instance
          var diskpie' . $i . ' = am4core.create("diskpie' . $i . '", am4charts.PieChart);

          // Add data
          diskpie' . $i . '.data = ' . $diskspace[$i] . ';

          var title = diskpie' . $i . '.titles.create();
          title.text = "' . $itemname[$i] . '";
          title.fontSize = 25;
          title.marginBottom = 30;
          title.align = "center";

          // Add and configure Series
          var pieSeries = diskpie' . $i . '.series.push(new am4charts.PieSeries());
          pieSeries.dataFields.value = "value";
          pieSeries.dataFields.category = "category";
          pieSeries.slices.template.stroke = am4core.color("#fff");
          pieSeries.slices.template.strokeOpacity = 1;
          pieSeries.slices.template.strokeWidth = 1;

          pieSeries.alignLabels = false;
          pieSeries.ticks.template.disabled = true;
          pieSeries.labels.template.radius = am4core.percent(-40); //Place numbers in pie chart
          pieSeries.labels.template.fill = am4core.color("white");
          

          pieSeries.colors.list = [
          am4core.color("#dd4b39"),
          am4core.color("#00a65a")
        ];

          // This creates initial animation
          pieSeries.hiddenState.properties.opacity = 1;
          pieSeries.hiddenState.properties.endAngle = -90;
          pieSeries.hiddenState.properties.startAngle = -90;
          diskpie' . $i . '.hiddenState.properties.radius = am4core.percent(0);
          </script>';
    }
    print '<div class="col-md-6">
    <div id="diskpie' . $i . '" style="width: auto; height: 400px;"></div>
    </div>';
  }
  ?>

</body>

</html>