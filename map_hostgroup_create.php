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
            Map ( Host Group )
            <small>Create</small>
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
              <div class="box box-primary">
                <div class="box-body">
                  <form name="mapForm" method="GET" action="map_hostgroup_add.php">
                   <table class="table table-bordered table-striped">
                    <td>Choose Host Group</td>
                    <td>
                      <select class="form-control" name="groupid" style="width: auto;">
                      <?php
                      $params = array(
                        "output" => "extend"
                      );
              
                      $result = $zbx->call('hostgroup.get',$params);
                      foreach ($result as $hostgroup) {
                        $hostgroup_name = $hostgroup["name"];

                        $params = array (
                          "output" => "extend"
                        );

                        $maps = $zbx->call('map.get', $params);

                        $group_map_exists = false;

                        foreach ($maps as $map) {
                            if ($map['name'] == $hostgroup_name) {
                                $group_map_exists = true;
                            }
                        }
                
                        if (!$group_map_exists) {
          
                        print '<option value="'.$hostgroup["groupid"].'">'.$hostgroup["name"].'</option>';
                      }
                    }
                      ?>
                      </select>
                    </td>
                  </tr>
                  </table>
                </div><!-- /.box-body -->
                <div class="box-footer">
                  <button class='btn bg-green margin' type="submit">Create Map</button>
                </div>
                </form>
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
          
        </section><!-- /.content -->
      </div><!-- ./wrapper -->

      <?php include("footer.php"); ?>

<?php
//$zbx->logout();//logout from zabbix API
?>

  </body>
</html>