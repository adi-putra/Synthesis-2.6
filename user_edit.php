<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//Author: Adiputra
  
  include "session.php";

  //only allow admin to see user list
  if ($zabUtype !== "3") {
    //display error message box
    print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';
  }

  $userid = $_GET["userid"];

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();

?>
<!DOCTYPE html>
<html>
  
<?php include("head.php"); ?>

  <body class="skin-blue">
    <div class="wrapper">
      
    <?php include('header.php'); ?>
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            User Configuration
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

        <?php
        $params = array(
        "output" => array("userid", "username", "roleid"),
        "userids" => $userid,
        "selectUsrgrps" => array("usrgrpid", "name", "users_status")
        );

        $result = $zbx->call('user.get',$params);

        foreach ($result as $row) {
          $username = $row["username"];
          $userrole = $row["roleid"];
          foreach ($row["usrgrps"] as $usrgrp) {
            $usrgrpid1 = $usrgrp["usrgrpid"];
            $usrgrpname1 = str_replace("Zabbix", "Synthesis", $usrgrp["name"]);
          }
        }

        // check if current user is equal to this user
        if ($zabUser == $username) {
          $thisisuser = 1;
        }
        else {
          $thisisuser = 0;
        }
        ?>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
           <form name="userForm" method="get" action="user_update.php" onsubmit="return validateForm()">
           <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#tab_user" data-toggle="tab">User</a></li>
                  <li><a href="#tab_media" data-toggle="tab">Email</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_user">
                    <input type="hidden" name="userid" value="<?php echo $userid;?>"/>
                    <input type="hidden" name="thisisuser" value="<?php echo $thisisuser;?>"/>
                    <table class="table table-bordered table-striped">
                    <tr>
                      <td>Username</td>
                      <td><input style="width: 50%;" class="form-control" type="text" name="username" value="<?php echo $username; ?>" required></td>
                    </tr>
                    <?php
                    // check if current user now is equal to this user
                    if ($thisisuser == 1) {
                      $disabled_userrole_select = "display:none;";
                    }
                    else {
                      $disabled_userrole_select = "display:all;";
                    }
                    ?>
                    <tr style="<?php echo $disabled_userrole_select; ?>">
                      <td>User Type</td>
                      <td>
                        <select name="userrole" class="form-control" style="width: auto;" required>
                          <?php
                          if ($userrole == "1") {
                            print '<option value="'.$userrole.'">User (default)</option>';
                          }
                          else if ($userrole == "2" || $userrole == "3") {
                            print '<option value="'.$userrole.'">Admin</option>';
                          }
                          // else if ($userrole == "3") {
                          //   print '<option value="'.$userrole.'">Admin</option>';
                          // }
                          ?>
                          <option disabled>-------------------------</option>
                          <option value="1">User (default)</option>
                          <option value="3">Admin</option>
                        </select>
                        <hr>
                        <p><i>User (default): Only have read access to monitor host's info, performance, issues, etc.</i></p>
                        <p><i>Admin: Read access together with ability to add users, license and other setups.</i></p>
                      </td>
                    </tr>
                    <tr>
                      <td>Groups</td>
                      <td>
                        <select name="usrgrpid" class="form-control" style="width: auto;" required>
                          <option value=<?php echo $usrgrpid1;?>><?php echo $usrgrpname1;?></option>
                          <?php
                          $params = array(
                              "output" => array("usrgrpid","name")
                            );

                            $result = $zbx->call('usergroup.get',$params);
                            foreach ($result as $row) {
                              if ($row["usrgrpid"] != $usrgrpid1) {
                                $usrgrpid = $row["usrgrpid"];
                                $usrgrpname = $row["name"];
                                if (stripos($usrgrpname, "Zabbix") !== false) {
                                  continue;
                                }

                                print '<option value="'.$usrgrpid.'">'.$usrgrpname.'</option>';
                              }
                            }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Password</td>
                      <td>
                        <button id="chgpass_btn" type="button" class='btn btn-default margin'>Change password</button>
                        <div style="display: none;" id="chgpass_div">
                          <table class="table table-bordered table-hover">
                          <tr>
                            <td>Password</td>
                            <td><input style="width: 50%;" class="form-control" type="password" id="userpassword" name="userpassword" onkeyup="check();"></td>
                          </tr>
                          <tr>
                            <td>Password again</td>
                            <td><input style="width: 50%;" class="form-control" type="password" id="confuserpassword" name="confuserpassword" onkeyup="check();"><span id='message'></span></td>
                          </tr>
                          </table>
                        </div>
                      </td>
                    </tr>
                    </table>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_media">
                    <table id="usermedia_table" class="table table-bordered table-striped">
                      <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
                      <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    <?php
                    $params = array(
                    "userids" => $userid,
                    "selectMedias" => array("mediaid", "sendto", "active")
                    );

                    $result = $zbx->call('user.get',$params);

                    // print "<pre>";
                    // print json_encode($result);
                    // print "</pre>";

                    foreach ($result as $row) {
                      foreach ($row["medias"] as $media) {

                        if (is_array($media["sendto"])) {
                          $media_email = $media["sendto"][0];
                        }
                        else {
                          $media_email = $media["sendto"];
                        }

                        // foreach ($media["sendto"] as $email) {
                          print '<tr>';
                          print '<td><input type="email" class="form-control" name="usermedia_email[]" value="'.$media_email.'" required /></td>';
                          print '<td>
                                  <select class="form-control" name="usermedia_active[]">';

                          if ($media["active"] == 0) {
                            print '<option value="0" selected>Enabled</option>
                                  <option value="1">Disabled</option>
                                  </select>
                                  </td>
                                  <td><button class="btn btn-flat btn-danger margin" type="button" onclick="deleteEmail(this);">Delete</button></td>
                                  </tr>';
                          }
                          else {
                            print '<option value="0">Enabled</option>
                                  <option value="1" selected>Disabled</option>
                                  </select>
                                  </td>
                                  <td><button class="btn btn-flat btn-danger margin" type="button" onclick="deleteEmail(this);">Delete</button></td>
                                  </tr>';
                          }
                        // }
                      }
                    }
                    ?>
                    </table>
                    <!-- <button class='btn btn-default margin' type="reset">Reset</button> -->
                    <button class='btn btn-primary margin' type="button" onclick="addEmail();" >Add +</button>
                    <!-- <button class='btn btn-success margin' type="submit">Update</button> -->
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- nav-tabs-custom -->
              <button class='btn btn-default margin' type="reset">Reset</button>
              <?php
              // check if current user now is equal to this user
              if ($thisisuser == 0) {
                print '<a href="user_delete.php?userid='.$userid.'" onclick="if (!confirm("Confirm to delete the user?")) { return false }"><button type="button" class="btn bg-red margin">Delete</button></a>';
              }
              ?>
              <button class='btn bg-green margin' type="submit" onclick="if (!confirm('Confirm update user?')) { return false }">Update</button>
              </form>
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

