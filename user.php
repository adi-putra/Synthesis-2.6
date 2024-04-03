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
            User
            <small>User List</small>
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
                  <a href="user_create.php"><button class='btn btn-block btn-primary btn-sm' style="float: left; width: auto;">Create User</button></a><br><br><br>
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>User type</th>
                        <th>Groups</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                      $params = array(
                        "output" => array("userid", "username", "name", "surname", "type"),
                        "selectUsrgrps" => array("usrgrpid", "name", "users_status"),
                        "selectRole" => "extend"
                      );

                      $result = $zbx->call('user.get',$params);
                      //count id from 1 and list all details from result
                      $id = 1;
                      foreach ($result as $v) {

                        $userid = $v["userid"];

                        if ($v["username"] == "Admin" || $v["username"] == "synthesis") {
                          continue;
                        }
                        // else if ($v["username"] == "synthesis") {
                        //   if ($zabUser == $v["username"]) {
                        //     $userusername = $v["username"];
                        //   }
                        //   else {
                        //     continue;
                        //   }
                        // }
                        else {
                          if ($v['username'] !== "") {
                            $userusername = str_replace("Zabbix","Synthesis",$v["username"]);
                          }
                          else {
                            $userusername = "-";
                          }
                        }

                        if ($v['name'] !== "") {
                          $username = str_replace("Zabbix","Synthesis",$v["name"]);
                        }
                        else {
                          $username = "-";
                        }
                        
                        if ($v['surname'] !== "") {
                          $usersurname = str_replace("Zabbix","Synthesis",$v["surname"]);
                        }
                        else {
                          $usersurname = "-";
                        }

                        if ($v['role']["roleid"] == "1") {
                          $usertype = "User";
                        }
                        else if ($v['role']['roleid'] == "2" || $v['role']['roleid'] == "3") {
                          $usertype = "Admin";
                        }
                        // else {
                        //   $usertype = "Super Admin";
                        // }
                        
                        foreach ($v["usrgrps"] as $usrgrp) {
                          if ($usrgrp['name'] !== "") {
                            $usrgrpname = str_replace("Zabbix","Synthesis",$usrgrp["name"]);
                          }
                          else {
                            $username = "-";
                          }

                          if ($usrgrp['users_status'] == "0") {
                            $usrgrpstatus = "<button class='btn btn-block btn-success btn-sm'>Enabled</button>";
                          }
                          else {
                            $usrgrpstatus = "<button class='btn btn-block btn-danger btn-sm'>Disabled</button>";
                          }
                        }
                  
                        print "<tr>
                          <td>$id</td>
                          <td><a href='user_edit.php?userid=$userid'>$userusername</a></td>
                          <td>$usertype</td>
                          <td>$usrgrpname</td>
                          <td>$usrgrpstatus</td>";

                        $id = $id + 1;
                      } 
                      ?>                 
                      <script>
                      $(document).ready(function(){
                      $('[data-toggle="tooltip"]').tooltip();   
                      });
                      </script>
                     </tr>
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