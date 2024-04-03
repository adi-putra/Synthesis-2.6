<?php

include 'session.php';

//start page with a group id
//initiate group id
$params = array(
  "output" => array("groupid", "name"),
  "search" => array("name" => "hypervisor"),
);
$result = $zbx->call('hostgroup.get', $params);
foreach ($result as $row) {
  $getgroupid = $row["groupid"];
}

$groupid = $_GET["groupid"] ?? $getgroupid;

//start page with a hosts belong to group id
//initiate host ids
$counthost1 = 0;
$hostids = array();
$params = array(
  "output" => array("hostid", "name"),
  "groupids" => $groupid
);
//call api
$result = $zbx->call('host.get', $params);
foreach ($result as $row) {
  $gethostid = $row["hostid"];
  $hostids[$counthost1] = $gethostid;
  $counthost1++;
}

if ($counthost1 > 30) {
  $hostids = array_slice($hostids, 0, 30);
}

$hostid = $_GET["hostid"] ?? $hostids;

// display count of hostid, if all selected, display "all"
$counthostid = count($hostid);
if ($counthostid == $counthost1) {
  $counthostid = "all";
}

// $hostid = $_GET["hostid"];


if (isset($_POST['submit'])) {
  // Execute this code if the submit button is pressed.
  $timefrom1 = $_POST['timefrom'];
  $timefrom = strtotime($timefrom1);
  $timetill1 = $_POST['timetill'];
  $timetill = strtotime($timetill1);
} else {
  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();
}

//display time format
$diff = $timetill - $timefrom;
if ($diff == 3600) {
  $status = "Last 1 hour";
} else if ($diff < 86400) {
  $status = "Today";
} else if ($diff == 86400) {
  $status = "Last 1 day";
} else if ($diff == 172800) {
  $status = "Last 2 days";
} else if ($diff == 604800) {
  $status = "Last 7 days";
} else if ($diff == 2592000) {
  $status = "Last 30 days";
} else if ($diff == 15638400) {
  $status = "Last 6 months";
}

// Format datetime to d/m/y h:i A
$formattimefrom = date("d/m/y h:i A", $timefrom);
$formattimetill = date("d/m/y h:i A", $timetill);


function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

