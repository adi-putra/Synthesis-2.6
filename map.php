<?php
  
  include "session.php";

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();

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
            <small>List</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Map</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                  <a href="map_hostgroup_create.php"><button class='btn btn-block btn-primary btn-sm margin' style="float: left; width: auto;">Create Map by Host Group</button></a>
                  <a href="map_user_create.php"><button class='btn btn-block bg-purple btn-sm margin' style="float: left; width: auto;">Create Map by User</button></a><br><br><br>
                  <table id="map_table" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>No.</th>
                        <th>Map Name</th>
                        <th>Width</th>
                        <th>Height</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $id = 1;
                        $params = array(
                            "output" => "extend"
                        );
                        $result = $zbx->call('map.get', $params);
                        foreach ($result as $map) {

                            $map_id = $map["sysmapid"];
                            $map_name = $map["name"];
                            $map_width = $map["width"];
                            $map_height = $map["height"];

                            print "<tr>";
                            print "<td>".$id."</td>";
                            print "<td><a href='map_display.php?mapid=".$map_id."'>".$map_name."</a></td>";
                            print "<td>".$map_width."</td>";
                            print "<td>".$map_height."</td>";
                            print '<td><button class="btn btn-block btn-danger" onclick="delete_map('.$map_id.')">Delete</button></td>';
                            print "</tr>";

                            $id++;
                        }
                        ?>
                    </tbody>
                  </table>
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
    $("#map_table").DataTable();

    function delete_map(mapid) {
      if (confirm("Confirm delete this map?")) {
        location.assign("map_delete.php?mapid=" + mapid);
      }
      else {
        return false;
      }
    }
</script>