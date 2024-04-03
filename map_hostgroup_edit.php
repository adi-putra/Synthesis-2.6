<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
  
include "session.php";

$timefrom = $_GET['timefrom'] ?? strtotime('today');
$timetill = $_GET['timetill'] ?? time();

$mapid = $_GET['mapid'];

// get map name
$params = array(
    "output" => "extend",
    "sysmapids" => $mapid,
);
$result = $zbx->call('map.get', $params);
foreach ($result as $map) {
    $mapname = $map['name'];
}

?>

<script>
    var mapids = <?php echo $mapid;?>;
</script>
<!DOCTYPE html>
<html>
  <?php include("head.php"); ?>

  <body class="skin-blue" onload="hide_sidebar();">

    <div class="wrapper">
      
    <?php include("header.php"); ?>
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
           Edit Hostgroup Map
            <!-- <small>User List</small> -->
          </h1>
          <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Users</li>
          </ol> -->
        </section>

        <!-- Main content -->
        <section class="content">

          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><?php echo $mapname; ?></h3>
                </div>
                <div class="box-body">
                    <iframe id="map_iframe" src="/synconfig/sysmap.php?sysmapid=<?php echo $mapid; ?>" width="100%" height="100%" style="border:1px solid black;" onLoad="iframe_onload();"></iframe>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->


        </section><!-- /.content -->
      </div><!-- ./wrapper -->
      
      <?php include("footer.php"); ?>


<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>

<script>

var mapid = <?php echo $mapid; ?>;

function hide_sidebar() {
  var map_iframe = document.getElementById("map_iframe");
  var elem = map_iframe.contentWindow.document.querySelector(".sidebar");
  elem.style.display = "none";
}

function iframe_onload() {

  var map_iframe = document.getElementById("map_iframe");

  var iframe_url = map_iframe.contentWindow.location.href
  // iframe_url = iframe_url.slice(iframe_url.indexOf("sys"));
  var check = iframe_url.indexOf("?");
  // alert(check);


  if (check == -1) {
    map_iframe.style.display = "none";
    location.assign("map_display.php?mapid=" + mapid);
  }
}
</script>