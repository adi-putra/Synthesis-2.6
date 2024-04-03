<?php

include 'session.php';

$hostid = $_GET['hostid'] ?? "10084";

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

            print "$hostname";
          }
          ?>
          <small>Latest Data</small>
        </h1>
        <?php
        foreach ($row["interfaces"] as $interface) {
          print $interface['ip'] . ", ";
          break;
        }

        foreach ($row["groups"] as $group) {
          $hostgroup[] = $group['name'];
          print $group['name']." ";
        }
        ?>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
        <br><br>

        <a href="hostlist.php"><button class="btn btn-default"><i class="fa  fa-arrow-left"></i> &nbsp;Host List</button></a>

        <br><br>

        <?php

        if ($_GET["timefrom"] == "" and $_GET["timetill"] == "") {
          $time_str = "Today";
        } else if ($_GET["timetill"] == "") {
          $time_str = date("d/m/Y H:i A", $timefrom) . " - Now";
        } else {
          $time_str = date("d/m/Y H:i A", $timefrom) . " - " . date("d/m/Y H:i A", $timetill);
        }

        ?>
      </section>

      <script>
        //initializer
        var hostid = <?php echo $hostid; ?>;
        var hostArr = '<?php echo $hostArr; ?>';
        var timefrom = '<?php echo $_GET["timefrom"]; ?>';
        var timetill = '<?php echo $_GET["timetill"]; ?>';
        var groupid = '<?php echo $groupid . "&"; ?>';

        if (timefrom == "" && timetill == "") {
          var timerange = "";
          var diff = 0;
        } else if (timetill == "") {
          var timerange = "&timefrom=" + timefrom;
          var currtime = moment().unix();
          var diff = currtime - timefrom;
        } else {
          var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
          var diff = timetill - timefrom;
        }
      </script>


      <!-- Main content -->
      <section class="content">

        <!-- Cards -->
        <div class="row">

          <!-- cpu count card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3>
                  <div id="card_node"></div>
                  <script>
                    loadCardNode();
                    var card_node = setInterval(loadCardNode, 60000);

                    function loadCardNode() {
                      $("#card_node").load("hostdetails/netapp/cards/card_node.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Total Node (Online/Offline)
                </p>
                <div class="icon">
                  <i class="fa fa-microchip"></i>
                </div>
              </div>
              <a href="#netapp-nodes" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

          <!-- cpu usage card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3>
                  <div id="card_volume"></div>
                  <script>
                    loadCardVolume();
                    var card_volume = setInterval(loadCardVolume, 60000);

                    function loadCardVolume() {
                      $("#card_volume").load("hostdetails/netapp/cards/card_volume.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Total Volume (Online/Offline)
                </p>
                <div class="icon">
                  <i class="fa fa-microchip"></i>
                </div>
              </div>
              <a href="#netapp-volumes" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

          <!-- cpu usage card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3>
                  <div id="card_aggr"></div>
                  <script>
                    loadCardAggr();
                    var card_aggr = setInterval(loadCardAggr, 60000);

                    function loadCardAggr() {
                      $("#card_aggr").load("hostdetails/netapp/cards/card_aggr.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Total Aggregate (Online/Offline)
                </p>
                <div class="icon">
                  <i class="fa fa-microchip"></i>
                </div>
              </div>
              <a href="#netapp-aggregations" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

          <!-- cpu usage card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3>
                  <div id="card_lun"></div>
                  <script>
                    loadCardLUN();
                    var card_lun = setInterval(loadCardLUN, 60000);

                    function loadCardLUN() {
                      $("#card_lun").load("hostdetails/netapp/cards/card_lun.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Total LUN
                </p>
                <div class="icon">
                  <i class="fa fa-microchip"></i>
                </div>
              </div>
              <a href="#netapp-LUN" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

        </div>


        <div class="row">

          <!-- no ofdisk card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-orange">
              <div class="inner">
                <h3>
                  <div id="card_cpuusage"></div>
                  <script>
                    loadCardCPUUsage();
                    var card_cpuusage = setInterval(loadCardCPUUsage, 60000);

                    function loadCardCPUUsage() {
                      $("#card_cpuusage").load("hostdetails/netapp/cards/card_cpuusage.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  CPU Usage
                </p>
                <div class="icon">
                  <i class="fa fa-memory"></i>
                </div>
              </div>
              <a href="#netapp-performance" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

          <!-- failef disk card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-orange">
              <div class="inner">
                <h3>
                  <div id="card_lunlatency"></div>
                  <script>
                    loadCardLUNLatency();
                    var card_lunlatency = setInterval(loadCardLUNLatency, 60000);

                    function loadCardLUNLatency() {
                      $("#card_lunlatency").load("hostdetails/netapp/cards/card_lunlatency.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  LUN Latency
                </p>
                <div class="icon">
                  <i class="fa fa-memory"></i>
                </div>
              </div>
              <a href="#netapp-performance" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

          <!-- spare disk card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-orange">
              <div class="inner">
                <h3>
                  <div id="card_luniops"></div>
                  <script>
                    loadCardLUNIOPS();
                    var card_luniops = setInterval(loadCardLUNIOPS, 60000);

                    function loadCardLUNIOPS() {
                      $("#card_luniops").load("hostdetails/netapp/cards/card_luniops.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  LUN IOPS
                </p>
                <div class="icon">
                  <i class="fa fa-memory"></i>
                </div>
              </div>
              <a href="#netapp-performance" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

          <!-- problem card -->
          <div class="col-lg-3">
            <!-- small box -->
            <div class="small-box bg-red">
              <div class="inner">
                <h3>
                  <div id="today_problems"></div>
                  <script>
                    loadTodayProb();
                    var today_problems = setInterval(loadTodayProb, 60000);

                    function loadTodayProb() {
                      $("#today_problems").load("hostdetails/netapp/cards/today_problems.php?hostid=" + hostid);
                    }
                  </script>
                </h3>
                <p>
                  Problem (Today)
                </p>
                <div class="icon">
                  <i class="fa fa-exclamation-circle"></i>
                </div>
              </div>
              <a href="#netapp-issues" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div><!-- ./col -->

        </div>
        <!-- end cards -->

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
                        <button class="btn btn-block btn-primary btn-xs" onclick="prevmonth();">Previous month</button>
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

                <!-- /.tab-pane -->
                <div class="tab-pane active" id="tab_2-2">
                  <div class="row">
                    <div class="col-md-12">
                      <table class="table table-bordered table-hover">
                        <tr>
                          <td>Host</td>
                          <td>
                            <!-- select group -->
                            <div class="form-group" style="display: inline-block;">
                              <select class="form-control" onchange="location = this.value;" style="width: auto;">
                                <option><?php echo $hostname; ?></option>
                                <?php
                                $params = array(
                                  "output" => array("hostid", "name"),
                                  "groupids" => $groupid,
                                  "search" => array("name" => array("netapp")),
                                  "searchByAny" => true
                                );
                                $result = $zbx->call('host.get', $params);
                                foreach ($result as $row) {

                                  if ($row["name"] != $hostname) {
                                    $gethostid = $row["hostid"];
                                    $gethostname = $row["name"];
                                    print "<option value='hostdetails_netapp.php?hostid=" . $gethostid . "'>$gethostname</option>";
                                  }
                                }
                                ?>
                              </select>
                            </div>
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
                    <li class="active"><input type="checkbox" class="sub-box" value="netapp-issues" checked><a href="#netapp-issues">Problems</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-general" checked><a href="#netapp-general">General</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-nodes" checked><a href="#netapp-nodes">Nodes</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-volumes" checked><a href="#netapp-volumes">Volumes</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-aggregations" checked><a href="#netapp-aggregations">Aggregations</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-enclosure" checked><a href="#netapp-enclosure">Enclosure</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-vif" checked><a href="#netapp-vif">VIF</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-LUN" checked><a href="#netapp-LUN">LUN</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-capacity" checked><a href="#netapp-capacity">Capacity</a></li>
                    <li><input type="checkbox" class="sub-box" value="netapp-performance" checked><a href="#netapp-performance">Performance</a></li>
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

                <!-- Issues/Problems -->
                <div class="row" id="netapp-issues">
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

                <!-- general -->
                <div id="netapp-general">
                  <h3 class="box-title">General</h3>

                  <!-- version -->
                  <div class="row" id="netapp-version">
                    <div class="col-md-12">
                      <div class="box box-solid box-default">
                        <div class="box-header">
                          <h3 class="box-title">Product Version</h3>
                          <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                          </div>
                        </div>
                        <div class="box-body">
                          <div id="version"></div>
                          <script>
                            $("#version").load("hostdetails/netapp/general/version.php?hostid=" + hostid);
                          </script>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- autosupport -->
                  <div class="row" id="netapp-Autosupport">
                    <div class="col-md-12">
                      <div class="box box-solid box-default">
                        <div class="box-header">
                          <h3 class="box-title">Autosupport</h3>
                          <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                          </div>
                        </div>
                        <div class="box-body">
                          <div id="autosupport"></div>
                          <script>
                            loadAutoSupport();
                            if (diff <= 604800 && timetill == "") {
                              var autosupport = setInterval(loadAutoSupport, 60000);
                            }

                            function loadAutoSupport() {
                              //console.log("load");           
                              $("#autosupport").load("hostdetails/netapp/general/autosupport.php?hostid=" + hostid);
                            }
                          </script>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- no of disk -->
                  <div class="row" id="netapp-disk">
                    <div class="col-md-12">
                      <div class="box box-solid box-default">
                        <div class="box-header">
                          <h3 class="box-title">Disk</h3>
                          <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                          </div>
                        </div>
                        <div class="box-body">
                          <div id="no_disk"></div>
                          <script>
                            loadNo_disk();
                            if (diff <= 604800 && timetill == "") {
                              var no_disk = setInterval(loadNo_disk, 60000);
                            }

                            function loadNo_disk() {
                              //console.log("load");           
                              $("#no_disk").load("hostdetails/netapp/general/numOfDisk.php?hostid=" + hostid);
                            }
                          </script>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!--nodes  -->
                <div id="netapp-nodes">
                  <h3 class="box-title">Nodes</h3>
                  <div class="row" id="netapp-disk">
                    <div class="col-md-12">
                      <div class="box box-solid box-default">
                        <div class="box-header">
                          <h3 class="box-title">Nodes</h3>
                          <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                          </div>
                        </div>
                        <div class="box-body">
                          <div id="node"></div>
                          <script>
                            loadNode();
                            if (diff <= 604800 && timetill == "") {
                              var node = setInterval(loadNode, 60000);
                            }

                            function loadNode() {
                              //console.log("load");           
                              $("#node").load("hostdetails/netapp/node/node.php?hostid=" + hostid);
                            }
                          </script>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Volumes -->
                <div id="netapp-volumes">
                  <h3 class="box-title">Volumes</h3>

                  <!-- volume info -->
                  <div class="row" id="volume-info">
                    <div class="col-md-12">
                      <div class="box box-solid box-default">
                        <div class="box-header">
                          <h3 class="box-title">Volume Info</h3>
                          <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                          </div>
                        </div>
                        <div class="box-body">
                          <div id="volumeInfo"></div>
                          <script>
                            loadVolumeInfo();
                            if (diff <= 604800 && timetill == "") {
                              var volumeInfo = setInterval(loadVolumeInfo, 60000);
                            }

                            function loadVolumeInfo() {
                              //console.log("load");           
                              $("#volumeInfo").load("hostdetails/netapp/volume/volumeinfo.php?hostid=" + hostid);
                            }
                          </script>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Volume Capacity -->
                  <div class="row" id="volume-capacity">
                    <div class="col-md-12">
                      <div class="box box-solid box-default">
                        <div class="box-header">
                          <h3 class="box-title">Volume Capacity</h3>
                          <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                          </div>
                        </div>
                        <div class="box-body">
                          <div id="volumeCapacity"></div>
                          <script>
                            loadVolumeCap();
                            if (diff <= 604800 && timetill == "") {
                              var volumeCap = setInterval(loadVolumeCap, 60000);
                            }

                            function loadVolumeCap() {
                              //console.log("load");           
                              $("#volumeCapacity").load("hostdetails/netapp/volume/volumecap.php?hostid=" + hostid);
                            }
                          </script>
                        </div>
                      </div>
                    </div>
                  </div>


                  <!-- aggregations -->
                  <div id="netapp-aggregations">
                    <h3 class="box-title">Aggregations</h3>

                    <!-- Aggregate Info -->
                    <div class="row" id="aggregate-state">
                      <div class="col-md-12">
                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Aggregate Info</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="aggregateInfo"></div>
                            <script>
                              loadAggregateInfo();
                              if (diff <= 604800 && timetill == "") {
                                var aggregateInfo = setInterval(loadAggregateInfo, 60000);
                              }

                              function loadAggregateInfo() {
                                //console.log("load");           
                                $("#aggregateInfo").load("hostdetails/netapp/aggregation/aggregateInfo.php?hostid=" + hostid);
                              }
                            </script>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Aggregate Capacity -->
                    <div class="row" id="aggregate-capacity">
                      <div class="col-md-12">
                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Aggregate Capacity</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="aggregateCapacity"></div>
                            <script>
                              loadAggregateCapacity();
                              if (diff <= 604800 && timetill == "") {
                                var aggregateCapacity = setInterval(loadAggregateCapacity, 60000);
                              }

                              function loadAggregateCapacity() {
                                //console.log("load");           
                                $("#aggregateCapacity").load("hostdetails/netapp/aggregation/aggregateCap.php?hostid=" + hostid);
                              }
                            </script>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- enclosure -->
                    <div id="netapp-enclosure">
                      <h3 class="box-title">Enclosure</h3>
                      <div class="row" id="netapp-disk">
                        <div class="col-md-12">
                          <div class="box box-solid box-default">
                            <div class="box-header">
                              <h3 class="box-title">Enclosure</h3>
                              <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                              </div>
                            </div>
                            <div class="box-body">
                              <div id="enclosure"></div>
                              <script>
                                loadEnclosure();
                                if (diff <= 604800 && timetill == "") {
                                  var enclosure = setInterval(loadEnclosure, 60000);
                                }

                                function loadEnclosure() {
                                  //console.log("load");           
                                  $("#enclosure").load("hostdetails/netapp/enclosure/enclosure.php?hostid=" + hostid);
                                }
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- VIF -->
                    <div id="netapp-vif">
                      <h3 class="box-title">VIF</h3>
                      <div class="row" id="netapp-disk">
                        <div class="col-md-12">
                          <div class="box box-solid box-default">
                            <div class="box-header">
                              <h3 class="box-title">VIF</h3>
                              <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                              </div>
                            </div>
                            <div class="box-body">
                              <div id="vif"></div>
                              <script>
                                loadVIF();
                                if (diff <= 604800 && timetill == "") {
                                  var vif = setInterval(loadVIF, 60000);
                                }

                                function loadVIF() {
                                  //console.log("load");           
                                  $("#vif").load("hostdetails/netapp/VIF/VIF.php?hostid=" + hostid);
                                }
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!--LUN  -->
                    <div id="netapp-LUN">
                      <h3 class="box-title">LUN</h3>
                      <div class="row" id="netapp-disk">
                        <div class="col-md-12">
                          <div class="box box-solid box-default">
                            <div class="box-header">
                              <h3 class="box-title">LUN Capacity</h3>
                              <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                              </div>
                            </div>
                            <div class="box-body">
                              <div id="lunCapacity"></div>
                              <script>
                                loadLUN();
                                if (diff <= 604800 && timetill == "") {
                                  var LUN = setInterval(loadLUN, 60000);
                                }

                                function loadLUN() {
                                  //console.log("load");           
                                  $("#lunCapacity").load("hostdetails/netapp/LUN/lunCap.php?hostid=" + hostid);
                                }
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Capacity -->
                    <div id="netapp-capacity">
                      <h3 class="box-title">Capacity</h3>

                      <!--Vol percentage  -->
                      <div class="row" id="capacity-vol-percent">
                        <div class="col-md-12">
                          <div class="box box-solid box-default">
                            <div class="box-header">
                              <h3 class="box-title">Volume Used Data Percentage</h3>
                              <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                              </div>
                            </div>
                            <div class="box-body">
                              <div id="vol_data_percentage"></div>
                              <script>
                                loadVolumePercentGraph();
                                if (diff <= 604800 && timetill == "") {
                                  var vol_data_percentage = setInterval(loadVolumePercentGraph, 60000);
                                }

                                function loadVolumePercentGraph() {
                                  //console.log("load");           
                                  $("#vol_data_percentage").load("hostdetails/netapp/performance/volumePercentageGraph.php?hostid[]=" + hostid + timerange);
                                }
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!--Vol capacity  -->
                      <div class="row" id="capacity-vol-capacity">
                        <div class="col-md-12">
                          <div class="box box-solid box-default">
                            <div class="box-header">
                              <h3 class="box-title">Aggregate Size Used Percentage</h3>
                              <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                              </div>
                            </div>
                            <div class="box-body">
                              <div id="aggre_size_used"></div>
                              <script>
                               loadAggregateSizeUsedGraph();
                                if (diff <= 604800 && timetill == "") {
                                  var aggre_size_used = setInterval(loadAggregateSizeUsedGraph, 60000);
                                }

                                function loadAggregateSizeUsedGraph() {
                                  //console.log("load");           
                                  $("#aggre_size_used").load("hostdetails/netapp/performance/aggregateSizeUsedGraph.php?hostid[]=" + hostid + timerange);
                                }
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- performance -->
                      <div id="netapp-performance">
                        <h3 class="box-title">Performances</h3>

                        <!--CPU  Usage  -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="box box-solid box-default">
                              <div class="box-header">
                                <h3 class="box-title">CPU Usage</h3>
                                <div class="box-tools pull-right">
                                  <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                </div>
                              </div>
                              <div class="box-body">
                                <div id="cpuUsage"></div>
                                <script>
                                  loadCPUUsageGraph();
                                  if (diff <= 604800 && timetill == "") {
                                    var cpuUsage = setInterval(loadCPUUsageGraph, 60000);
                                  }

                                  function loadCPUUsageGraph() {
                                    //console.log("load");           
                                    $("#cpuUsage").load("hostdetails/netapp/performance/cpuUsage.php?hostid[]=" + hostid + timerange);
                                  }
                                </script>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!--LUN IOPS  -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="box box-solid box-default">
                              <div class="box-header">
                                <h3 class="box-title">LUN IOPS</h3>
                                <div class="box-tools pull-right">
                                  <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                </div>
                              </div>
                              <div class="box-body">
                                <div id="LUN_iops"></div>
                                <script>
                                  loadLUNIOPSGraph();
                                  if (diff <= 604800 && timetill == "") {
                                    var LUN_iops = setInterval(loadLUNIOPSGraph, 60000);
                                  }

                                  function loadLUNIOPSGraph() {
                                    //console.log("load");           
                                    $("#LUN_iops").load("hostdetails/netapp/performance/LUN_iops.php?hostid[]=" + hostid + timerange);
                                  }
                                </script>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!--LUN  Latency  -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="box box-solid box-default">
                              <div class="box-header">
                                <h3 class="box-title">LUN Latency</h3>
                                <div class="box-tools pull-right">
                                  <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                </div>
                              </div>
                              <div class="box-body">
                                <div id="LUN_latency"></div>
                                <script>
                                  loadLUNLatencyGraph();
                                  if (diff <= 604800 && timetill == "") {
                                    var LUN_latency = setInterval(loadLUNLatencyGraph, 60000);
                                  }

                                  function loadLUNLatencyGraph() {
                                    //console.log("load");           
                                    $("#LUN_latency").load("hostdetails/netapp/performance/LUN_latency.php?hostid[]=" + hostid + timerange);
                                  }
                                </script>
                              </div>
                            </div>
                          </div>
                        </div>


                      </div>
                    </div>
                    <!-- box-body -->
                  </div>
                </div>
              </div>
      </section>
      <!-- /.content -->
    </div>

    <!-- Date Range Buttons script -->
    <script>
      function changeDateTime() {
        var gettimefrom = document.getElementsByName("timefrom")[0].value;
        var gettimetill = document.getElementsByName("timetill")[0].value;
        //if value is blank
        if (gettimefrom === "" || gettimetill === "") {
          alert("Zero/Wrong inputs detected!");
          return false;
        } else {
          var timefrom = moment(gettimefrom, "D-M-Y hh:mm A").unix();
          var timetill = moment(gettimetill, "D-M-Y hh:mm A").unix();

          //get current URL
          var currentURL = document.URL;

          //trim and fine the position in url string to cut timefrom and timetil
          var stringPos = currentURL.search("timefrom");

          //if no timefrom in url string

          if (stringPos == -1) {
            var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
          } else {
            var trimmedUrl = currentURL.substr(0, stringPos - 1);

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

        var newURL = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

          //declare new URL together with new values
          var newURL = trimmedUrl + "&timefrom=" + timefrom + "&timetill=" + timetill;
        }

        //alert(hostUrl);
        //submit the form by reopening the window with new values 
        location.assign(newURL);
      }

      function prevmonth() {
        var timefrom = moment().subtract(1, 'months').startOf('month').unix();
        var timetill = moment().subtract(1, 'months').endOf('month').unix();

        //get current URL
        var currentURL = document.URL;

        //trim and fine the position in url string to cut timefrom and timetill
        var stringPos = currentURL.search("timefrom");

        //if no timefrom in url string
        if (stringPos == -1) {
          var newURL = currentURL + "&timefrom=" + timefrom + "&timetill=" + timetill;
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

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
        } else {
          var trimmedUrl = currentURL.substr(0, stringPos - 1);

          //declare new URL together with new values
          var newURL = trimmedUrl + "&timefrom=" + timefrom;
        }

        //alert(hostUrl);
        //submit the form by reopening the window with new values 
        location.assign(newURL);
      }
    </script>

    <!-- Change Date script -->
    <script type="text/javascript">
      $(function() {
        $('#datetimepicker6').datetimepicker({
          format: "DD-MM-YYYY hh:mm A"
        });
        $('#datetimepicker7').datetimepicker({
          format: "DD-MM-YYYY hh:mm A"
        });
        $("#datetimepicker6").on("dp.change", function(e) {
          $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
        });
        $("#datetimepicker7").on("dp.change", function(e) {
          $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
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
</body>

</html>