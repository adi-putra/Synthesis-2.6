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

  $hostid = $_GET["hostid"] ?? "10361";
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();

  //display time format
  $diff = $timetill - $timefrom;

  //declare used and free disk space array to store id
  $useddisk_id = "";

  //fetch diskpace data
  $count = 0;
  $params = array(
    "output" => array("itemid", "name"),
    "hostids" => $hostid,
    "search" => array("name" => "disk space on") //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  if (!empty($result)) {
    foreach ($result as $item) {
      //get only ids contain the name 'percentage'
      if (stripos($item["name"], "percentage") !== false) {
        $itemid[] = $item["itemid"];
        $itemname[] = $item["name"];
        $count++;
      }
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
    $params = array(
      "output" => array("lastvalue"),
      "itemids" => $itemid[$i],
    );
    //call api method
    //store in disk data array
    $result = $zbx->call('item.get', $params);
    foreach ($result as $disk) {
      //if current value is 0, then used value also 0
      if ($disk["lastvalue"] == 0) {
        $usedvalue = 0;
      }
      else {
        $usedvalue = 100 - $disk["lastvalue"];
      }
      //store in temp array
      $freevalue = $disk["lastvalue"];
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

          pieSeries.colors.list = [
          am4core.color("#FF0000"),
          am4core.color("#2EFF00"),
        ];

          // This creates initial animation
          pieSeries.hiddenState.properties.opacity = 1;
          pieSeries.hiddenState.properties.endAngle = -90;
          pieSeries.hiddenState.properties.startAngle = -90;

          diskpie' . $i . '.hiddenState.properties.radius = am4core.percent(0);

          </script>';

    print '<div class="col-md-6">
                  <div id="diskpie' . $i . '" style="width: auto; height: 400px;"></div>
                </div>';
  }
  ?>

</body>

</html>