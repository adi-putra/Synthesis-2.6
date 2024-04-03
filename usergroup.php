<?php
  
  include "session.php";

  //only allow admin to see user list
  if ($zabUtype !== "3") {
    //display error message box
    print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';
  }

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
            User Group List
            <small>User Group</small>
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
                <div class="box-body">
                  <a href="usergroup_create.php"><button class='btn btn-block btn-primary btn-sm' style="float: left; width: auto;">Create User Group</button></a><br><br><br>
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <!-- <th>Frontend Access</th>
                        <th style="text-align: center;">Debug Mode</th> -->
                        <th style="text-align: center;">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                      $params = array(
                        "output" => array("usrgrpid", "name", "gui_access", "users_status", "debug_mode")
                      );

                      $result = $zbx->call('usergroup.get',$params);
                      //count id from 1 and list all details from result
                      $id = 1;
                      foreach ($result as $usrgrp) {
                        //get user group id and name
                        $usrgrpid = $usrgrp["usrgrpid"];

                        if (stripos($usrgrp["name"], "Zabbix") !== false) {
                          continue;
                        }

                        $name = str_replace("Zabbix","Synthesis",$usrgrp["name"]);

                        //check if gui access enable or not
                        if ($usrgrp["gui_access"] == 0) {
                          $gui_access = "System default";
                        }
                        else if ($usrgrp["gui_access"] == 1) {
                          $gui_access = "Internal";                        
                        }
                        else if ($usrgrp["gui_access"] == 2) {
                          $gui_access = "LDAP authentication";                        
                        }
                        else if ($usrgrp["gui_access"] == 3) {
                          $gui_access = "Disabled";                        
                        }

                        //check if debug mode enable or not
                        if ($usrgrp["debug_mode"] == 0) {
                          $debug_mode = "<button class='btn btn-block btn-danger btn-sm'>Disabled</button>";
                        }
                        else {
                          $debug_mode = "<button class='btn btn-block btn-success btn-sm'>Enabled</button>";
                        }

                        //check if user group enable or not
                        if ($usrgrp["users_status"] == 0) {
                          $users_status = "<button class='btn btn-block btn-success btn-sm'>Enabled</button>";
                        }
                        else {
                          $users_status = "<button class='btn btn-block btn-danger btn-sm'>Disabled</button>";
                        }

                        print "<tr>
                              <td>$id</td>
                              <td><a href='usergroup_settings.php?usrgrpid=".$usrgrpid."'>$name</a></td>
                              <td>$users_status</td>
                              </tr>";

                        $id = $id + 1;
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

    <!-- page script -->
    <script type="text/javascript">
      $(function () {
        $("#example1").dataTable();
        $('#example2').dataTable({
          "bPaginate": true,
          "bLengthChange": false,
          "bFilter": false,
          "bSort": true,
          "bInfo": true,
          "bAutoWidth": false
        });
      });
    </script>

<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>