function formatBytes($bytes, $precision = 2)
{
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
<?php include('head.php'); ?>

<body class="skin-blue">
  <div class="wrapper">

    <?php include('header.php'); ?>

    <?php include('sidebar.php'); ?>

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Hypervisor
          <small>Overview</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
        <br>

        <?php
         //display time format string
         if ($_GET["timefrom"] == "" AND $_GET["timetill"] == "") {
           $time_str = "Today";
         }
         else if ($_GET["timetill"] == "") {
           $time_str = date("d/m/Y H:i A", $timefrom)." - Now";
         }
         else {
            $time_str = date("d/m/Y H:i A", $timefrom)." - ".date("d/m/Y H:i A", $timetill);
         }


        //get current group name
        $params = array(
          "output" => array("name"),
          "groupids" => $groupid
        );
        //call api
        $result = $zbx->call('hostgroup.get', $params);
        foreach ($result as $row) {
          $groupname = $row["name"];
        }
        ?>

        <?php
        $counthost = 0;
        $hostArr = "";
        if (!empty($hostid)) {
          foreach ($hostid as $hostID) {
            $hostArr .= "hostid[]=" . $hostID . "&";
            $counthost++;
          }
        }

        if ($counthost < 30) {
          $chgDateEnable = "display: all;";
        } else {
          $chgDateEnable = "display: none;";
        }
        $formlink = "overview_hypervisor.php?" . $hostArr;

        $timerange = "&timefrom=" . $timefrom . "&timetill=" . $timetill;
        ?>

        <!--<form action="overview_hypervisor.php?<?php echo $hostArr; ?>timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" method="post">-->


        <!--</form>-->

        <?php
        $hostTB = "";
        $params = array(
          "output" => array("name"),
          "hostids" => $hostid
        );
        //call api
        $result = $zbx->call('host.get', $params);
        foreach ($result as $row) {
          $hostTB .= $row["name"] . " + ";
        }
        $hostTB = substr($hostTB, 0, -3);


        //get current group name
        $params = array(
          "output" => array("name"),
          "groupids" => $groupid
        );
        //call api
        $result = $zbx->call('hostgroup.get', $params);
        foreach ($result as $row) {
          $groupname = $row["name"];
        }
        ?>

      </section>

      <!-- Main content -->
      <section class="content">

      <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs (Pulled to the right) -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs pull-right">
                    <li><a data-target="#tab_1-1" data-toggle="tab"><i class="fa fa-clock"></i> <?php echo $time_str; ?></a></li>
                    <li class="active"><a data-target="#tab_2-2" data-toggle="tab"><i class="fa fa-filter"></i> Filter</a></li>
                    </ul>
                    <div class="tab-content">
                    <div class="tab-pane" id="tab_1-1">
                        <div class="row">
                            <div class="col-xs-12 col-md-4 col-lg-4">
                                <div class="form-group">
                                    <div class='input-group date' id='datetimepicker6'>
                                        <span class="input-group-addon to-from">From:</span>
                                        <input type='text' class="form-control" name="timefrom" placeholder="From:" value="<?php echo date('d-m-Y h:i A', $timefrom); ?>" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    <br>
                                    <div class='input-group date' id='datetimepicker7'>
                                        <span class="input-group-addon to-from">To:</span>
                                        <input type='text' class="form-control" name="timetill" placeholder="To:" value="<?php echo date('d-m-Y h:i A', $timetill); ?>" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    <br>
                                    <button class="btn btn-block bg-green" onclick="changeDateTime();">Apply</button>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-8 col-lg-8" id="btn-wrapper" style="justify-content: flex-end;display: flex;">
                                <div class="col-xs-12">
                                    <button class="btn btn-block btn-primary btn-xs" onclick="today();">Today</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="yesterday();">Yesterday</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="thisweek();">This week</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="thismonth();">This month</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="thisyear();">This year</button>
                                </div>
                                <div class="col-xs-12">
                                    <button class="btn btn-block btn-primary btn-xs" onclick="daybeforeyesterday();">Day before yesterday</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="prevweek();">Previous week</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="prehypervisoronth();">Previous month</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="prevyear();">Previous year</button>
                                </div>
                                <div class="col-xs-12">
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last1day();">Last 1 day</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last2days();">Last 2 days</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last7days();">Last 7 days</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last30days();">Last 30 days</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last3months();">Last 3 months</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last6months();">Last 6 months</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last1year();">Last 1 year</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last2years();">Last 2 years</button>
                                </div>
                                <div class="col-xs-12">
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last5min();">Last 5 minutes</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last15min();">Last 15 minutes</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last30min();">Last 30 minutes</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last1hour();">Last 1 hour</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last3hour();">Last 3 hours</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last6hour();">Last 6 hours</button>
                                    <button class="btn btn-block btn-primary btn-xs" onclick="last12hour();">Last 12 hours</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    //get host name and display in tbHost
                    $tbHostVal = "";
                    if (!empty($hostid)) {
                        //counter
                        $counthost2 = 0;
                        foreach ($hostid as $hostID) {
                            $params = array(
                                "output" => array("hostid", "name"),
                                "hostids" => $hostID
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('host.get',$params);
                            foreach ($result as $host) {
                                $tbHostVal .= $host["name"].", ";
                                $counthost2++;
                            }
                        }
                        if ($counthost1 == $counthost2) {
                          $tbHostVal = "All";
                        }
                        else {
                          $tbHostVal = rtrim($tbHostVal, ", ");
                        }
                    }
                    else {
                        $tbHostVal = "All";
                    }
                    
                    ?>
                    <!-- /.tab-pane -->
                    <div class="tab-pane active" id="tab_2-2">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-hover">
                                    <tr>
                                        <td>Host Groups</td>
                                        <td>
                                            <!-- select group -->
                                            <div class="form-group" style="display: inline-block;">
                                              <select class="form-control" onchange="location = this.value;" style="width: auto;">
                                                <option><?php echo $groupname; ?></option>
                                                <?php
                                                $params = array(
                                                  "output" => array("groupid", "name"),
                                                  "search" => array("name" => "hypervisor"),
                                                );
                                                $result = $zbx->call('hostgroup.get', $params);
                                                foreach ($result as $row) {
                                                  if ($row["name"] != $groupname) {
                                                    $getgroupid = $row["groupid"];
                                                    $getgroupname = $row["name"];
                                                  } else {
                                                    continue;
                                                  }
                                                  print "<option value='overview_hypervisor.php?groupid=" . $getgroupid . "'>$getgroupname</option>";
                                                }
                                                ?>
                                              </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Hosts</td>
                                        <td>
                                            <textarea cols="50" rows="5" id="tbHost" class="form-control" type="text" name="host" disabled><?php echo $tbHostVal; ?></textarea><br>
                                            <p><i>Selected <?php echo $counthostid; ?> hosts.</i></p>
                                            <p><i>Click 'Select' here to add/remove hosts.</i></p>
                                            <button id="hostmodal_btn" class="btn btn-primary" data-toggle="modal" data-target="#hostModal" type="button">Select</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div><!-- /.tab-pane -->
                    </div><!-- /.tab-content -->
                </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
        </div> <!-- /.row -->

        <!-- Content -->
        <div class="row" id="mycontent">
          <div class="col-lg-2 col-md-3 col-xs-12 d-block" id="mysidebar">
            <div class="box collapsed-box">
              <div class="box-header with-border">
                <h3 class="box-title">Filters</h3>
                <div class="box-tools pull-right">
                  <button class="btn btn-box-tool" data-toggle="tooltip" title="Collapse" onclick="closeMysidebar()"><i class="fa fa-close"></i></button>
                </div>
              </div>
              <div class="box-body show">
                <ul class="nav nav-pills nav-stacked overview-nav">
                  <li><input type="checkbox" class="upper-box" checked>
                    <p>Select All</p>
                  </li>
                  <div class="sub-group">
                    <li class="active"><input type="checkbox" class="sub-box" value="hypervisor-problems" checked><a href="#hypervisor-problems">Problems</a></li>
                    <!-- <li><input type="checkbox" class="sub-box" value="hypervisor-about" checked><a href="#hypervisor-about">About</a></li> -->
                    <li><input type="checkbox" class="sub-box" value="hypervisor-uptime" checked><a href="#hypervisor-uptime">Uptime</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-cpuusage" checked><a href="#hypervisor-cpuusage">CPU Usage</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-memusage" checked><a href="#hypervisor-memusage">Memory Usage</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-freedisk" checked><a href="#hypervisor-freedisk">Free Disk Space</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-cpuusagegraph" checked><a href="#hypervisor-cpuusagegraph">CPU Usage Graph</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-memusagegraph" checked><a href="#hypervisor-memusagegraph">Memory Usage Graph</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-readlatencygraph" checked><a href="#hypervisor-readlatencygraph">Average Read Latency Graph</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-writelatencygraph" checked><a href="#hypervisor-writelatencygraph">Average Write Latency Graph</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-networkutilgraph" checked><a href="#hypervisor-networkutilgraph">Network Utilization Graph</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-bytesrecgraph" checked><a href="#hypervisor-bytesrecgraph">Bytes Received Graph</a></li>
                    <li><input type="checkbox" class="sub-box" value="hypervisor-bytestransmitgraph" checked><a href="#hypervisor-bytestransmitgraph">Bytes Transmitted Graph</a></li>
                  </div>
                </ul>
              </div>
            </div>
          </div>

          <div id="sidebar-collapse" class="close-sidebar">
            <div class="box-header with-border">
              <h3 class="box-title"><a class="btn btn-primary" onclick="openMysidebar()">Filters</a></h3>
            </div>
          </div>

          <div class="content-graph-wrapper">
            <div class="col-lg-10 col-md-9 col-xs-12 wrapper-1 d-block" style="float: right;">

              <div class="box-body">
                <div class="row" id="hypervisor-problems">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Problems</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                          <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-body">
                                      <!-- small box -->
                                      <div class="row">
                                          <div class="col-md-2">
                                              <div class="small-box bg-red">
                                                  <div class="inner">
                                                      <h3 style="text-align: center;">
                                                      <div id="count_disaster">0</div>
                                                      </h3>
                                                  </div>
                                                  <a href="javascript:;" onclick="searchTableDisaster()" class="small-box-footer">
                                                      Disaster
                                                  </a>
                                              </div>
                                          </div>
                                          <div class="col-md-2">
                                              <div class="small-box" style="background-color: tomato; color: white;">
                                                  <div class="inner">
                                                      <h3 style="text-align: center;">
                                                      <div id="count_high">0</div>
                                                      </h3>
                                                  </div>
                                                  <a href="javascript:;" onclick="searchTableHigh()" class="small-box-footer">
                                                      High
                                                  </a>
                                              </div>
                                          </div>
                                          <div class="col-md-2">
                                              <div class="small-box" style="background-color: #ff6600; color: white;">
                                                  <div class="inner">
                                                      <h3 style="text-align: center;">
                                                      <div id="count_average">0</div>
                                                      </h3>
                                                  </div>
                                                  <a href="javascript:;" onclick="searchTableAverage()" class="small-box-footer">
                                                      Average
                                                  </a>
                                              </div>
                                          </div>
                                          <div class="col-md-2">
                                              <div class="small-box" style="background-color: orange; color: white;">
                                                  <div class="inner">
                                                      <h3 style="text-align: center;">
                                                      <div id="count_warning">0</div>
                                                      </h3>
                                                  </div>
                                                  <a href="javascript:;" onclick="searchTableWarning()" class="small-box-footer">
                                                      Warning
                                                  </a>
                                              </div>
                                          </div>
                                          <div class="col-md-2">
                                              <div class="small-box bg-aqua">
                                                  <div class="inner">
                                                      <h3 style="text-align: center;">
                                                      <div id="count_info">0</div>
                                                      </h3>
                                                  </div>
                                                  <a href="javascript:;" onclick="searchTableInfo()" class="small-box-footer">
                                                      Info
                                                  </a>
                                              </div>
                                          </div>
                                          <div class="col-md-2">
                                              <div class="small-box" style="background-color: gray; color: white;">
                                                  <div class="inner">
                                                      <h3 style="text-align: center;">
                                                      <div id="count_unclass">0</div>
                                                      </h3>
                                                  </div>
                                                  <a href="javascript:;" onclick="searchTableUnclassified()" class="small-box-footer">
                                                      Unclassified
                                                  </a>
                                              </div>
                                          </div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                          <div class="row">
                            <div class="col-md-12">
                              <div id="problems_table"></div>
                              <script>
                                var counthost = <?php echo $counthost; ?>;
                                var hostArr = '<?php echo $hostArr; ?>';
                                var timefrom = '<?php echo $_GET["timefrom"]; ?>';
                                var timetill = '<?php echo $_GET["timetill"]; ?>';
                                var groupid = '<?php echo $groupid."&"; ?>';
                                var xhr_arr = [];

                                if (timefrom == "" && timetill == "") {
                                  var timerange = "";
                                  var diff = 0;
                                }
                                else if (timetill == "") {
                                  var timerange = "&timefrom=" + timefrom;
                                  var currtime = moment().unix();
                                  var diff = currtime - timefrom;
                                }
                                else {
                                  var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
                                  var diff = timetill - timefrom;
                                }

                                //alert(diff);

                                  //begin page by start the interval
                                  loadProblemsTable();
                                  // if (diff <= 604800 && timetill == "") {
                                  //   var problems_table = setTimeout(loadProblemsTable, 60000);
                                  // }

                                  //ajax call to load content                                                                      
                                  function loadProblemsTable() {
                                      //$("#problem_sev_info").load("problems/problem_sev_info.php?" + groupid + hostid + timerange);
                                      // $("#problems_table").load("problems/problem_table.php?groupid[]=" + groupid + hostArr + timerange);
                                      problems_table_xhr = $.ajax({
                                        url: "problems/problem_table.php?groupid[]=" + groupid + hostArr + timerange, 
                                        success: function(result) {
                                          $("#problems_table").html(result);
                                        },
                                        complete: function() {
                                          if (diff <= 604800 && timetill == "") {
                                            // var problems_table = setTimeout(loadProblemsTable, 60000);
                                            setTimeout(loadProblemsTable, 60000);
                                          }
                                          // setTimeout(loadProblemsTable, 60000);
                                        }
                                      });
                                  }

                                  //if click button acknowledge problem, stop interval
                                  function stopIntProblemsTable() {
                                      console.log("stop prob int");
                                      clearTimeout(problems_table);
                                  }

                                  //if done or close the ack form, start interval back
                                  function startIntProblemsTable() {
                                      console.log("start prob int");
                                      //refresh problem table after 1 sec
                                      // setTimeout(loadProblemsTable, 1000);
                                      // problems_table = setTimeout(loadProblemsTable, 60000);
                                      if (diff <= 604800 && timetill == "") {
                                        // var problems_table = setTimeout(loadProblemsTable, 60000);
                                        setTimeout(loadProblemsTable, 60000);
                                      }
                                  }
                              </script>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-about">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">About</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="loadabout"></div>
                        <script>
                          loadAbout();
                          // if (diff <= 604800 && timetill == "") {
                          //   var getabout = setTimeout(loadAbout, 60000);
                          // }
                          function loadAbout() {      
                            //console.log("load");           
                            // $("#loadabout").load("ajax/hypervisor/getabout.php?groupid=" + groupid + "&" + hostArr);
                            getabout_xhr = $.ajax({
                              url: "ajax/hypervisor/getabout.php?groupid=" + groupid + hostArr + timerange, 
                              success: function(result) {
                                $("#loadabout").html(result);
                              },
                              complete: function() {
                                if (diff <= 604800 && timetill == "") {
                                  // var problems_table = setTimeout(loadProblemsTable, 60000);
                                  setTimeout(loadAbout, 60000);
                                }
                                // setTimeout(loadProblemsTable, 60000);
                              }
                            });
                          }
                        </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-uptime">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Uptime</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="getuptime"></div>        
                          <script>
                            loadUptime();
                            // if (diff <= 604800 && timetill == "") {
                            //   var getuptime = setTimeout(loadUptime, 60000);
                            // }
                            function loadUptime() {      
                              //console.log("load");           
                              // $("#getuptime").load("ajax/hypervisor/getuptime.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              getuptime_xhr = $.ajax({
                                url: "ajax/hypervisor/getuptime.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#getuptime").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadUptime, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-cpuusage">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: CPU Usage</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="cpu_usage"></div>        
                          <script>
                            loadCPUUsage();
                            // if (diff <= 604800 && timetill == "") {
                            //   var getuptime = setTimeout(loadUptime, 60000);
                            // }
                            function loadCPUUsage() {      
                              //console.log("load");           
                              // $("#cpu_usage").load("ajax/hypervisor/cpu_usage.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              cpu_usage_xhr = $.ajax({
                                url: "ajax/hypervisor/cpu_usage.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#cpu_usage").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadCPUUsage, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- <div class="row" id="hypervisor-memusage">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Memory Usage</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="mem_usage"></div>        
                          <script>
                            // loadMemUsage();
                            // // if (diff <= 604800 && timetill == "") {
                            // //   var mem_usage = setTimeout(loadMemUsage, 60000);
                            // // }
                            // function loadMemUsage() {      
                            //   //console.log("load");           
                            //   // $("#mem_usage").load("ajax/hypervisor/mem_usage.php?groupid=" + groupid + "&" + hostArr + timerange);     
                            //   mem_usage_xhr = $.ajax({
                            //     url: "ajax/hypervisor/mem_usage.php?groupid=" + groupid + hostArr + timerange, 
                            //     success: function(result) {
                            //       $("#mem_usage").html(result);
                            //     },
                            //     complete: function() {
                            //       if (diff <= 604800 && timetill == "") {
                            //         // var problems_table = setTimeout(loadProblemsTable, 60000);
                            //         setTimeout(loadMemUsage, 60000);
                            //       }
                            //       // setTimeout(loadProblemsTable, 60000);
                            //     }
                            //   });
                            // }
                          </script>
                      </div>
                    </div>
                  </div>
                </div> -->

                <div class="row" id="hypervisor-freedisk">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Free Disk Space</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="getdisk"></div>
                        <script>
                          loadFreeDiskSpace();
                          // if (diff <= 604800 && timetill == "") {
                          //   var getdisk = setTimeout(loadFreeDiskSpace, 60000);
                          // }
                          function loadFreeDiskSpace() { 
                            // $("#getdisk").load("ajax/hypervisor/getdisk.php?groupid=" + groupid + "&" + hostArr + timerange);
                            getdisk_xhr = $.ajax({
                              url: "ajax/hypervisor/getdisk.php?groupid=" + groupid + hostArr + timerange, 
                              success: function(result) {
                                $("#getdisk").html(result);
                              },
                              complete: function() {
                                if (diff <= 604800 && timetill == "") {
                                  // var problems_table = setTimeout(loadProblemsTable, 60000);
                                  setTimeout(loadFreeDiskSpace, 60000);
                                }
                                // setTimeout(loadProblemsTable, 60000);
                              }
                            });
                          }
                        </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-cpuusagegraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: CPU Usage Graph</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="getcpugraph"></div>        
                          <script>
                            loadCPUUsageGraph();
                            // if (diff <= 604800 && timetill == "") {
                            //   var getcpugraph = setTimeout(loadCPUUsageGraph, 60000);
                            // }
                            function loadCPUUsageGraph() {      
                              //console.log("load");           
                              // $("#getcpugraph").load("ajax/hypervisor/getcpugraph.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              getcpugraph_xhr = $.ajax({
                                url: "ajax/hypervisor/getcpugraph.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#getcpugraph").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadCPUUsageGraph, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-memusagegraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Memory Usage Graph</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="getmemgraph"></div>        
                          <script>
                            loadMemUsageGraph();
                            // if (diff <= 604800 && timetill == "") {
                            //   var getmemgraph = setTimeout(loadMemUsageGraph, 60000);
                            // }
                            function loadMemUsageGraph() {      
                              //console.log("load");           
                              // $("#getmemgraph").load("ajax/hypervisor/getmemgraph.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              getmemgraph_xhr = $.ajax({
                                url: "ajax/hypervisor/getmemgraph.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#getmemgraph").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadMemUsageGraph, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-readlatencygraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Average read latency to the disk</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="read_latency"></div>        
                          <script>
                            loadReadLatency();
                            // if (diff <= 604800 && timetill == "") {
                            //   var read_latency = setTimeout(loadReadLatency, 60000);
                            // }
                            function loadReadLatency() {      
                              //console.log("load");           
                              // $("#read_latency").load("ajax/hypervisor/read_latency.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              read_latency_xhr = $.ajax({
                                url: "ajax/hypervisor/read_latency.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#read_latency").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadReadLatency, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-writelatencygraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Average write latency to the disk </h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="write_latency"></div>        
                          <script>
                            loadWriteLatency();
                            // if (diff <= 604800 && timetill == "") {
                            //   var write_latency = setTimeout(loadWriteLatency, 60000);
                            // }
                            function loadWriteLatency() {      
                              //console.log("load");           
                              // $("#write_latency").load("ajax/hypervisor/write_latency.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              write_latency_xhr = $.ajax({
                                url: "ajax/hypervisor/write_latency.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#write_latency").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadWriteLatency, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- <div class="row" id="hypervisor-networkutilgraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Network Utilization on Interface</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="network_util"></div>        
                          <script>
                            // loadNetworkUtil();
                            // // if (diff <= 604800 && timetill == "") {
                            // //   var network_util = setTimeout(loadNetworkUtil, 60000);
                            // // }
                            // function loadNetworkUtil() {      
                            //   //console.log("load");           
                            //   // $("#network_util").load("ajax/hypervisor/network_util.php?groupid=" + groupid + "&" + hostArr + timerange);     
                            //   network_util_xhr = $.ajax({
                            //     url: "ajax/hypervisor/network_util.php?groupid=" + groupid + hostArr + timerange, 
                            //     success: function(result) {
                            //       $("#network_util").html(result);
                            //     },
                            //     complete: function() {
                            //       if (diff <= 604800 && timetill == "") {
                            //         // var problems_table = setTimeout(loadProblemsTable, 60000);
                            //         setTimeout(loadNetworkUtil, 60000);
                            //       }
                            //       // setTimeout(loadProblemsTable, 60000);
                            //     }
                            //   });
                            // }
                          </script>
                      </div>
                    </div>
                  </div>
                </div> -->

                <div class="row" id="hypervisor-bytesrecgraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Number of Bytes Received on Interface</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="bytes_received"></div>        
                          <script>
                            loadBytesRec();
                            // if (diff <= 604800 && timetill == "") {
                            //   var bytes_received = setTimeout(loadBytesRec, 60000);
                            // }
                            function loadBytesRec() {      
                              //console.log("load");           
                              // $("#bytes_received").load("ajax/hypervisor/bytes_received.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              bytes_received_xhr = $.ajax({
                                url: "ajax/hypervisor/bytes_received.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#bytes_received").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadBytesRec, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-bytestransmitgraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Top 10: Number of Bytes Transmitted on Interface</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="bytes_transmit"></div>        
                          <script>
                            loadBytesTransmit();
                            // if (diff <= 604800 && timetill == "") {
                            //   var bytes_transmit = setTimeout(loadBytesTransmit, 60000);
                            // }

                            function loadBytesTransmit() {      
                              // console.log("load");           
                              // $("#bytes_transmit").load("ajax/hypervisor/bytes_transmit.php?groupid=" + groupid + "&" + hostArr + timerange);     
                              bytes_transmit_xhr = $.ajax({
                                url: "ajax/hypervisor/bytes_transmit.php?groupid=" + groupid + hostArr + timerange, 
                                success: function(result) {
                                  $("#bytes_transmit").html(result);
                                },
                                complete: function() {
                                  if (diff <= 604800 && timetill == "") {
                                    // var problems_table = setTimeout(loadProblemsTable, 60000);
                                    setTimeout(loadBytesTransmit, 60000);
                                  }
                                  // setTimeout(loadProblemsTable, 60000);
                                }
                              });
                            }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- <div class="row" id="hypervisor-packetsrecgraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Number of Packets Received on Interface</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="packets_received"></div>        
                          <script>
                            // loadPacketRec();
                            // if (diff <= 604800 && timetill == "") {
                            //   var packets_received = setTimeout(loadPacketRec, 60000);
                            // }
                            // function loadPacketRec() {      
                            //   //console.log("load");           
                            //   $("#packets_received").load("ajax/hypervisor/packets_received.php?groupid=" + groupid + "&" + hostArr + timerange);     
                            // }
                          </script>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row" id="hypervisor-packetstransmitgraph">
                  <div class="col-md-12">
                    <div class="box box-solid box-default">
                      <div class="box-header">
                        <h3 class="box-title">Number of Packets Transmitted on Interface</h3>
                        <div class="box-tools pull-right">
                          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        </div>
                      </div>
                      <div class="box-body">
                        <div id="packets_transmit"></div>        
                          <script>
                            // loadPacketTransmit();
                            // if (diff <= 604800 && timetill == "") {
                            //   var packets_transmit = setTimeout(loadPacketTransmit, 60000);
                            // }
                            // function loadPacketTransmit() {      
                            //   //console.log("load");           
                            //   $("#packets_transmit").load("ajax/hypervisor/packets_transmit.php?groupid=" + groupid + "&" + hostArr + timerange);     
                            // }
                          </script>
                      </div>
                    </div>
                  </div>
                </div> -->

              </div>
            </div>
          </div>
        </div>




      </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
    <?php include('footer.php'); ?>

  </div><!-- ./wrapper -->

  <!-- Date Range Buttons script -->
  <script>
        function changeDateTime() {
            var gettimefrom = document.getElementsByName("timefrom")[0].value;
            var gettimetill = document.getElementsByName("timetill")[0].value;
            //if value is blank
            if (gettimefrom === "" || gettimetill === "") {
                alert("Zero/Wrong inputs detected!");
                return false;
            } 
            else {
                var timefrom = moment(gettimefrom, "D-M-Y hh:mm A").unix();
                var timetill = moment(gettimetill, "D-M-Y hh:mm A").unix();
                
                //get current URL
                var currentURL = document.URL;
                
                //trim and fine the position in url string to cut timefrom and timetil
                var stringPos = currentURL.search("timefrom");
                
                //if no timefrom in url string
                
                if (stringPos == -1) {
                  var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
                } 
                else {
                  var trimmedUrl = currentURL.substr(0, stringPos-1);

                  //declare new URL together with new values
                  var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
                }

                //alert(hostUrl);
                //submit the form by reopening the window with new values 
                location.assign(newURL);
            }
        }

        function today() {
            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");
            
            var newURL = currentURL.substr(0, stringPos-1);

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function yesterday() {
            var timefrom = moment().startOf('day').subtract(1, 'day').unix();
            var timetill = moment().startOf('day').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last1day() {
            var timefrom = moment().subtract(1, 'day').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last2days() {
            var timefrom = moment().subtract(2, 'days').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last7days() {
            var timefrom = moment().subtract(7, 'days').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last30days() {
            var timefrom = moment().subtract(30, 'days').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last3months() {
            var timefrom = moment().subtract(3, 'months').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last6months() {
            var timefrom = moment().subtract(6, 'months').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last1year() {
            var timefrom = moment().subtract(1, 'year').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last2years() {
            var timefrom = moment().subtract(2, 'years').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function daybeforeyesterday() {
            var timefrom = moment().startOf('day').subtract(2, "days").unix();
            var timetill = moment().subtract(2, 'days').unix();
            
            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function prevweek() {
            var timefrom = moment().subtract(1, 'weeks').startOf('week').unix();
            var timetill = moment().subtract(1, 'weeks').endOf('week').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function prehypervisoronth() {
            var timefrom = moment().subtract(1, 'months').startOf('month').unix();
            var timetill = moment().subtract(1, 'months').endOf('month').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function prevyear() {
            var timefrom = moment().subtract(1, 'years').startOf('year').unix();
            var timetill = moment().subtract(1, 'years').endOf('year').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }


        function thisweek() {
            var timefrom = moment().startOf('week').unix();
            var timetill = moment().endOf('week').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function thismonth() {
            var timefrom = moment().startOf('month').unix();
            var timetill = moment().endOf('month').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function thisyear() {
            var timefrom = moment().startOf('year').unix();
            var timetill = moment().endOf('year').unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last5min() {
            var timefrom = moment().subtract(5, 'minutes').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last15min() {
            var timefrom = moment().subtract(15, 'minutes').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last30min() {
            var timefrom = moment().subtract(30, 'minutes').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last1hour() {
            var timefrom = moment().subtract(1, 'hour').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last3hour() {
            var timefrom = moment().subtract(3, 'hours').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last6hour() {
            var timefrom = moment().subtract(6, 'hours').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }

        function last12hour() {
            var timefrom = moment().subtract(12, 'hours').unix();
            //var timetill = moment().unix();

            //get current URL
            var currentURL = document.URL;

            //trim and fine the position in url string to cut timefrom and timetill
            var stringPos = currentURL.search("timefrom");

            //if no timefrom in url string
            if (stringPos == -1) {
              var newURL = currentURL + "&timefrom=" + timefrom;
            } 
            else {
              var trimmedUrl = currentURL.substr(0, stringPos-1);

              //declare new URL together with new values
              var newURL = trimmedUrl + "&timefrom=" + timefrom;
            }

            //alert(hostUrl);
            //submit the form by reopening the window with new values 
            location.assign(newURL);
        }
    </script>

  <script type="text/javascript">
    $(function() {
      $('#datetimepicker6').datetimepicker({
        format: "DD-MM-YYYY hh:mm A"
      });
      $('#datetimepicker7').datetimepicker({
        format: "DD-MM-YYYY hh:mm A"
      });

      $('#datetimepicker8').datetimepicker({
        format: "DD-MM-YYYY hh:mm A"
      });
      $('#datetimepicker9').datetimepicker({
        format: "DD-MM-YYYY hh:mm A"
      });
      $("#datetimepicker6").on("dp.change", function(e) {
        $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
      });
      $("#datetimepicker7").on("dp.change", function(e) {
        $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
      });

      $("#datetimepicker8").on("dp.change", function(e) {
        $('#datetimepicker9').data("DateTimePicker").minDate(e.date);
      });
      $("#datetimepicker9").on("dp.change", function(e) {
        $('#datetimepicker8').data("DateTimePicker").maxDate(e.date);
      });
    });
  </script>

  <!-- Host List table -->
  <script type="text/javascript">
    $(document).ready(function() {
      var table = $('#example').DataTable({
        'order': [1, 'asc']
      });

      // Handle click on "Select all" control
      $('#example-select-all').on('click', function() {
        // Check/uncheck all checkboxes in the table
        var rows = table.rows({
          'search': 'applied'
        }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
      });

      // Handle click on checkbox to set state of "Select all" control
      $('#example tbody').on('change', 'input[type="checkbox"]', function() {
        // If checkbox is not checked
        if (!this.checked) {
          var el = $('#example-select-all').get(0);
          // If "Select all" control is checked and has 'indeterminate' property
          if (el && el.checked && ('indeterminate' in el)) {
            // Set visual state of "Select all" control 
            // as 'indeterminate'
            el.indeterminate = true;
          }
        }
      });

      $('#frm-example').on('submit', function(e) {
        var form = this;

        // Iterate over all checkboxes in the table
        table.$('input[type="checkbox"]').each(function() {
          // If checkbox doesn't exist in DOM
          if (!$.contains(document, this)) {
            // If checkbox is checked
            if (this.checked) {
              // Create a hidden element 
              $(form).append(
                $('<input>')
                .attr('type', 'hidden')
                .attr('name', this.name)
                .val(this.value)
              );
            }
          }
        });
      });
    });
  </script>

  <script type="text/javascript">
    //Smooth Scroll on Filters
    // handle links with @href started with '#' only
    $(document).on('click', 'a[href^="#"]', function(e) {
      // target element id
      var id = $(this).attr('href');

      // target element
      var $id = $(id);
      if ($id.length === 0) {
        return;
      }

      // prevent standard hash navigation (avoid blinking in IE)
      e.preventDefault();

      // top position relative to the document
      var pos = $id.offset().top;

      // animated top scrolling
      $('body, html').animate({
        scrollTop: pos
      });
    });

    //set sticky sidebar fixed position on scroll
    window.onscroll = function() {
      addSticky()
    };

    var navbar = document.querySelector("#mysidebar");
    var navbarClose = document.querySelector("#sidebar-collapse");
    var overviewContent = document.querySelector(".wrapper-1");
    var wrapperContent = document.querySelector(".content-graph-wrapper");
    var sticky = navbar.offsetTop;

    function addSticky() {
      if (window.pageYOffset >= sticky) {
        navbar.classList.add("sticky");
        navbarClose.classList.add("sticky");
      } else {
        navbar.classList.remove("sticky");
        navbarClose.classList.remove("sticky");
      }
    }

    function closeMysidebar() {
      navbarClose.classList.remove("close-sidebar");
      navbarClose.classList.add("open-sidebar");
      navbar.classList.add("close-sidebar");
      navbar.classList.remove("open-sidebar");
      overviewContent.classList.add("col-md-12");
      overviewContent.classList.remove("col-md-9");
      overviewContent.classList.add("col-lg-12");
      overviewContent.classList.remove("col-lg-10");
    }

    function openMysidebar() {
      navbarClose.classList.remove("open-sidebar");
      navbarClose.classList.add("close-sidebar");
      navbar.classList.remove("close-sidebar");
      navbar.classList.add("open-sidebar");
      overviewContent.classList.add("col-md-9");
      overviewContent.classList.remove("col-md-12");
      overviewContent.classList.add("col-lg-10");
      overviewContent.classList.remove("col-lg-12");
    }

    $('.sub-group li').click(function() {
      $(this).addClass('active');
      $(this).siblings().removeClass('active');
    });

    // Toggle checkbox to hide or show rows
    $(document).ready(function() {
      // ref: https://www.geeksforgeeks.org/how-to-show-and-hide-div-elements-using-checkboxes/ , http://jsfiddle.net/scmd13np/1/ , https://www.tutorialrepublic.com/faq/how-to-check-a-checkbox-is-checked-or-not-using-jquery.php

      // When Upper checkbox change
      $(".upper-box").on("change", function() {
        var $this = $(this); // $this is .upper-box

        // Get all values from each .sub-box using each() and toggle display show/hide with toggle("fast")
        $('.sub-box').each(function() {
          var inputValue = $(this).attr("value"); // $this is .sub-box value
          $("#" + inputValue).toggle("fast");
          if ($this.prop("checked") == true) {
            $("#" + inputValue).css("display", "block");
          } else if ($this.prop("checked") == false) {
            $("#" + inputValue).css("display", "none");
          }
        });
        // Get all .sub-box and prop/add "checked" on all .sub-box, including .upper-box
        $("div.sub-group").find(".sub-box").prop("checked", $this.prop("checked"))
      });

      // When Sub checkbox Box change
      $('.sub-box').on("change", function() {
        lenCheck = $(".sub-group").find("input:checkbox").length; // Get total number of checkboxes within sub group
        lenChecked = $(".sub-group").find("input:checked").length; // Get total number of "CHECKED" checkboxes within sub group

        if (lenCheck == lenChecked) { // If the total number matched with checked then prop "checked" to true (indeterminate is "-")
          $("input.upper-box").prop("indeterminate", false).prop("checked", true);
        } else if (lenChecked == 0) { // If none is checked then prop "checked" to false
          $("input.upper-box").prop("indeterminate", false).prop("checked", false);
        } else {
          $("input.upper-box").prop("indeterminate", true);
        }

        var inputValue = $(this).attr("value"); // $this is .sub-box value
        $("#" + inputValue).toggle("fast");
      });
    });
  </script>

  <!-- Host  Modal -->
  <div class="modal fade" id="hostModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="example-modal">
    <div class="modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" onclick="resetHostForm()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Select Host Group</h4>
          </div>
          <div class="modal-body">
            <form id="hostForm">
            <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
            <!-- <p><i>To select "All", click "Apply" wtihout checking on the checkboxes</i></p> -->
            <?php
            //get current groupid
            if (!empty($groupid)) {
                foreach($groupid as $value) {
                    echo '<input type="hidden" name="groupid[]" value="'. $value. '">';
                }
            }
            ?>

            <!-- Select All hosts cb -->
            <input type="checkbox" id="checkall_hostid"> Select All</th>

            <table class="table table-bordered table-hover" id="hostmodal_table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                //get hostid and name
                $params = array(
                    "output" => array("hostid", "name"),
                    "groupids" => $groupid
                );
                //call api problem.get only to get eventid
                $result = $zbx->call('host.get',$params);
                foreach ($result as $host) {
                    $gethostid = $host["hostid"];
                    $gethostname = $host["name"];

                    $host_checked = "";
                    $host_checked_bool = 0;
                    if (in_array($gethostid, $hostid)) {
                      $host_checked = "checked";
                      $host_checked_bool = 1;
                    }

                    print "<tr>";
                    print "<td><input type='checkbox' name='hostid[]' value='$gethostid' $host_checked><p style='visibility: hidden; display: none;'>".$host_checked_bool."</p></td>";
                    print "<td>$gethostname</td>";
                    print "</tr>";
                }
                ?>
                </tbody>
            </table>
          </div>
            <input type="hidden" name="timefrom" value="<?php echo $timefrom; ?>">
            <input type="hidden" name="timetill" value="<?php echo $timetill; ?>">
          <div class="modal-footer">
            <button type="button" onclick="resetHostForm()" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Apply</button>
          </div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </div><!-- /.example-modal -->
</div>

<script>
//reset group form
function resetGroupForm(){
    $('#groupForm').trigger("reset");
}
//reset host form
function resetHostForm(){
    $('#hostForm').trigger("reset");
}

$(function() {
  $('#hostmodal_table').DataTable({
    "order": [
      [0, "desc"]
    ],
    // 'columnDefs': [ {
    //     'targets': [0, 1], /* column index */
    //     'orderable': false, /* true or false */
    //  }],
    "scrollY": '400px',
    "scrollCollapse": true,
    "paging": false
  });
});

$("#checkall_hostid").click(function () {
  $("input:checkbox[name='hostid[]']").not(this).prop('checked', this.checked);
});

window.onbeforeunload = function(){
  // console.log("abort");
  problems_table_xhr.abort(); 
  getuptime_xhr.abort();
  cpu_usage_xhr.abort();
  getdisk_xhr.abort();
  getcpugraph_xhr.abort();
  getmemgraph_xhr.abort();
  read_latency_xhr.abort();
  write_latency_xhr.abort();
  bytes_received_xhr.abort();
  bytes_transmit_xhr.abort();
  // window.stop();
}
</script>

  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>