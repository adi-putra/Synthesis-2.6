<?php
  
  include "session.php";

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();

  $templateid = 10362;

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
            Trigger
            <small>List</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Trigger</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                <iframe src="http://172.16.210.117/synconfig/triggers.php?filter_set=1&filter_hostids%5B0%5D=<?php echo $templateid; ?>&context=template" title="Triggers"></iframe>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- ./wrapper -->
      
      <?php include("footer.php"); ?>
  

<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>

<script>
    $("#map_table").DataTable();

    function delete_map(mapid) {
      if (confirm("Confirm delete this map?")) {
        location.assign("map_delete.php?mapid=" + mapid);
      }
      else {
        return false;
      }
    }
</script>