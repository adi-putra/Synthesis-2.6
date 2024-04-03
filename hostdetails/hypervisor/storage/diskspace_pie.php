<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>diskspacePie</title>
</head>

<body>

<?php

  include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

  $hostid = $_GET["hostid"] ?? "10084";
  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();

  //display time format
  $diff = $timetill - $timefrom;


  $params = array(
    "output" => array("itemid", "name", "lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => array("VMware: Total disk space on C:")), //seach id contains specific word
  );
  //call api method
  $result = $zbx->call('item.get', $params);

  $params = array(
    "output" => array("itemid", "name", "lastvalue"),
    "hostids" => $hostid,
    "search" => array("name" => array("VMware: Used disk space on C:")), //seach id contains specific word
  );
  //call api method
  $result2 = $zbx->call('item.get', $params);

  if($result && $result2){

    $Totaldiskspace = round($result[0]['lastvalue']/1024/1024/1024, 1);
    $Useddiskspace = round($result2[0]['lastvalue']/1024/1024/1024, 1);
   // $Useddiskspace = round(($Useddiskspace/$Totaldiskspace)*100,2);
    $freediskspace = $Totaldiskspace - $Useddiskspace;

  }

  

?>

  <!-- Resources -->
  <script src="//cdn.amcharts.com/lib/4/core.js"></script>
  <script src="//cdn.amcharts.com/lib/4/charts.js"></script>

  <div id="diskspace_pie" style="width: 100%; height: 400px;"></div>

  <script>
    // Create chart instance
    var DiskSpace_chart = am4core.create("diskspace_pie", am4charts.PieChart);

    var title = DiskSpace_chart.titles.create();
    title.text = "Disk Space Pie Chart";
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = "center";

    // Add data
    DiskSpace_chart.data = [{
      "name": "Used Disk Space",
      "space": <?=  $Useddiskspace ?>
    },{

      "name": "Free Disk Space",
      "space": <?=  $freediskspace  ?>

    }];

    // Add and configure Series
    var pieSeries = DiskSpace_chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "space";
    pieSeries.dataFields.category = "name";

    
    // add legend
    DiskSpace_chart.legend = new am4charts.Legend();

  </script>

  
</body>

</html>