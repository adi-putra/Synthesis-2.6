<?php
  
  include 'session.php';

  $hostid = $_GET['hostid'] ?? "10334";
  $vdname = $_GET['hostid'] ?? "0.1";

  if (isset($_POST['submit']))
  {
  // Execute this code if the submit button is pressed.
  $timefrom1 = $_POST['timefrom'];
  $timefrom = strtotime($timefrom1);
  $timetill1 = $_POST['timetill'];
  $timetill = strtotime($timetill1);
  }
  else {
  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();
  }

  //display time format
  $diff = $timetill - $timefrom;
  if ($diff == 3600) {
    $status = "Last 1 hour";
  }
  else if ($diff < 86400) {
    $status = "Today";
  }
  else if ($diff == 86400) {
    $status = "Last 1 day";
  }
  else if ($diff == 172800) {
    $status = "Last 2 days";
  }
  else if ($diff == 604800) {
    $status = "Last 7 days";
  }
  else if ($diff == 2592000) {
    $status = "Last 30 days";
  }

  function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
  }

  function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
  
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Virtual Disks</title>
    <!--<meta http-equiv="refresh" content="60">-->
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- daterange picker -->
    <link href="plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- iCheck for checkboxes and radio inputs -->
    <link href="plugins/iCheck/all.css" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Color Picker -->
    <link href="plugins/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet"/>
    <!-- Bootstrap time Picker -->
    <link href="plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet"/>
    <!-- Morris charts -->
    <link href="plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />

     <!-- external libs from cdnjs -->
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

      <!-- webdatarocks PIVOT TABLE -->
      <link href="https://cdn.webdatarocks.com/latest/webdatarocks.min.css" rel="stylesheet"/>
      <script src="https://cdn.webdatarocks.com/latest/webdatarocks.toolbar.min.js"></script>
      <script src="https://cdn.webdatarocks.com/latest/webdatarocks.js"></script>

      <!-- optional: mobile support with jqueryui-touch-punch -->
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

        <!-- DataTables -->
    <link href="plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
    <script src="plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
    <script src="plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>

    <!-- Resources AM4CORE -->
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/dark.js"></script>


    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">

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
        <a href="index2.html" class="logo"><b>Admin</b>LTE</a>
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
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            <?php
            $params = array(
            "output" => array("name"),
            "hostids" => $hostid
             );
             //call api method
             $result = $zbx->call('host.get',$params);   
             foreach ($result as $host) {
                $hostname = $host["name"];
            }

            $params = array(
            "output" => array("name"),
            "itemids" => $itemid
             );
             //call api method
             $result = $zbx->call('item.get',$params);   
             foreach ($result as $item) {
                $itemname = $item["name"];
                if (strpos($itemname, "[") !== false) {
                  $titlegraph = str_replace("[","[[", $itemname);
                  $titlegraph = str_replace("]","]]", $titlegraph);
                }
                else {
                  $titlegraph = $itemname;
                }
            }
            
            print $itemname;
            ?>
            <small><?php echo $hostname;?></small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
          </ol>
          <br><br>
          <!-- select -->
          <div class="form-group">
            <select class="form-control" name="selecthost" onchange="location = this.value;" style="display: inline-block; width: auto;">
              <option><?php echo $itemname ?></option>
              <?php
              $params = array(
              "output" => array("itemid","name"),
              "hostids" => $hostid,
              "search" => array("key_" => "system.hw.virtualdisk.layout[cpqDaLogDrvFaultTol.")//seach id contains specific word
              );
              //call api method
              $result = $zbx->call('item.get',$params);
              foreach ($result as $row) {
                $getvdname = substr($row["key_"], 49);
                $getvdname = substr($row, 0, -1);
                echo '<option value="ilo_vdisks.php?vdname='.$getvdname.'">'.$getvdname.'</option>';
              }
              ?>
              </select>
          </div>
        </section>
       
        <!-- Main content -->
        <section class="content">
          <div class="row">             
            <div class="col-xs-12">
              <div class="box" style="background-color: #3c8dbc;">
                <div class="box-header">
                  <h3 class="box-title" style="color: white;"><?php if(isset($_POST['submit'])){ echo $daterange = $timefrom1.' - '.$timetill1;}else{ echo $status; }?></h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="container">
                    <form action="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo $timefrom;?>&timetill=<?php echo $timetill;?>" method="post">
                      <div class="col-md-3">
                        <div class="btn-group">
                          <button type="button" class="btn btn-success btn-flat">Action</button>
                          <button type="button" class="btn btn-success btn-flat dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                          </button>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo strtotime('today');?>&timetill=<?php echo time();?>">Today</a></li>
                            <li><a href="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo time()-60*60;?>&timetill=<?php echo time();?>">Last 1 hour</a></li>
                            <li><a href="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo time()-24*60*60;?>&timetill=<?php echo time();?>">Last 1 day</a></li>
                            <li><a href="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo time()-24*2*60*60;?>&timetill=<?php echo time();?>">Last 2 days</a></li>
                            <li><a href="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo time()-24*7*60*60;?>&timetill=<?php echo time();?>">Last 7 days</a></li>
                            <li><a href="fw_vpn.php?hostid=<?php echo $hostid;?>&itemid=<?php echo $itemid;?>&timefrom=<?php echo time()-24*30*60*60;?>&timetill=<?php echo time();?>">Last 30 days</a></li>
                            <li><a href="#">Something else here</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Separated link</a></li>
                          </ul>
                        </div>
                        <input type="text" class="form-control" value="<?php if(isset($_POST['submit'])){ echo "";}else{ echo $status; }?>" style="display: inline-block; width: 150px;" disabled>
                     </div>
                     <div class='col-md-3'>
                              <div class="form-group">
                                 <div class='input-group date' id='datetimepicker6'>
                                    <input type='text' class="form-control" name="timefrom" value="<?php if (isset($_POST['timefrom'])) {echo $_POST['timefrom'];} else{ echo date('d-m-Y h:i A', $timefrom);} ?>" placeholder="From:"/>
                                    <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                 </div>
                              </div>
                           </div>
                           <div class='col-md-3'>
                              <div class="form-group">
                                 <div class='input-group date' id='datetimepicker7'>
                                    <input type='text' class="form-control" name="timetill" value="<?php if (isset($_POST['timetill'])) {echo $_POST['timetill'];} else{ echo date('d-m-Y h:i A', $timetill);} ?>" placeholder="To:" />
                                    <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                 </div>
                              </div>
                           </div>
                     <div class='col-md-3'>
                       <input type="submit" name="submit" value="Apply" style="height: 35px;"></input>
                     </div>
                   </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-xs-12">
                                <?php

                                //perform trend.get if time range above 7 days
                                if ($diff >= 72000) {
                                  $params = array(
                                        "output" => array("clock", "value_avg"),
                                        "itemids" => $itemid,
                                        "sortfield" => "clock",
                                        "sortorder" => "DESC",
                                        "time_from" => $timefrom,
                                        "time_till" => $timetill
                                        );

                                  //call api history.get with params
                                  $result = $zbx->call('trend.get',$params);
                                  $chart_data = '';
                                  foreach ($result as $row) {
                                        //$row["clock"] = date(" H:i\ ", $row["clock"]);
                                        $row["clock"] = $row["clock"] * 1000;
                                        $chart_data .= "{clock: ";
                                        $chart_data .= $row["clock"];
                                        $chart_data .= ", ";
                                        $chart_data .= "value: ";
                                        $chart_data .= $row["value_avg"];
                                        $chart_data .= "},";
                                    } 
                                }

                                else {
                                $params = array(
                                        "output" => array("clock", "value"),
                                        "itemids" => $itemid,
                                        "sortfield" => "clock",
                                        "sortorder" => "DESC",
                                        "time_from" => $timefrom,
                                        "time_till" => $timetill
                                        );

                                //call api history.get with params
                                $result = $zbx->call('history.get',$params);
                                $chart_data = '';
                                foreach (array_reverse($result) as $row) {
                                      //$row["clock"] = date(" H:i\ ", $row["clock"]);
                                      $row["clock"] = $row["clock"] * 1000;
                                      $chart_data .= "{clock: ";
                                      $chart_data .= $row["clock"];
                                      $chart_data .= ", ";
                                      $chart_data .= "value: ";
                                      $chart_data .= $row["value"];
                                      $chart_data .= "},";                                      
                                  } 
                                }
                                ?>  
                                  <!-- VPN CHART -->
                                  <div class="box box-primary">
                                    <div class="box-body chart-responsive">
                                      <div id="vpnchart" style="width: auto;height: 400px;"></div>
                                    </div><!-- /.box-body -->
                                  </div><!-- /.box -->
                                
            </div>
          </div>





        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Version</b> 2.0
        </div>
        <strong>Copyright &copy; 2014-2015 <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights reserved.
      </footer>
    </div><!-- ./wrapper -->

   
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- Morris.js charts -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="plugins/morris/morris.min.js" type="text/javascript"></script>

    
    <script src="https://cdn.jsdelivr.net/momentjs/2.14.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">

    <!-- SlimScroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js" type="text/javascript"></script>

    

    <script type="text/javascript">
   $(function () {
       $('#datetimepicker6').datetimepicker({
                format: "DD-MM-YYYY hh:mm A",
              
            });
       $('#datetimepicker7').datetimepicker({
   useCurrent: false, //Important! See issue #1075
   format: "DD-MM-YYYY hh:mm A",
   });
       $("#datetimepicker6").on("dp.change", function (e) {
           $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
       });
       $("#datetimepicker7").on("dp.change", function (e) {
           $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
       });
   });
