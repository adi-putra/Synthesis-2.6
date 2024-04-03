<?php
  include 'session.php';

  $timefrom = $_GET['timefrom'] ?? time() - 24*60*60;
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;
  if ($diff == 86400) {
    $status = "Last 1 day";
  }
  else if ($diff == 604800) {
    $status = "Last 7 days";
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>AdminLTE 2 | Dashboard</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />    
    <!-- FontAwesome 4.3.0 -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />    
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Date Picker -->
    <link href="plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />

    <!-- DATA TABLES -->
    <link href="plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />

    <!-- external libs from cdnjs -->
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

        <!-- PivotTable.js libs from ../dist -->
        <link rel="stylesheet" type="text/css" href="dist/pivot.css">
        <script type="text/javascript" src="dist/pivot.js"></script>
        
        <!-- optional: mobile support with jqueryui-touch-punch -->
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue">
    <div class="wrapper">
      
      <header class="main-header">
        <!-- Logo -->
        <a href="dashboard.php" class="logo"><b>NOC</b></a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu" style="display: none;">
            <ul class="nav navbar-nav">
              <!-- Messages: style can be found in dropdown.less-->
              <li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-envelope-o"></i>
                  <span class="label label-success">4</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have 4 messages</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <li><!-- start message -->
                        <a href="#">
                          <div class="pull-left">
                            <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
                          </div>
                          <h4>
                            Support Team
                            <small><i class="fa fa-clock-o"></i> 5 mins</small>
                          </h4>
                          <p>Why not buy a new awesome theme?</p>
                        </a>
                      </li><!-- end message -->
                      <li>
                        <a href="#">
                          <div class="pull-left">
                            <img src="dist/img/user3-128x128.jpg" class="img-circle" alt="user image"/>
                          </div>
                          <h4>
                            AdminLTE Design Team
                            <small><i class="fa fa-clock-o"></i> 2 hours</small>
                          </h4>
                          <p>Why not buy a new awesome theme?</p>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <div class="pull-left">
                            <img src="dist/img/user4-128x128.jpg" class="img-circle" alt="user image"/>
                          </div>
                          <h4>
                            Developers
                            <small><i class="fa fa-clock-o"></i> Today</small>
                          </h4>
                          <p>Why not buy a new awesome theme?</p>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <div class="pull-left">
                            <img src="dist/img/user3-128x128.jpg" class="img-circle" alt="user image"/>
                          </div>
                          <h4>
                            Sales Department
                            <small><i class="fa fa-clock-o"></i> Yesterday</small>
                          </h4>
                          <p>Why not buy a new awesome theme?</p>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <div class="pull-left">
                            <img src="dist/img/user4-128x128.jpg" class="img-circle" alt="user image"/>
                          </div>
                          <h4>
                            Reviewers
                            <small><i class="fa fa-clock-o"></i> 2 days</small>
                          </h4>
                          <p>Why not buy a new awesome theme?</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="footer"><a href="#">See All Messages</a></li>
                </ul>
              </li>
              <!-- Notifications: style can be found in dropdown.less -->
              <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bell-o"></i>
                  <span class="label label-warning">10</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have 10 notifications</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <li>
                        <a href="#">
                          <i class="fa fa-users text-aqua"></i> 5 new members joined today
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <i class="fa fa-warning text-yellow"></i> Very long description here that may not fit into the page and may cause design problems
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <i class="fa fa-users text-red"></i> 5 new members joined
                        </a>
                      </li>

                      <li>
                        <a href="#">
                          <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <i class="fa fa-user text-red"></i> You changed your username
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="footer"><a href="#">View all</a></li>
                </ul>
              </li>
              <!-- Tasks: style can be found in dropdown.less -->
              <li class="dropdown tasks-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-flag-o"></i>
                  <span class="label label-danger">9</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have 9 tasks</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <li><!-- Task item -->
                        <a href="#">
                          <h3>
                            Design some buttons
                            <small class="pull-right">20%</small>
                          </h3>
                          <div class="progress xs">
                            <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                              <span class="sr-only">20% Complete</span>
                            </div>
                          </div>
                        </a>
                      </li><!-- end task item -->
                      <li><!-- Task item -->
                        <a href="#">
                          <h3>
                            Create a nice theme
                            <small class="pull-right">40%</small>
                          </h3>
                          <div class="progress xs">
                            <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                              <span class="sr-only">40% Complete</span>
                            </div>
                          </div>
                        </a>
                      </li><!-- end task item -->
                      <li><!-- Task item -->
                        <a href="#">
                          <h3>
                            Some task I need to do
                            <small class="pull-right">60%</small>
                          </h3>
                          <div class="progress xs">
                            <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                              <span class="sr-only">60% Complete</span>
                            </div>
                          </div>
                        </a>
                      </li><!-- end task item -->
                      <li><!-- Task item -->
                        <a href="#">
                          <h3>
                            Make beautiful transitions
                            <small class="pull-right">80%</small>
                          </h3>
                          <div class="progress xs">
                            <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                              <span class="sr-only">80% Complete</span>
                            </div>
                          </div>
                        </a>
                      </li><!-- end task item -->
                    </ul>
                  </li>
                  <li class="footer">
                    <a href="#">View all tasks</a>
                  </li>
                </ul>
              </li>
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                  <span class="hidden-xs">Aput</span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
                    <p>
                      Alexander Pierce - Web Developer
                      <small>Member since Nov. 2012</small>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <li class="user-body">
                    <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </li>
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="#" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="#" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="active treeview">
              <a href="dashboard.php">
                <i class="fa fa-fw fa-home"></i> <span>Dashboard</span> 
              </a>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-fw fa-desktop"></i> <span>Host</span> <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="active"><a href="hostlist.php"><i class="fa fa-circle-o"></i> Hosts List</a></li>
                <li class="active"><a href="hostdetails.php"><i class="fa fa-circle-o"></i> Latest Data</a></li>
              </ul>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-fw fa-sitemap"></i> <span>Groups</span> <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li>
                  <a href="windows.php"><i class="fa fa-circle-o"></i> Windows </a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Linux </a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Firewall </a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> UPS </a>
                </li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Switches </a>
                </li>
              </ul>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-folder-open"></i> <span>Application</span> <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li><a href="dashboard.php"><i class="fa fa-circle-o"></i> Dashboard</a></li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Hosts <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                    <li><a href="hostlist.php"><i class="fa fa-circle-o"></i> Hosts List </a></li>
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i> Overview <i class="fa fa-angle-left pull-right"></i></a>
                      <ul class="treeview-menu">
                        <li><a href="windows.php"><i class="fa fa-circle-o"></i> Windows Overview</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> Report</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
                <li><a href="index2.html"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>
              </ul>
            </li>

            <li class="treeview">
              <a href="logout.php" onclick="if (!confirm('Are you sure you want to log out?')) { return false }">
                <i class="fa fa-fw fa-power-off"></i> <span>Logout</span> 
              </a>
            </li>
            
            
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Groups
            <small>Group List</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
          </ol>
        </section>

          <section class="content">
          	<div class="row">
          		<div class="col-xs-12">
	              <div class="box">
	                <div class="box-header">
	                  <h3 class="box-title">Overview</h3>
	                </div><!-- /.box-header -->
	                <div class="box-body">
	                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Interface</th>
                        <th>Availability</th>
                        <th>Problems</th>
                        <th>Group</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                      $params = array(
                        "output" => array("hostid","name", "status", "available", "ipmi_available", "snmp_available", "jmx_available"),
                        "selectGroups" => array("name"),
                        "selectInterfaces" => array("ip")
                      );

                      $result = $zbx->call('host.get',$params);
                      //count id from 1 and list all details from result
                      $id = 1;
                      foreach ($result as $v) {
                        $hostid = $v['hostid'];
                        $name = $v['name'];
                        $status = $v['status'];
                        $available = $v['available'];
                        $ipmi = $v['ipmi_available'];
                        $snmp = $v['snmp_available'];
                        $jmx = $v['jmx_available'];
                        print "<tr>
                          <td>$id</td>";


                        if ($name != 'NOC SQL Server') {
                          foreach ($v['groups'] as $group) {
                          $groupname = $group['name'];
                          print "<td><a href='hostdetails.php?hostid=".$hostid."'>$name</a></td>";
                          }
                        }
                        //specific for NOC SQL Server
                        else {
                          print "<td><a href='hostdetails.php?hostid=".$hostid."'>$name</a></td>";
                        }

                        print "<td>";
                        foreach($v['interfaces'] as $interface) {
                          $ip = $interface['ip'];
                          print "$ip <br>";
                          break;
                        }
                        print"</td>";

                        //Availability
                        print("<td style='text-align: center; padding: 0;'>");
                        if ($available == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>ZBX</button>";
                        }
                        else if ($available == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>ZBX</button>";
                        }
                        else {
                          print"<button class='btn bg-white margin' style='margin: 0px;'>ZBX</button>";
                        }

                        if ($snmp == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>SNMP</button>";
                        }
                        else if ($snmp == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>SNMP</button>";
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>SNMP</button>";
                        }

                        if ($jmx == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>JMX</button>";
                        }
                        else if ($jmx == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>JMX</button>";
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>JMX</button>";
                        }

                        if ($ipmi == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>IPMI</button>";
                        }
                        else if ($ipmi == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>IPMI</button>";
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>IPMI</button>";
                        }

                        print "</td>";

                        //
                        print "<td style='text-align: center; padding: 0;'>";

                        $params = array(
                        "output" => array("severity"),
                        "hostids" => $hostid,
                        "severities" => "1",
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                        );

                        //call api problem.get correespond to hostid
                        $result = $zbx->call('problem.get',$params);

                        if ($result > 0) {
                          print "<a href='#' data-toggle='tooltip' data-placement='right' title='Info'><button class='btn bg-blue margin' style='margin: 0px;'>$result</button></a>";
                        }

                        $params = array(
                        "output" => array("severity"),
                        "hostids" => $hostid,
                        "severities" => "2",
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                        );

                        //call api problem.get correespond to hostid
                        $result = $zbx->call('problem.get',$params);

                        if ($result > 0) {
                          print "<a href='#' data-toggle='tooltip' data-placement='right' title='Warning'><button class='btn bg-yellow margin' style='margin: 0px;'>$result</button></a>";
                        }

                        $params = array(
                        "output" => array("severity"),
                        "hostids" => $hostid,
                        "severities" => "3",
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                        );

                        //call api problem.get correespond to hostid
                        $result = $zbx->call('problem.get',$params);

                        if ($result > 0) {
                          print "<a href='#' data-toggle='tooltip' data-placement='right' title='Average'><button class='btn bg-orange margin' style='margin: 0px;'>$result</button></a>";
                        }

                        $params = array(
                        "output" => array("severity"),
                        "hostids" => $hostid,
                        "severities" => "4",
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                        );

                        //call api problem.get correespond to hostid
                        $result = $zbx->call('problem.get',$params);

                        if ($result > 0) {
                          print "<a href='#' data-toggle='tooltip' data-placement='right' title='High'><button class='btn bg-red margin' style='margin: 0px;'>$result</button></a>";
                        }

                        $params = array(
                        "output" => array("severity"),
                        "hostids" => $hostid,
                        "severities" => "5",
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                        );

                        //call api problem.get correespond to hostid
                        $result = $zbx->call('problem.get',$params);

                        if ($result > 0) {
                          print "<a href='#' data-toggle='tooltip' data-placement='right' title='Disaster'><button class='btn bg-red margin' style='margin: 0px;'>$result</button></a>";
                        }
                        print "</td>";
                        //

                        foreach ($v['groups'] as $group) {
                          $groupname = $group['name'];
                          if ($groupname != "MSSQLServer Monitoring") {
                            print "<td>$groupname</td>";
                          }
                        
                        } 
                        if ($status == 0) {
                          print "<td><button class='btn btn-block btn-success btn-sm'>Enabled</button></td>";
                        }
                        else {
                          print "<td><button class='btn btn-block btn-danger btn-sm'>Disabled</button></td>";
                        };

                        $id = $id + 1;
                      } 
                      ?>                 
                      <script>
                      $(document).ready(function(){
                      $('[data-toggle="tooltip"]').tooltip();   
                      });
                      </script>
                     </tr>
                    </tbody>
                  </table>
        				
	                </div>
	              </div>
            	</div>
          	</div>
        </section>
        
      <footer class="main-footer" style="margin-left: 0px;">
        <div class="pull-right hidden-xs">
          <b>Version</b> 2.0
        </div>
        <strong>Copyright &copy; 2014-2015 <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights reserved.
      </footer>
    </div><!-- ./wrapper -->

    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- DATA TABES SCRIPT -->
    <script src="plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
    <script src="plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
    <!-- SlimScroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- InputMask -->
    <script src="plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
    <script src="plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
    <script src="plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
    <!-- date-range-picker -->
    <script src="plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- bootstrap color picker -->
    <script src="plugins/colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>
    <!-- bootstrap time picker -->
    <script src="plugins/timepicker/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js" type="text/javascript"></script>
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
    <!-- Page script -->
    <script type="text/javascript">
      $(function () {
        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();

        //Date range picker
        $('#reservation').daterangepicker();
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A'});
        //Date range as a button
        $('#daterange-btn').daterangepicker(
                {
                  ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                  },
                  startDate: moment().subtract('days', 29),
                  endDate: moment()
                },
        function (start, end) {
          $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        );

        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
          checkboxClass: 'icheckbox_minimal-blue',
          radioClass: 'iradio_minimal-blue'
        });
        //Red color scheme for iCheck
        $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
          checkboxClass: 'icheckbox_minimal-red',
          radioClass: 'iradio_minimal-red'
        });
        //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
          checkboxClass: 'icheckbox_flat-green',
          radioClass: 'iradio_flat-green'
        });

        //Colorpicker
        $(".my-colorpicker1").colorpicker();
        //color picker with addon
        $(".my-colorpicker2").colorpicker();

        //Timepicker
        $(".timepicker").timepicker({
          showInputs: false
        });
      });
    </script>
  </body>
</html>