<?php
  // ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
  
  include 'session.php';

  $timefrom = $_GET['timefrom'] ?? strtotime("today");
  $timetill = $_GET['timetill'] ?? time();
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
?>
<!DOCTYPE html>
<html>
  
  <?php include("head.php"); ?>

  <body class="skin-blue">
    <div class="wrapper">
      
    <?php include('header.php'); ?>
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Dashboard
            <small></small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
          </ol>
        </section>

          <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
              <div class="col-xs-4">
                <!-- small box -->
                <div class="small-box bg-aqua">
                  <div class="inner">
                    <h3><?php
                        $result = $zbx->call('host.get',array("countOutput" => true));
                        print "$result\n";
                        ?></h3>
                    <p>Hosts</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-fw fa-laptop"></i>
                  </div>
                  <a href="hostlist.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div><!-- ./col -->
              <div class="col-xs-4">
                <!-- small box -->
                <div class="small-box bg-red">
                  <div class="inner">
                    <h3>
                      <div id="problems_7days"></div>
                      <script>
                        load7days_problems();
                        var problems_7days = setInterval(load7days_problems, 180000);

                        function load7days_problems() {
                          $("#problems_7days").load("dashboard/card/problems_7days.php");
                        }
                      </script>
                    </h3>
                    <p>Problems (7 days)</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-fw fa-exclamation-circle"></i>
                  </div>
                  <a href="#" onclick="problemspage_7days()" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div><!-- ./col -->
              <div class="col-xs-4">
                <!-- small box -->
                <div class="small-box bg-red">
                  <div class="inner">
                    <h3>
                      <div id="problems_today"></div>
                      <script>
                        loadToday_problems();
                        var problems_today = setInterval(loadToday_problems, 180000);

                        function loadToday_problems() {
                          $("#problems_today").load("dashboard/card/problems_today.php");
                        }
                      </script>
                    </h3>
                    <p>Problems (Today)</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-fw fa-exclamation-circle"></i>
                  </div>
                  <a href="#" onclick="problemspage_today()" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div><!-- ./col -->
            </div><!-- /.row -->

          <div class="row">
            <div class="col-md-12">
              <!-- Custom Tabs -->
              <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" id="tab_dashboard">
                  <li class="active"><a href="#tab_1" data-toggle="tab">Last 100 Problems</a></li>
                  <li><a href="#tab_2" data-toggle="tab">Top 10 - CPU & Memory (Windows)</a></li>
                  <li><a href="#tab_3" data-toggle="tab">Top 10 - System Resources (Windows)</a></li>
                  <li><a href="#tab_4" data-toggle="tab">Top 10 - CPU & Memory (Linux)</a></li>
                  <li><a href="#tab_5" data-toggle="tab">Top 10 - System Resources (Linux)</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_1">
                    <div id="problems">
                        <!-- Loading Gif -->
                        <div class="overlay">
                            <i class="fa fa-refresh fa-spin"></i>
                        </div> 
                    </div>
                    <script>
                        //begin page by start the interval
                        loadProblemsTable();
                        var problems_table = setInterval(loadProblemsTable, 60000);

                        //ajax call to load content                                                                      
                        function loadProblemsTable() {
                            // console.log("refresh table");
                            $("#problems").load("dashboard/problems/problem_table.php");
                        }

                        //if click button acknowledge problem, stop interval
                        function stopIntProblemsTable() {
                            console.log("stop prob int");
                            clearInterval(problems_table);
                        }

                        //if done or close the ack form, start interval back
                        function startIntProblemsTable() {
                            console.log("start prob int");
                            //refresh problem table after 1 sec
                            setTimeout(loadProblemsTable, 1000);
                            problems_table = setInterval(loadProblemsTable, 60000);
                        }
                    </script>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_2">
                    <div id="overview_windows_1">
                      <!-- Loading Gif -->
                      <div class="overlay">
                          <i class="fa fa-refresh fa-spin"></i>
                      </div>
                    </div>
                    <script>
                    //begin page by start the interval
                    loadWindowsOverview1();
                    var overview_windows_1 = setInterval(loadWindowsOverview1, 60000);

                    //ajax call to load content                                                                      
                    function loadWindowsOverview1() {
                      // console.log("refresh table");
                      $("#overview_windows_1").load("dashboard/overview/overview_windows_1.php");
                    }
                    </script>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_3">
                    <div id="overview_windows_2">
                      <!-- Loading Gif -->
                      <div class="overlay">
                          <i class="fa fa-refresh fa-spin"></i>
                      </div>
                    </div>
                    <script>
                    //begin page by start the interval
                    loadWindowsOverview2();
                    var overview_windows_2 = setInterval(loadWindowsOverview2, 60000);

                    //ajax call to load content                                                                      
                    function loadWindowsOverview2() {
                      // console.log("refresh table");
                      $("#overview_windows_2").load("dashboard/overview/overview_windows_2.php");
                    }
                    </script>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_4">
                    <div id="overview_linux_1">
                      <!-- Loading Gif -->
                      <div class="overlay">
                          <i class="fa fa-refresh fa-spin"></i>
                      </div>
                    </div>
                    <script>
                    //begin page by start the interval
                    loadLinuxOverview1();
                    var overview_linux_1 = setInterval(loadLinuxOverview1, 60000);

                    //ajax call to load content                                                                      
                    function loadLinuxOverview1() {
                      // console.log("refresh table");
                      $("#overview_linux_1").load("dashboard/overview/overview_linux_1.php");
                    }
                    </script>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_5">
                    <div id="overview_linux_2">
                      <!-- Loading Gif -->
                      <div class="overlay">
                          <i class="fa fa-refresh fa-spin"></i>
                      </div>
                    </div>
                    <script>
                    //begin page by start the interval
                    loadLinuxOverview2();
                    var overview_linux_2 = setInterval(loadLinuxOverview2, 60000);

                    //ajax call to load content                                                                      
                    function loadLinuxOverview2() {
                      // console.log("refresh table");
                      $("#overview_linux_2").load("dashboard/overview/overview_linux_2.php");
                    }
                    </script>
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
          </div>

        </section>
      </div><!-- ./wrapper -->

      <script>
        function problemspage_today() {
            var newURL = "problems.php";

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function problemspage_7days() {
            var timefrom = moment().subtract(7, 'day').unix();
            var newURL = "problems.php?&timefrom=" + timefrom;

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }
      </script>
      
      <?php
      include("footer.php");
      ?>
    

<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>

<script>
  // var currentTab = 0;

  // var switchtab_int = setInterval(switch_tab, 5000);

  // function switch_tab() {
  //   var tabs = document.querySelectorAll("#tab_dashboard a");
  //   tabs[currentTab].click();
  //   currentTab++;
    
  //   if (currentTab >= tabs.length)
  //     currentTab = 0;
  // }
</script>