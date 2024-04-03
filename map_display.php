<?php
  
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
<!DOCTYPE html>
<html>
  <?php include("head.php"); ?>
  <body class="skin-blue">
    <div class="wrapper">
      
    <?php include("header.php"); ?>
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
           Map
            <small>Display</small>
          </h1>
          <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Users</li>
          </ol> -->
        </section>

        <!-- Main content -->
        <section class="content">

        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs (Pulled to the right) -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs pull-left">
                        <li class="active"><a data-target="#tab_1-1" data-toggle="tab"><i class="fa fa-filter"></i> Filter</a></li>
                    </ul>
                    <div class="tab-content">
                    <!-- /.tab-pane -->
                    <div class="tab-pane active" id="tab_1-1">
                        <div class="row">
                          <div class="col-md-12">
                            <table class="table table-bordered table-hover">
                                <tr>
                                    <td>Map</td>
                                    <td>
                                        <!-- select group -->
                                        <div class="form-group">
                                        <select class="form-control" onchange="location = this.value;" style="width: auto;">
                                            <option><?php echo $mapname; ?></option>
                                            <?php
                                            $params = array(
                                                "output" => "extend"
                                            );
                                            $result = $zbx->call('map.get', $params);
                                            foreach ($result as $map) {
                                                if ($mapid == $map['sysmapid']) {
                                                    continue;
                                                }else {
                                                    print "<option value='map_display.php?mapid=" . $map['sysmapid'] . "'>".$map['name']."</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                          </div>
                        </div>
                    </div><!-- /.tab-pane -->
                  </div><!-- /.tab-content -->
                </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
        </div> <!-- /.row -->

          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title"><?php echo $mapname; ?></h3>
                  <div class="box-tools pull-right">
                    <a href="map_edit.php?mapid=<?php echo $mapid; ?>"><button class='btn btn-info margin'>Edit Map</button></a>
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <br>
                  <div id="map">
                    <!-- Loading Gif -->
                    <div class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div> 
                  </div>
                  <script>
                    var mapid = <?php echo $mapid;?>;

                    loadMap();
                    function loadMap(){
                      map_xhr = $.ajax({
                          url: "map/get_map.php?mapid=" + mapid,
                          success: function(result){
                              $("#map").html(result);
                          }
                      });
                    }
                  </script>
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