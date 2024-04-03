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
          Create User Map
          <small>form</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
          <li class="active">Map</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box box-primary">
              <div class="box-body">
                <form name="MapuserAdd" method="get" action="map_user_add.php">
                  <table class="table table-bordered table-striped">
                    <tr>
                      <td>Onwer</td>
                      <td>
                        <select class="form-control" name="usrid" style="width: auto;">
                          <?php
                          $params = array(
                            "output" => "extend"
                          );

                          $result = $zbx->call('user.get', $params);
                          foreach ($result as $usr) {

                            if ($usr["name"] == "Zabbix") {
                              print '<option value="' . $usr["userid"] . '">' . $usr['username'] . ' (Synthesis ' . $usr["surname"] . ')' . '</option>';
                            }
                            if (($usr["name"] || $usr["surname"] != "") && $usr["name"] != "Zabbix") {
                              print '<option value="' . $usr["userid"] . '">' . $usr['username'] . ' (' . $usr["name"] . ' ' . $usr["surname"] . ')' . '</option>';
                            } else if (($usr["name"] || $usr["surname"] == "") && $usr["name"] != "Zabbix") {
                              print '<option value="' . $usr["userid"] . '">' . $usr['username'] . '</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Name</td>
                      <td><input style="width: 50%;" class="form-control" type="text" name="name" required></td>
                    </tr>
                    <tr>
                      <td>Width</td>
                      <td><input style="width: 50%;" class="form-control" type="text" name="width" required></td>
                    </tr>
                    <tr>
                      <td>Height</td>
                      <td><input style="width: 50%;" class="form-control" type="text" name="height" required></td>
                    </tr>
                  </table>
                  <div class="box-footer">
                    <button class='btn bg-green margin' type="submit">Submit</button>
                    <button class='btn bg-blue margin' type="reset">Reset</button>
                  </div>
                </form>
              </div><!-- /.box-body -->
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->
    </div><!-- ./wrapper -->

    <?php include("footer.php"); ?>

    <?php
    $zbx->logout(); //logout from zabbix API
    ?>

</body>

</html>