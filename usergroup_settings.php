<?php
  
  include "session.php";

  //only allow admin to see user list
  if ($zabUtype !== "3") {
    //display error message box
    print '<script>alert("You do not have access to this page!");</script>';
    //go to login page
    print '<script>window.location.assign("dashboard.php");</script>';
  }

  else {

  //user group id
  $usrgrpid = $_GET['usrgrpid'];

  //get user group name
  $params = array(
    "output" => array("name"),
    "usrgrpids" => $usrgrpid
  );

  $result = $zbx->call('usergroup.get',$params);
  foreach ($result as $usrgrp) {
    $usrgrpname = str_replace("Zabbix","Synthesis",$usrgrp["name"]);
  }

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();

}
?>
<!DOCTYPE html>
<html>
  <head>

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
            <?php echo $usrgrpname; ?>
            <small>User Group</small>
          </h1>
          <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Users</li>
          </ol> -->
        </section>

        <!-- Main content -->
        <section class="content">

         <?php
         // start with user group api call
         $params = array(
          "output" => array("usrgrpid", "name", "gui_access", "users_status", "debug_mode"),
          "usrgrpids" => $usrgrpid
          );

          $result = $zbx->call('usergroup.get',$params);
          foreach ($result as $usrgrp) {
            $getName = str_replace("Zabbix","Synthesis",$usrgrp["name"]);
            $getFA = $usrgrp["gui_access"];
            $getStatus = $usrgrp["users_status"];
            $getDebug = $usrgrp["debug_mode"];
          }
         ?> 

          <!-- User Group Tabs -->
          <div class="row">
            <div class="col-xs-12">
              <!-- Custom Tabs -->
              <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#usergroup" data-toggle="tab">User Group</a></li>
                  <li><a href="#permission" data-toggle="tab">Permission</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="usergroup">
                    <div class="row">
                     <div class="col-xs-12">
                        <div class="box box-primary">
                          <div class="box-body">
                            <form name="userForm" method="post" action="usergroup_update.php">
                              <input type="hidden" name="usrgrpid" value="<?php echo $usrgrpid;?>">
                             <table class="table table-bordered table-striped">
                             <tr>
                              <td>Group Name</td>
                              <td><input style="width: 50%;" class="form-control" type="text" name="usrgrpName" value='<?php echo $getName; ?>' required></td>
                            </tr>
                            <!-- <tr>
                               <td>Frontend Access</td>
                               <td>
                                 <select name="usrgrpFA" class="form-control" style="width: auto;" required>
                                  <?php
                                  //display current frontend access status first
                                  if ($getFA == 0) {
                                    echo "<option value='0' selected>System Default</option>
                                          <option value='1'>Internal</option>
                                          <option value='2'>LDAP</option>
                                          <option value='3'>Disable</option>";
                                  }
                                  else if ($getFA == 1) {
                                    echo "<option value='0'>System Default</option>
                                          <option value='1' selected>Internal</option>
                                          <option value='2'>LDAP</option>
                                          <option value='3'>Disable</option>";
                                  }
                                  else if ($getFA == 2) {
                                    echo "<option value='0'>System Default</option>
                                          <option value='1'>Internal</option>
                                          <option value='2' selected>LDAP</option>
                                          <option value='3'>Disable</option>";
                                  }
                                  else if ($getFA == 3) {
                                    echo "<option value='0'>System Default</option>
                                          <option value='1'>Internal</option>
                                          <option value='2'>LDAP</option>
                                          <option value='3' selected>Disable</option>";
                                  }
                                  ?>
                                </select>
                               </td>
                            </tr> -->
                            <tr>
                               <td>Enabled</td>
                               <td>
                                 <?php
                                 if ($getStatus == 0) {
                                   echo "<input type='checkbox' name='usrgrpStatus' class='flat-red' value='0' checked>";
                                 }
                                 else if ($getStatus == 1) {
                                    echo "<input type='checkbox' name='usrgrpStatus' class='flat-red' value='0'>";
                                 }
                                 ?>
                               </td>
                            </tr>
                            <!-- <tr>
                              <td>Debug Mode</td>
                              <td>
                                <?php
                                 if ($getDebug == 0) {
                                   echo "<input type='checkbox' name='usrgrpDebug' class='flat-red' value='1'>";
                                 }
                                 else if ($getDebug == 1) {
                                    echo "<input type='checkbox' name='usrgrpDebug' class='flat-red' value='1' checked>";
                                 }
                                 ?>
                              </td>
                            </tr> -->
                            </table>
                          </div><!-- /.box-body -->
                          <button class='btn bg-green margin' type="submit">Update</button>
                          <button class='btn btn-default margin' type="reset">Reset</button>
                          <a href="usergroup_delete.php?usrgrpid=<?php echo $usrgrpid; ?>" onclick="if (!confirm('Confirm to delete the user group?')) { return false }"><button type="button" class='btn bg-red margin'>Delete User Group</button></a>
                          </form>
                        </div><!-- /.box -->
                      </div><!-- /.col -->
                    </div><!-- /.row -->    
                  </div><!-- /.tab-pane -->

                  <div class="tab-pane" id="permission">
                    <!-- List of Permissions available -->
                    <div class="row">
                     <div class="col-xs-12">
                        <div class="box box-primary">
                          <div class="box-header">
                            <h3 class="box-title">Available Permissions</h3>
                          </div><!-- /.box-header -->
                          <div class="box-body">
                            <form action="usergroup_perm_upd.php" method="post">
                            <input type="hidden" name="usrgrpid" value="<?php echo $usrgrpid;?>">
                            <table id="rightsTable" class="table table-bordered table-striped">
                              <thead>
                                <tr>
                                  <th>ID</th>
                                  <th>Host Groups</th>
                                  <th>Permissions <i style="color: red;">(Select "None" to delete permission)</i></th>
                                </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  // create array to store input values for user
                                  $get_rights = array();

                                  //create array for storing group ids
                                  $groupids = array();

                                  //declare counter for storing array values
                                  $count = 0;

                                  // declare table row
                                  $id = 1;

                                // start with user group api call
                                $params = array(
                                  "output" => array("usrgrpid", "name", "gui_access", "users_status", "debug_mode"),
                                  "usrgrpids" => $usrgrpid,
                                  "selectRights" => array("permission", "id")
                                );

                                $result = $zbx->call('usergroup.get',$params);
                                foreach ($result as $usrgrp) {

                                  //get permissions and host group name
                                  foreach ($usrgrp["rights"] as $rights) {

                                    $params = array(
                                      "output" => array("name"),
                                      "groupids" => $rights["id"]
                                    );

                                    $result = $zbx->call('hostgroup.get',$params);
                                    foreach ($result as $group) {
                                      $groupname = str_replace("Zabbix","Synthesis",$group["name"]);

                                      //insert host group id in groupids array
                                      array_push($groupids, $rights["id"]);

                                      //store permission in array
                                      $get_rights[$count] = array("permission" => $rights["permission"], "id" => $rights["id"]);
                                      
                                      //set permission value
                                      if ($rights["permission"] == 0) {
                                        //display checkboxes and check its permission
                                        $chkbox = '<div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="3">Read-Write</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="2">Read-Only</label>
                                                    <label class="btn btn-primary active"><input type="radio" name="permission['.$count.']" value="0" checked>Deny</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="None">None</label>
                                                  </div>';
                                        
                                      }

                                      else if ($rights["permission"] == 2) {
                                        //display checkboxes and check its permission
                                        $chkbox = '<div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="3">Read-Write</label>
                                                    <label class="btn btn-primary active"><input type="radio" name="permission['.$count.']" value="2" checked>Read-Only</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="0">Deny</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="None">None</label>
                                                  </div>';
                                        
                                      }

                                      else if ($rights["permission"] == 3) {
                                        //display checkboxes and check its permission
                                        $chkbox = '<div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-primary active"><input type="radio" name="permission['.$count.']" value="3" checked>Read-Write</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="2">Read-Only</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="0">Deny</label>
                                                    <label class="btn btn-primary"><input type="radio" name="permission['.$count.']" value="None">None</label>
                                                  </div>';
                                        
                                      }

                                      print "<tr>
                                              <td>$id</td>
                                              <td>$groupname</td>
                                              <td>$chkbox</td>
                                            </tr>";

                                      $count++;
                                      $id++;
                                    }
                                  }
                                }
                                ?>
                              </tbody>
                            </table>
                            <button class='btn bg-green margin' type="submit">Update</button>
                            </form>
                          </div><!-- /.box-body -->
                        </div><!-- /.box -->
                      </div><!-- /.col -->
                    </div><!-- /.row -->


                  <!-- Add Permissions form -->
                    <div class="row">
                     <div class="col-xs-12">
                        <div class="box box-primary">
                          <div class="box-header">
                            <h3 class="box-title">Add Permissions</h3>
                          </div><!-- /.box-header -->
                          <div class="box-body">
                            <form action="usergroup_perm_add.php" method="post">
                              <input type="hidden" name="usrgrpid" value="<?php echo $usrgrpid;?>">
                            <table id="addperm" class="table table-bordered table-striped">
                              <tr>
                                <th>Host Groups</th>
                                <th>Permissions</th>
                              </tr>
                              <tr>
                                <td>
                                  <!-- Host Group dropdown -->
                                  <div class="form-group">
                                    <select class="form-control" name="groupid[]" style="width: auto;" required>
                                      <option value="" disabled selected>Select Host Group</option>
                                    <?php
                                    $params = array(
                                      "output" => array("groupid", "name")
                                    );
                                    //call api
                                    $result = $zbx->call('hostgroup.get',$params);

                                    //display select dropdown with host groupname
                                    foreach ($result as $group) {
                                      $groupid = $group["groupid"];
                                      $groupname = $group['name'];

                                      if (in_array($groupid, $groupids)) {
                                        continue;
                                      }
                                      else {
                                        print "<option value='".$groupid."'>$groupname</option>";
                                      }
                                    }
                                    ?>   
                                    </select>
                                  </div>
                                </td>
                                <td>
                                  <!-- Permission radio -->
                                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-primary"><input type="radio" name="permission[0]" value="3">Read-Write</label>
                                    <label class="btn btn-primary"><input type="radio" name="permission[0]" value="2">Read-Only</label>
                                    <label class="btn btn-primary"><input type="radio" name="permission[0]" value="0">Deny</label>
                                    <label class="btn btn-primary active"><input type="radio" name="permission[0]" value="None" checked>None</label>
                                  </div>
                                </td>
                              </tr>                     
                            </table>
                            <button class='btn bg-green margin' type="submit">Add</button>
                            <button class='btn bg-blue margin' type="button" onclick="addRowPerm();">Add Row +</button>
                            </form>
                          </div><!-- /.box-body -->
                        </div><!-- /.box -->
                      </div><!-- /.col -->
                    </div><!-- /.row -->
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
          </div> <!-- /.row -->

          <script>
          var lastrow = 2;
          var arrayCount = 1;
          function addRowPerm() {
            var table = document.getElementById("addperm");
            var row = table.insertRow(lastrow);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            cell1.innerHTML = table.rows[1].cells[0].innerHTML;
            cell2.innerHTML = '<div class="btn-group btn-group-toggle" data-toggle="buttons"><label class="btn btn-primary"><input type="radio" name="permission[' + arrayCount + ']" value="3">Read-Write</label><label class="btn btn-primary"><input type="radio" name="permission[' + arrayCount + ']" value="2">Read-Only</label><label class="btn btn-primary"><input type="radio" name="permission[' + arrayCount + ']" value="0">Deny</label><label class="btn btn-primary active"><input type="radio" name="permission[' + arrayCount + ']" value="None" checked>None</label></div>';
            lastrow = lastrow + 1;
            arrayCount = arrayCount + 1;
            //activate css class after add row
            $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
              checkboxClass: 'icheckbox_flat-green',
              radioClass: 'iradio_flat-blue'
            });
          }
          </script>

        </section><!-- /.content -->
    </div><!-- ./wrapper -->

    <?php include("footer.php"); ?>

    <!-- page script -->
    <script type="text/javascript">
      // $(function () {
        // $("#rightsTable").dataTable();
        function loadPerm() {
          var checkedValue = $("input[name='permission[]']").val();
          console.log(checkedValue);
        }
      // });
    </script>
    <script>
      //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
          checkboxClass: 'icheckbox_flat-green',
          radioClass: 'iradio_flat-blue'
        });
    </script>

<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>