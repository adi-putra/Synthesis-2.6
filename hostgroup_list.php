<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include "session.php";

//only allow admin to see user list
if ($zabUtype !== "3") {
  //display error message box
  print '<script>alert("You do not have access to this page!");</script>';
  //go to login page
  print '<script>window.location.assign("dashboard.php");</script>';
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
          Hosts Group
          <small> List</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
          <li class="active">Hosts Group List</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body">
                <a href="hostgroup_create.php"><button class="btn btn-primary btn-sm">Create Host Group</button></a><br><br><br>
                <table id="hostgroup_tbl" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Group Name</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php

                    $params = array(
                      "output" => array("groupid", "name"),
                      //"selectGroups" => array("name"),
                    );

                    $result = $zbx->call('hostgroup.get', $params);

                    $i = 1;
                    foreach ($result as $hostgroup) {

                      print "<tr>";
                      print "<td>" . $i . "</td>";
                      print "<td><a href='hostgroup_edit.php?groupid=" . $hostgroup['groupid'] . "'>" . $hostgroup['name'] . "</td></a>";
                      print "</tr>";

                      $i++;
                    }
                    ?>
                  </tbody>
                </table>
              </div><!-- /.box-body -->
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->

    <?php include("footer.php"); ?>

  </div><!-- ./wrapper -->

  <!-- page script -->
  <script type="text/javascript">
    $(function() {
      $("#hostgroup_tbl").dataTable();
    });
  </script>

  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>