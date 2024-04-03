<?php

include 'session.php';

$hostid = $_GET['hostid'];

if ($hostid == "10084") {
  header("location: hostdetails_linux.php?hostid=$hostid");
}

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
} else if ($diff >= 2678400 and $diff < 7948800) {
  $status = "Last 1 month";
} 

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
<?php include("head.php"); ?>

<body class="skin-blue">
  <div class="wrapper">

    <?php include("header.php") ?>

    <?php include('sidebar.php'); ?>

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          <?php
          $params = array(
            "output" => array("name"),
            "hostids" => $hostid,
            "selectGroups" => array("name"),
            "selectInterfaces" => array("ip")
          );

          $result = $zbx->call('host.get', $params);
          foreach ($result as $row) {
            $hostname = $row["name"];

            foreach ($row["groups"] as $group) {
              //if group is in windows, print windows logo with it...also for other brand
              if (stripos($group["name"], 'Firewall') !== false) {
                $hostgroup = $group['name'] . " <i class='fa fa-fw fa-windows'></i>";
              } else {
                $hostgroup = $group['name'];
              }
            }
            foreach ($row["interfaces"] as $interface) {
              $hostip = $interface['ip'];
              break;
            }
            print "$hostname";
          }
          ?>
          <small>Latest Data</small>
        </h1>
        <?php
        print $hostip . ', ' . $hostgroup;
        ?>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
        <br><br>

        <a href="hostlist.php"><button class="btn btn-default"><i class="fa  fa-arrow-left"></i> &nbsp;Host List</button></a>

        <br><br>
        <!-- Select Host Dropdown -->
        <div class="form-group">
          <select class="form-control" name="selecthost" onchange="location = this.value;" style="display: inline-block; width: auto;">
            <option><?php echo $hostname ?></option>
            <?php
            $params = array(
              "output" => array("hostid", "name"),
              "selectGroups" => array("name")
            );
            $result = $zbx->call('host.get', $params);
            foreach ($result as $row) {
              $gethostid = $row["hostid"];
              //if hostname is not same as current hostname, get hostname
              if ($row["name"] != $hostname) {
                $gethostname = $row["name"];
                
                //get group name
                foreach($row["groups"] as $group) {
                  $getgroupname = $group["name"];
                }
              } 
              
              else {
                continue;
              }
              
              if (stripos($gethostname, "FW") !== false or stripos($gethostname, "Firewall") !== false or stripos($getgroupname, "FW") !== false or stripos($getgroupname, "Firewall") !== false) {
                print "<option value='hostdetails_firewall.php?hostid=" . $gethostid . "'>$gethostname</option>";
              } 
            }
            ?>
          </select>
        </div>
      </section>

      <script>
        // Graph Button
        function graphBtn() {
          var timefrom = document.getElementsByName("timefrom")[0].value;
          var timetill = document.getElementsByName("timetill")[0].value;
          //if value is blank
          if (timefrom === "" || timetill === "") {
            alert("Zero/Wrong inputs detected!");
            return false;
          } else {
            timefrom = moment(timefrom, "D-M-Y hh:mm:ss A").unix();
            timetill = moment(timetill, "D-M-Y hh:mm:ss A").unix();

            fromTime = displayTime(timefrom);
            tillTime = displayTime(timetill);

            var timefrom = document.getElementsByName("timefrom")[0].value;
            timefrom = moment(timefrom, "D-M-Y hh:mm:ss A").unix();
            var timetill = document.getElementsByName("timetill")[0].value;
            timetill = moment(timetill, "D-M-Y hh:mm:ss A").unix();
            loadAllGraph(timefrom, timetill);
          }
        }

        function graphBtnSide() {
          graphBtn();
        }
      </script>

      <script>
      // Get Time Status, HostID, Time From and Till 
      getTimeStat = '<?php echo $status; ?>';
      if (getTimeStat == "Today" || getTimeStat == "Last 1 hour") {
        var intervalOn = true;
      }
      //take time value from server
      var hostid = '<?php echo $hostid; ?>';
      var timefrom = '<?php echo $timefrom ?>';
      var timetill = '<?php echo $timetill ?>';

      //take current time value
      var currTime = '<?php echo time(); ?>';
      var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;

      // Load All Graph Function
      function loadAllGraph(timefrom, timetill) {
        interval = false; //if this function starts, stop interval
        timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
        loadCPUPercGraph(hostid, timerange);
        loadUsedMemGraph(hostid, timerange);
        loadNetTrafficGraph(hostid, timerange);
        return true;
      }

      function loadCPUPercGraph(hostid, timerange) {
        var interval = setInterval(function() {
          timefrom = '<?php echo $timefrom; ?>';
          if (timetill == currTime) {
            timetill = Math.floor(Date.now() / 1000);
          }
          else {
            timetill = '<?php echo $timetill ?>';
          }
          timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
          $("#cpu_percentage").load("hostdetails/firewall/performance/cpu_percentage.php?hostid=" + hostid + timerange);
        }, 60000);

        //load chart 
        $("#cpu_percentage").load("hostdetails/firewall/performance/cpu_percentage.php?hostid=" + hostid + timerange);

        //if interval is set true, start interval
        if (intervalOn == true) {
          $('#submitDate').click(function() {
            clearInterval(interval);
          });
          interval;
        }
      }

      function loadUsedMemGraph(hostid, timerange) {
        var interval = setInterval(function() {
          timefrom = '<?php echo $timefrom; ?>';
          if (timetill == currTime) {
            timetill = Math.floor(Date.now() / 1000);
          }
          else {
            timetill = '<?php echo $timetill ?>';
          }
          timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
          $("#used_memory").load("hostdetails/firewall/performance/used_memory.php?hostid=" + hostid + timerange);
        }, 60000);

        //load chart 
        $("#used_memory").load("hostdetails/firewall/performance/used_memory.php?hostid=" + hostid + timerange);

        //if interval is set true, start interval
        if (intervalOn == true) {
          $('#submitDate').click(function() {
            clearInterval(interval);
          });
          interval;
        }
      }

      function loadNetTrafficGraph(hostid, timerange) {
        var interval = setInterval(function() {
          timefrom = '<?php echo $timefrom; ?>';
          if (timetill == currTime) {
            timetill = Math.floor(Date.now() / 1000);
          }
          else {
            timetill = '<?php echo $timetill ?>';
          }
          timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
          $("#net_traffic").load("hostdetails/firewall/performance/net_traffic.php?hostid=" + hostid + timerange);
        }, 60000);

        //load chart 
        $("#net_traffic").load("hostdetails/firewall/performance/net_traffic.php?hostid=" + hostid + timerange);

        //if interval is set true, start interval
        if (intervalOn == true) {
          $('#submitDate').click(function() {
            clearInterval(interval);
          });
          interval;
        }
      }

      // Load Graphs
      //loadAllGraph(timefrom, timetill);
    </script>

      <!-- Main content -->
      <section class="content">

        <!-- Cards -->
        <div class="row">
          <div class="col-md-4">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3>
                  <div id="current_cpu"></div>
                  <script>
                    loadCurrentCPU();
                    var current_cpu = setInterval(loadCurrentCPU, 60000);
                    function loadCurrentCPU() {
                      $("#current_cpu").load("hostdetails/firewall/cards/current_cpu.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Current CPU Utilization (%)
                </p>
                <div class="icon">
                  <i class="fa fa-microchip"></i>
                </div>
              </div>
              <a href="#cpu_percentage" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->
          <div class="col-md-4">
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3>
                  <div id="current_memory"></div>
                  <script>
                    loadCurrentMem();
                    var current_cpu = setInterval(loadCurrentMem, 60000);
                    function loadCurrentMem() {
                      $("#current_memory").load("hostdetails/firewall/cards/current_memory.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Current RAM Usage (%)
                </p>
                <div class="icon">
                  <i class="fa fa-memory"></i>
                </div>
              </div>
              <a href="#used_memory" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->
          <div class="col-md-4">
            <!-- small box -->
            <div class="small-box bg-red">
              <div class="inner">
                <h3>
                  <div id="today_problems"></div>
                  <script>
                    loadCurrentProblems();
                    var today_problems = setInterval(loadCurrentProblems, 60000);
                    function loadCurrentProblems() {
                      $("#today_problems").load("hostdetails/firewall/cards/today_problems.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Problems (Today)
                </p>
                <div class="icon">
                  <i class="fa fa-exclamation-circle"></i>
                </div>
              </div>
              <a href="problems.php?hostid[]=<?php echo $hostid; ?>&timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" target="_blank" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->
        </div>

        
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title"><?php echo $hostname; ?></h3>
              </div>
              <div class="box-body">
                <!-- Datetime Picker -->
                <div class="col-sm-12">
                  <div class="box" style="background-color: #3c8dbc;">
                    <div class="box-header">
                      <h3 class="box-title" style="color: white;"><?php if (isset($_POST['submit'])) {
                                                                    echo $daterange = $timefrom1 . ' - ' . $timetill1;
                                                                  } else {
                                                                    echo $status;
                                                                  } ?></h3>
                      <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                      </div>
                    </div>
                    <div class="box-body">
                      <form action="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" method="post">
                        <div class="row">
                          <div class="col-lg-4 col-md-4 col-sm-12 text-center" style="padding-bottom: 15px;">
                            <div class="input-group" style="padding-right: 15px;padding-left: 15px;">
                              <div class="input-group-addon" style="background:none;border:none;padding:0;">
                                <button type="button" class="btn btn-success btn-flat dropdown-toggle" data-toggle="dropdown">Range
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 hour"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 hour</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 day"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 day</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-2 days"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 2 days</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 week"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 7 days</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 month"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 month</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-3 months"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 3 months</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-6 months"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 6 months</a></li>
                                </ul>
                              </div>
                              <input type="text" class="form-control" value="<?php if (isset($_POST['submit'])) {
                                                                                echo "";
                                                                              } else {
                                                                                echo $status;
                                                                              } ?>" disabled>
                            </div>
                          </div>
                          <div class="col-lg-8 col-md-8 col-sm-12 text-center">
                            <div class="col-lg-5 col-md-5 col-sm-12">
                              <div class="form-group">
                                <div class='input-group date' id='datetimepicker6'>
                                  <input type='text' class="form-control" name="timefrom" value="<?php if (isset($_POST['timefrom'])) {
                                                                                                    echo $_POST['timefrom'];
                                                                                                  } else {
                                                                                                    echo date('d-m-Y h:i A', $timefrom);
                                                                                                  } ?>" placeholder="From:" />
                                  <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                  </span>
                                </div>
                              </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-sm-12">
                              <div class="form-group">
                                <div class='input-group date' id='datetimepicker7'>
                                  <input type='text' class="form-control" name="timetill" value="<?php if (isset($_POST['timetill'])) {
                                                                                                    echo $_POST['timetill'];
                                                                                                  } else {
                                                                                                    echo date('d-m-Y h:i A', $timetill);
                                                                                                  } ?>" placeholder="To:" />
                                  <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                  </span>
                                </div>
                              </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12">
                              <input type="submit" name="submit" id="submitDate" onclick="graphBtn();" value="Apply" class="btn btn-block"></input>
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>


                <div class="row" id="mycontent-host">
                  <div class="col-xs-12">
                    <aside class="col-lg-2 col-md-3 col-sm-12">
                      <div class="collapsed-box affix-top" id="mysidebar-host" style="z-index: 1;">
                        <div class="box-header with-border">

                        </div>
                        <div class="box-body show">
                          <ul class="nav nav-pills nav-stacked overview-nav">
                            <form action="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" method="post">
                              <h3 class="side-box-title">Date</h3>
                              <li>
                                <div class="dropdown" style="margin-bottom: 10px;">
                                  <button type="button" class="btn btn-block btn-default btn-flat dropdown-toggle side-range-btn" data-toggle="dropdown">Range: <?php if (isset($_POST['submit'])) {
                                                                                                                                                                  echo "";
                                                                                                                                                                } else {
                                                                                                                                                                  echo $status;
                                                                                                                                                                } ?>
                                    <span class="caret" style="margin-top: 7px;"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1" id="range-dropdown">
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 hour"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 hour</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 day"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 day</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-2 days"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 2 days</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 week"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 7 days</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 month"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 month</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-3 months"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 3 months</a></li>
                                  <li><a href="hostdetails_firewall.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-6 months"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 6 months</a></li>
                                  </ul>
                                </div>
                              </li>
                              <li>
                                <div class='input-group date' id='datetimepicker8' style="margin-bottom: 10px;">
                                  <input type='text' class="form-control" name="timefrom" value="<?php if (isset($_POST['timefrom'])) {
                                                                                                    echo $_POST['timefrom'];
                                                                                                  } else {
                                                                                                    echo date('d-m-Y h:i A', $timefrom);
                                                                                                  } ?>" placeholder="From:" />
                                  <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                  </span>
                                </div>
                              </li>
                              <li>
                                <div class='input-group date' id='datetimepicker9' style="margin-bottom: 10px;">
                                  <input type='text' class="form-control" name="timetill" value="<?php if (isset($_POST['timetill'])) {
                                                                                                    echo $_POST['timetill'];
                                                                                                  } else {
                                                                                                    echo date('d-m-Y h:i A', $timetill);
                                                                                                  } ?>" placeholder="To:" />
                                  <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                  </span>
                                </div>
                                <input type="submit" name="submit" onclick="graphBtnSide();" value="Apply" class="btn btn-block btn-success" style="margin-bottom: 10px;"></input>
                              </li>
                            </form>
                            <h3 class="side-box-title">Filters</h3>
                            <li><input type="checkbox" class="upper-box" checked>
                              <p>Select All</p>
                            </li>
                            <div class="sub-group" id="category">
                              <li><input type="checkbox" class="sub-box" value="issues" checked><a href="#issues">Problems</a></li>
                              <li><input type="checkbox" class="sub-box" value="systeminfo" checked><a href="#systeminfo">System Info</a></li>
                              <li><input type="checkbox" class="sub-box" value="performance" checked><a href="#performance">Performance</a></li>
                            </div>
                          </ul>
                        </div>
                      </div>
                    </aside>

                    <div class="col-lg-10 col-md-9 col-sm-12" id="content-wrapper-host">
                      <!-- Issues/Problems -->
                      <div class="row" id="issues">
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
                                    <div id="problems"></div>
                                    <script>
                                        //begin page by start the interval
                                        loadProblemsTable();
                                        if (diff <= 604800 && timetill == "") {
                                          var problems_table = setInterval(loadProblemsTable, 60000);
                                        }

                                        //ajax call to load content                                                                      
                                        function loadProblemsTable() {
                                            //$("#problem_sev_info").load("problems/problem_sev_info.php?" + groupid + hostid + timerange);
                                            $("#problems").load("problems/problem_table.php?hostid[]=" + hostid + timerange);
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
                                  </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- System Info -->
                      <div id="systeminfo" style="padding-bottom: 50px;">
                        <h3>System Information</h3>
                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">System Info</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="sysinfo"></div>
                            <script>
                              $("#sysinfo").load("hostdetails/firewall/system_info/systeminfo.php?hostid=" + hostid);
                            </script>
                          </div>
                        </div>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Sonicwall</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="sonicwall"></div>
                            <script>
                              $("#sonicwall").load("hostdetails/firewall/system_info/sonicwall.php?hostid=" + hostid);
                            </script>
                          </div>
                        </div>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">System Uptime</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="sysuptime"></div>
                            <script>
                              $("#sysuptime").load("hostdetails/firewall/system_info/sysuptime.php?hostid=" + hostid);
                            </script>
                          </div>
                        </div>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">VPN</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="isplinks"></div>
                            <script>
                              $("#isplinks").load("hostdetails/firewall/system_info/isplinks.php?hostid=" + hostid);
                            </script>
                          </div>
                        </div>
                      </div>


                      <div id="performance" style="padding-bottom: 50px;">
                        <h3>Performance</h3>
                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">CPU Utilization (%) Graph</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class='box-body'>
                            <div id='cpu_percentage'></div>
                            <script>
                              loadCPUPercGraph(hostid, timerange);
                            </script>
                          </div>
                        </div>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">RAM Utilization (%) Graph</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class='box-body'>
                            <div id='used_memory'></div>
                            <script>
                              loadUsedMemGraph(hostid, timerange);
                            </script>
                          </div>
                        </div>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Network Traffic Graph</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class='box-body'>
                            <div id='net_traffic'></div>
                            <script>
                              loadNetTrafficGraph(hostid, timerange);
                            </script>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>







    </div><!-- /.content-wrapper -->
    <?php include("footer.php"); ?>
  </div><!-- ./wrapper -->

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

  <script>
    $('#mysidebar-host').affix({
      offset: {
        /* affix after top content-wrapper */
        top: function() {
          var navOuterHeight = $('#mysidebar-host').height();
          return this.top = navOuterHeight;
        },
        /* un-affix when footer is reached */
        bottom: function() {
          return (this.bottom = $('footer').outerHeight(true))
        }
      }
    });

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
  </script>


  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>