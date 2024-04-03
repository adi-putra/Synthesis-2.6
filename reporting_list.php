<?php

//Author: Adiputra
  
include "session.php";

//SQL 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("sql")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $sql_link = "javascript:alert('No reports available.');";
    $sql_box_class = "small-box bg-black text-center";
    $sql_btn_txt = 'No reports available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $sql_link = "reporting_application_sql.php?groupid=".$groupid;
        $sql_box_class = "small-box bg-blue text-center";
        $sql_btn_txt = 'View Report <i class="fa fa-arrow-circle-right"></i>';
    }
}

//Linux 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("linux", "synthesis")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $linux_link = "javascript:alert('No reports available.');";
    $linux_box_class = "small-box bg-black text-center";
    $linux_btn_txt = 'No reports available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $linux_link = "reporting_linux.php?groupid=".$groupid;
        $linux_box_class = "small-box bg-yellow text-center";
        $linux_btn_txt = 'View Report <i class="fa fa-arrow-circle-right"></i>';
    }
}

//Windows 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("window", "ws")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $windows_link = "javascript:alert('No reports available.');";
    $windows_box_class = "small-box bg-black text-center";
    $windows_btn_txt = 'No reports available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        if ($hostgroup["name"] != "Windows Event Log") {
            $groupid = $hostgroup["groupid"];
            $windows_link = "reporting_windows.php?groupid=".$groupid;
            $windows_box_class = "small-box bg-aqua text-center";
            $windows_btn_txt = 'View Report <i class="fa fa-arrow-circle-right"></i>';
        }
    }
}

//Netapp 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("netapp")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $netapp_link = "javascript:alert('No reports available.');";
    $netapp_box_class = "small-box bg-black text-center";
    $netapp_btn_txt = 'No reports available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $netapp_link = "reporting_netapp.php?groupid=".$groupid;
        $netapp_box_class = "small-box bg-orange text-center";
        $netapp_btn_txt = 'View Report <i class="fa fa-arrow-circle-right"></i>';
    }
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
            Reporting List
            <small></small>
          </h1>
          <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Setup</li>
          </ol> -->
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="small-box bg-red text-center">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-circle-exclamation"></i>
                                    </h3>
                                    <h4>
                                        <b>Problems</b>
                                    </h4>
                                </div>
                                <a href="report_problems.php?" class="small-box-footer">
                                View Report <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <!-- <div class="col-xs-3">
                            <div class="<?php echo $sql_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-database"></i>
                                    </h3>
                                    <h4>
                                        <b>SQL</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $sql_link; ?>" class="small-box-footer">
                                    <?php echo $sql_btn_txt; ?>
                                </a>
                            </div>
                        </div>./col -->
								<div class="col-xs-3">
                            <!-- small box -->
                            <div class="small-box bg-green text-center">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-thumbs-up"></i>
                                    </h3>
                                    <h4>
                                        <b>Acknowledgement</b>
                                    </h4>
                                </div>
                                <a href="report_acknowledges.php?" class="small-box-footer">
                                View Report <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $linux_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa-brands fa-linux"></i>
                                    </h3>
                                    <h4>
                                        <b>Linux</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $linux_link; ?>" class="small-box-footer">
                                    <?php echo $linux_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $windows_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa-brands fa-windows"></i>
                                    </h3>
                                    <h4>
                                        <b>Windows</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $windows_link; ?>" class="small-box-footer">
                                    <?php echo $windows_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $netapp_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-cube"></i>
                                    </h3>
                                    <h4>
                                        <b>Netapp</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $netapp_link; ?>" class="small-box-footer">
                                    <?php echo $netapp_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                    </div>
                </div>
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
      });
    </script>

<?php
//$zbx->logout();//logout from zabbix API
?>

  </body>
</html>