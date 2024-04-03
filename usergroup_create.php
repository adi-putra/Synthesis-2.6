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
            Create User Group
            <small>form</small>
          </h1>
          <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">User</li>
          </ol> -->
        </section>


        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box box-primary">
                <div class="box-body">
                  <form name="userForm" method="post" action="usergroup_create_proc.php">
                   <table class="table table-bordered table-striped">
                   <tr>
                    <td>Group Name</td>
                    <td><input style="width: 50%;" class="form-control" type="text" name="usrgrpName" required></td>
                  </tr>
                  <!-- <tr>
                     <td>Frontend Access</td>
                     <td>
                       <select name="usrgrpFA" class="form-control" style="width: auto;" required>
                        <option value="0">System Default</option>
                        <option value="1">Internal</option>
                        <option value="2">LDAP</option>
                        <option value="3">Disable</option>
                      </select>
                     </td>
                  </tr> -->
                  <tr>
                     <td>Status</td>
                     <td><input type="checkbox" name="usrgrpStatus" class="flat-red" value="0" checked></td>
                  </tr>
                  <!-- <tr>
                    <td>Debug Mode</td>
                    <td><input type="checkbox" name="usrgrpDebug" class="flat-red" value="1"></td>
                  </tr> -->
                  </table>
                  <button class='btn bg-green margin' type="submit">Submit</button>
                  <button class='btn btn-default margin' type="reset">Reset</button>
                  </form>
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
    <script>
      //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
          checkboxClass: 'icheckbox_flat-green',
          radioClass: 'iradio_flat-blue'
        });
    </script>

<?php
//$zbx->logout();//logout from zabbix API
?>

  </body>
</html>