</script>

<script type="text/javascript">
  //CPU Chart
    // Themes begin
    am4core.useTheme(am4themes_dark);
    // Themes end

    // Create chart instance
    var vpnchart = am4core.create("vpnchart", am4charts.XYChart);

    //show in %
    vpnchart.numberFormatter.numberFormat = "#.0";

    // Add data
    vpnchart.data = [<?php echo substr($chart_data, 0, -1);?>];
    vpnchart.background.fill = '#121212';
    vpnchart.background.opacity = 1.0;

    var title = vpnchart.titles.create();
    title.text = '<?php echo $titlegraph;?>';
    title.fontSize = 25;
    title.marginBottom = 30;
    title.align = 'center';

    // Create axes
    var dateAxis = vpnchart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 20;
    dateAxis.renderer.labels.template.rotation = 90;
    dateAxis.renderer.labels.template.verticalCenter = "middle";
    dateAxis.renderer.labels.template.horizontalCenter = "left";


    var valueAxis = vpnchart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    var series = vpnchart.series.push(new am4charts.LineSeries());
    series.name = '<?php echo $titlegraph; ?>';
    series.dataFields.valueY = "value";
    series.dataFields.dateX = "clock";
    series.strokeWidth = 2;
    series.fillOpacity = 0.0;
    series.minBulletDistance = 10;
    series.tooltipText = "{dateX}: \n{valueY}";
    series.tooltip.pointerOrientation = "vertical";
    series.tooltip.background.cornerRadius = 20;
    series.tooltip.background.fillOpacity = 0.5;
    series.tooltip.label.padding(12,12,12,12);
    series.legendSettings.valueText = "last: {valueY.close}/min: {valueY.low}/avg: {valueY.average}/max: {valueY.high}";


    // Add scrollbar
    vpnchart.scrollbarX = new am4core.Scrollbar();
    
    // Add a legend
    vpnchart.legend = new am4charts.Legend();
    vpnchart.legend.useDefaultMarker = true;
    vpnchart.legend.position = 'bottom';
    vpnchart.legend.contentAlign = "left";

    // Add cursor
    vpnchart.cursor = new am4charts.XYCursor();
    vpnchart.cursor.xAxis = dateAxis;
    vpnchart.cursor.snapToSeries = series;

    vpnchart.exporting.title = '<?php echo $itemname;?>';
    vpnchart.exporting.filePrefix = '<?php echo $itemname;?>'+Date.now();
    vpnchart.exporting.menu = new am4core.ExportMenu();
</script>

<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>
