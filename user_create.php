<?php

  //Author: Adiputra
  
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
            Create User
            <small>form</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">User</li>
          </ol>
        </section>

        <!-- To check if password enter correct for both fields -->
        <script type="text/javascript">
          var check = function() {
            if (document.getElementById('userpassword').value == document.getElementById('confuserpassword').value) {
              if (document.getElementById('userpassword').value == "" && document.getElementById('confuserpassword').value == "") {
                document.getElementById('message').innerHTML = '';
              }
              else {
                document.getElementById('message').style.color = 'green';
                document.getElementById('message').innerHTML = 'matching';
              }
            }
            else {
              document.getElementById('message').style.color = 'red';
              document.getElementById('message').innerHTML = 'not matching';
            }
          }
        </script>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box box-primary">
                <div class="box-body">
                  <form name="userForm" method="get" action="user_add.php" onsubmit="return validateForm()">
                   <table class="table table-bordered table-striped">
                   <tr>
                    <td>Username</td>
                    <td><input style="width: 50%;" class="form-control" type="text" name="username" required></td>
                  </tr>
                  <tr>
                     <td>Password</td>
                     <td><input style="width: 50%;" class="form-control" type="password" id="userpassword" name="userpassword" required onkeyup="check();"></td>
                  </tr>
                  <tr>
                     <td>Password again</td>
                     <td><input style="width: 50%;" class="form-control" type="password" id="confuserpassword" name="confuserpassword" required onkeyup="check();"><span id='message'></span></td>
                  </tr>
                  <tr>
                    <td>User Role</td>
                    <td>
                      <select class="form-control" name="userrole" style="width: auto;">
                        <option value="1">User</option>
                        <option value="3">Admin</option>
                      </select></td>
                  </tr>
                  <tr>
                    <td>User Group</td>
                    <td>
                      <select class="form-control" name="usrgrpid" style="width: auto;">
                      <?php
                      $params = array(
                        "output" => "extend"
                      );
              
                      $result = $zbx->call('usergroup.get',$params);
                      foreach ($result as $usrgrp) {
                        
                        if (stripos($usrgrp["name"], "Zabbix") !== false) {
                          continue;
                        }

                        print '<option value="'.$usrgrp["usrgrpid"].'">'.$usrgrp["name"].'</option>';
                      }
                      ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                     <button class='btn bg-green margin' type="submit">Submit</button>
                    <button class='btn bg-blue margin' type="reset">Reset</button></td>
                  </tr>
                  </table>
                  </form>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      <script>
      function validateForm() {
        var password = document.forms["userForm"]["userpassword"].value;
        var confpassword = document.forms["userForm"]["confuserpassword"].value;
        if (password !== confpassword) {
          alert("Password not match!");
          return false;
        }
      }
      </script>
      </div><!-- ./wrapper -->
      
      <?php include("footer.php"); ?>

<?php
//$zbx->logout();//logout from zabbix API
?>

  </body>
</html>