<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
</head>

<body>

  <!-- Resources -->

  <?php

  //ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? "10713";
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();

  //display time format
  $diff = $timetill - $timefrom;

  //declare used and free disk space array to store id
  $useddisk_id = "";

  //fetch diskpace data
  $count = 0;
  $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => ": Data File Size"), //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);
  if (!empty($result)) {
    foreach ($result as $item) {
      //get only ids contain the name 'percentage'
      // if (stripos($item["name"], "percentage") !== false) {
      $itemid[] = $item["itemid"];
      $itemname[] = $item["name"];
      $itemerror[] = $item["error"];
      $count++;
      // }
    }
  } else {
    print '<div class="col-sm-12 text-danger"><p>No disk found</p></div>';
    return;
  }

  //store disk values and clock
  $temp = array();
  $temp2 = array();

  //store respective itemid values
  $dbspace = array();

  //fetch data
  $count2 = 0;
  $dbseries = "";
  $length = count($itemid);
  for ($i = 0; $i < $length; $i++) {
    $params = array(
      "output" => array("lastvalue", "name"),
      "itemids" => $itemid[$i],
    );
    //call api method
    //store in disk data array
    $result = $zbx->call('item.get', $params);
    foreach ($result as $db) {
      $dbname = substr($db["name"], 0, -17);
      //if current value is 0, then used value also 0
      if ($db["lastvalue"] == 0) {
        $usedvalue = 0;
      } else {
        $usedvalue = $db["lastvalue"];
      }
    }
    $temp = array("database" => "$dbname", "value" => "$usedvalue");
    $dbspace[$i] = $temp;
    $count2 = 0;
  }
  
  //sort by value
  usort($dbspace, function($a, $b){
    return $a['value'] < $b['value'];
  });

  $temp2 = json_encode($dbspace);

  //print $temp2;


  print '<!-- Chart code -->
  <script>
    am4core.options.autoDispose = true;
    am4core.useTheme(am4themes_animated);
    am4core.useTheme(am4themes_frozen);

  
    // Create chart instance
    var datachart = am4core.create("datachartdiv", am4charts.PieChart);

    // Add data
    datachart.numberFormatter.numberFormat = "#.##b";
    datachart.data = ' . $temp2 . ';

    // Add and configure Series
    var pieSeries = datachart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "database";
    pieSeries.labels.template.text = "{value}"; 
    pieSeries.slices.template.tooltipText = "{category} : {value}";

    // Disable ticks and labels
    pieSeries.labels.template.disabled = true;
    pieSeries.ticks.template.disabled = true;

    // Add Legend
    datachart.legend = new am4charts.Legend();
    datachart.legend.position = "right";
    datachart.legend.valueLabels.template.text = "{value}"




    datachart.legend.maxHeight = undefined;
    datachart.legend.width = 350;
    datachart.legend.scrollable = true;   
  </script>';

  print '<div id="datachartdiv" style="width: auto; height: 400px;"></div>';


  ?>

</body>

</html>