<script>
//toggle password div
$(function() {
  $('#chgpass_btn').on('click', function() {
    $('#chgpass_div').toggle();
    $('#userpassword').val("");
    $('#confuserpassword').val("");
    document.getElementById('message').innerHTML = '';
  });
});

function validateForm() {
  var password = document.forms["userForm"]["userpassword"].value;
  var confpassword = document.forms["userForm"]["confuserpassword"].value;
  if (password !== confpassword) {
    alert("Password not match!");
    return false;
  }
}
</script>

  <script>
  function deleteEmail(x) {
    var cellAndRow = $(x).parents('td,tr');

    var cellIndex = cellAndRow[0].cellIndex
    var rowIndex = cellAndRow[1].rowIndex;

    document.getElementById("usermedia_table").deleteRow(rowIndex);
  }

  function addEmail() {
    // Find a <table> element with id="myTable":
    var table = document.getElementById("usermedia_table");

    var rowCount = $('#usermedia_table tr').length;

    var row = table.insertRow(rowCount);

    // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);

    // Add some text to the new cells:
    cell1.innerHTML = '<input type="email" class="form-control" name="usermedia_email[]" value="" required/>';
    cell2.innerHTML = '<select class="form-control" name="usermedia_active[]">' + 
                        '<option value="0">Enabled</option>' +
                        '<option value="1">Disabled</option>' +
                      '</select>';
    cell3.innerHTML = '<button class="btn btn-flat btn-danger margin" type="button" onclick="deleteEmail(this);">Delete</button>';
  }
  </script>