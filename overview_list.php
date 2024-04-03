<?php

//Author: Adiputra
  
include "session.php";

//Linux 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("zabbix", "linux")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $linux_link = "javascript:alert('No reports available.');";
    $linux_box_class = "small-box bg-gray text-center";
    $linux_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $linux_link = "overview_linux.php?groupid=".$groupid;
        $linux_box_class = "small-box bg-yellow text-center";
        $linux_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
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
    $windows_link = "javascript:alert('Not available.');";
    $windows_box_class = "small-box bg-gray text-center";
    $windows_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $windows_link = "overview_windows1.php?groupid=".$groupid;
        $windows_box_class = "small-box bg-blue text-center";
        $windows_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//ESX 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("esx")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $esx_link = "javascript:alert('Not available.');";
    $esx_box_class = "small-box bg-gray text-center";
    $esx_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $esx_link = "overview_esx.php?groupid=".$groupid;
        $esx_box_class = "small-box bg-purple text-center";
        $esx_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//UPS 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("ups")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $ups_link = "javascript:alert('Not available.');";
    $ups_box_class = "small-box bg-gray text-center";
    $ups_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $ups_link = "overview_ups.php?groupid=".$groupid;
        $ups_box_class = "small-box bg-green text-center";
        $ups_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//Firewall 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("firewall", "fw")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $firewall_link = "javascript:alert('Not available.');";
    $firewall_box_class = "small-box bg-gray text-center";
    $firewall_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $firewall_link = "overview_firewall.php?groupid=".$groupid;
        $firewall_box_class = "small-box bg-maroon text-center";
        $firewall_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//iLO 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("ilo")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $ilo_link = "javascript:alert('Not available.');";
    $ilo_box_class = "small-box bg-gray text-center";
    $ilo_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $ilo_link = "overview_ilo.php?groupid=".$groupid;
        $ilo_box_class = "small-box bg-teal text-center";
        $ilo_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//IMM 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("imm")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $imm_link = "javascript:alert('Not available.');";
    $imm_box_class = "small-box bg-gray text-center";
    $imm_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $imm_link = "overview_imm.php?groupid=".$groupid;
        $imm_box_class = "small-box bg-orange text-center";
        $imm_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//Switches 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("switches")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $switches_link = "javascript:alert('Not available.');";
    $switches_box_class = "small-box bg-gray text-center";
    $switches_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $switches_link = "overview_switches.php?groupid=".$groupid;
        $switches_box_class = "small-box bg-aqua text-center";
        $switches_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//VM 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("vmware")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $vm_link = "javascript:alert('Not available.');";
    $vm_box_class = "small-box bg-gray text-center";
    $vm_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $vm_link = "overview_vm.php?groupid=".$groupid;
        $vm_box_class = "small-box bg-navy text-center";
        $vm_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//Hypervisor 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("hypervisor")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $hypervisor_link = "javascript:alert('Not available.');";
    $hypervisor_box_class = "small-box bg-gray text-center";
    $hypervisor_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $hypervisor_link = "overview_hypervisor.php?groupid=".$groupid;
        $hypervisor_box_class = "small-box bg-navy text-center";
        $hypervisor_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
    }
}

//VCenter 
$params = array(
"output" => array("groupid", "name"),
"search" => array("name" => array("vcenter")),
"searchByAny" => true,
"limit" => 1
);
//call api method
$result = $zbx->call('hostgroup.get', $params);
if (empty($result)) {
    $vcenter_link = "javascript:alert('Not available.');";
    $vcenter_box_class = "small-box bg-gray text-center";
    $vcenter_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $vcenter_link = "overview_vcenter.php?groupid=".$groupid;
        $vcenter_box_class = "small-box bg-navy text-center";
        $vcenter_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
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
    $netapp_link = "javascript:alert('Not available.');";
    $netapp_box_class = "small-box bg-gray text-center";
    $netapp_btn_txt = 'Not available <i class="fa fa-circle-xmark"></i>';
}
else {
    foreach ($result as $hostgroup) {
        $groupid = $hostgroup["groupid"];
        $netapp_link = "overview_netapp.php?groupid=".$groupid;
        $netapp_box_class = "small-box bg-orange text-center";
        $netapp_btn_txt = 'Overview <i class="fa fa-arrow-circle-right"></i>';
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
            Overview List
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
                            <div class="<?php echo $firewall_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-shield"></i>
                                    </h3>
                                    <h4>
                                        <b>Firewall</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $firewall_link; ?>" class="small-box-footer">
                                    <?php echo $firewall_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $esx_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-regular fa-square"></i>
                                    </h3>
                                    <h4>
                                        <b>ESX</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $esx_link; ?>" class="small-box-footer">
                                    <?php echo $esx_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                    </div>
                    <div class="row">
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $ups_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-bolt"></i>
                                    </h3>
                                    <h4>
                                        <b>UPS</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $ups_link; ?>" class="small-box-footer">
                                    <?php echo $ups_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $ilo_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-cube"></i>
                                    </h3>
                                    <h4>
                                        <b>iLO</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $ilo_link; ?>" class="small-box-footer">
                                    <?php echo $ilo_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $imm_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-cube"></i>
                                    </h3>
                                    <h4>
                                        <b>IMM</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $imm_link; ?>" class="small-box-footer">
                                    <?php echo $imm_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $switches_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-server"></i>
                                    </h3>
                                    <h4>
                                        <b>Switches</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $switches_link; ?>" class="small-box-footer">
                                    <?php echo $switches_btn_txt; ?>
                                </a>
                            </div>
                        </div><!-- ./col -->
                    </div>
                    <div class="row">
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $vm_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-desktop"></i>
                                    </h3>
                                    <h4>
                                        <b>Virtual Machines</b>
                                    </h4>
                                </div>
                                <a href="<?php echo $vm_link; ?>" class="small-box-footer">
                                    <?php echo $vm_btn_txt; ?>
                                </a>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $hypervisor_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-desktop"></i>
                                    </h3>
                                    <h4>
                                        <b>Hypervisor </b>
                                    </h4>
                                </div>
                                <a href="<?php echo $hypervisor_link; ?>" class="small-box-footer">
                                    <?php echo $hypervisor_btn_txt; ?>
                                </a>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $vcenter_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-desktop"></i>
                                    </h3>
                                    <h4>
                                        <b>VCenter </b>
                                    </h4>
                                </div>
                                <a href="<?php echo $vcenter_link; ?>" class="small-box-footer">
                                    <?php echo $vcenter_btn_txt; ?>
                                </a>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <!-- small box -->
                            <div class="<?php echo $netapp_box_class; ?>">
                                <div class="inner">
                                    <h3>
                                        <i class="fa fa-cube"></i>
                                    </h3>
                                    <h4>
                                        <b>Netapp </b>
                                    </h4>
                                </div>
                                <a href="<?php echo $netapp_link; ?>" class="small-box-footer">
                                    <?php echo $netapp_btn_txt; ?>
                                </a>
                            </div>
                        </div>
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