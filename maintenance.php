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
            Maintenance
            <small></small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Maintenance</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                  <a href="maintenance_create.php"><button class='btn btn-block btn-primary btn-sm' style="float: left; width: auto;">Create Maintenance</button></a><br><br><br>
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Active Since</th>
                        <th>Active Till</th>
                        <th>Description</th>
                        <th style="text-align: center;">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    $id = 1;
                    $params = array(
                        "output" => "extend"
                    );
                    //call api problem.get only to get eventid
                    $result = $zbx->call('maintenance.get',$params);
                    foreach ($result as $main) {
                        print "<tr>";
                        print "<td>$id</td>";
                        print "<td><a href='maintenance_edit.php?main_id=".$main["maintenanceid"]."'>".$main["name"]."</a></td>";

                        /*check maintenance type
                        if ($main["maintenance_type"] == 0) {
                            print "<td>With data collection</td>";
                        }
                        else {
                            print "<td>No data collection</td>";
                        }*/

                        print "<td>".date("d-m-Y\ H:i A\ ", $main["active_since"])."</td>";
                        print "<td>".date("d-m-Y\ H:i A\ ", $main["active_till"])."</td>";
                        print "<td>".$main["description"]."</td>";

                        //check if its active state or not
                        $curr_time = time();
                        if (($curr_time >= $main["active_since"]) && ($curr_time <= $main["active_till"])){
                            print '<td><button class="btn btn-block btn-success">Active</button></td>';
                        }
                        else{
                            print '<td><button class="btn btn-block btn-danger">Expired</button></td>';
                        }
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