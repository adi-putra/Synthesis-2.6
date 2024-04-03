<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

//ini_set('memory_limit', '-1');

include 'session.php';

//gen report params
$gen_report = $_GET["gen_report"] ?? 0;
$reportname = $_GET["reportname"];

//start page with a group id
//initiate group id
$params = array(
  "output" => array("groupid", "name"),
  "search" => array("name" => array("zabbix", "linux")),
  "searchByAny" => true
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

$hostid = $_GET["hostid"] ?? $hostids;

$counthost = 0;
$hostArr = "";
foreach ($hostid as $hostID) {
  $hostArr .= "hostid[]=" . $hostID . "&";
  $counthost++;
}

// $formlink = "application_sql.php?" . $hostArr;

$timefrom = $_GET['timefrom'] ?? strtotime('today');
$timetill = $_GET['timetill'] ?? strtotime('+3 minutes');

$timerange = "&timefrom=" . $timefrom . "&timetill=" . $timetill;

//display time format string
if ($_GET['timefrom'] == "" and $_GET['timetill'] == "") {
  $time_str = "Today";
} else if ($_GET['timetill'] == "") {
  $time_str = date("d/m/Y H:i A", $timefrom) . " - Now";
} else {
  $time_str = date("d/m/Y H:i A", $timefrom) . " - " . date("d/m/Y H:i A", $timetill);
}

//get current group name
$params = array(
  "output" => array("name"),
  "groupids" => $groupid
);
//call api
$result = $zbx->call('hostgroup.get', $params);
foreach ($result as $row) {
  $groupname = str_replace("Zabbix", "Synthesis", $row["name"]);
}

// current link
$report_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

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
<script>
  //variable to count all output when checked
  var countcheck = 0;

  //variable total of divs
  var countcheck_total = 10;

  //variable to store graph png
  var graphpng = [];

  //variable to store tables images
  var tablepng = [];

  //variable to store print error string
  var printerror = "";
</script>
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
          Reporting
          <small>Linux</small>
        </h1>
        <!-- <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#"><i class="fa fa-dashboard"></i> Application</a></li>
          <li class="active">SQL</li>
        </ol> -->
        <br>

      </section>

      <!-- Main content -->
      <section class="content no-print">

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
                    $result = $zbx->call('host.get', $params);
                    foreach ($result as $host) {
                      $tbHostVal .= $host["name"] . ", ";
                      $counthost2++;
                    }
                  }
                  if ($counthost1 == $counthost2) {
                    $tbHostVal = "All";
                  } else {
                    $tbHostVal = rtrim($tbHostVal, ", ");
                  }
                } else {
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
                                  "search" => array("name" => array("zabbix", "linux", "synthesis")),
                                  "searchByAny" => true
                                );
                                $result = $zbx->call('hostgroup.get', $params);
                                foreach ($result as $row) {
                                  if ($row["name"] != $groupname) {
                                    $getgroupid = $row["groupid"];
                                    $getgroupname = str_replace("Zabbix", "Synthesis", $row["name"]);
                                  } else {
                                    continue;
                                  }
                                  print "<option value='reporting_linux.php?groupid=" . $getgroupid . "'>$getgroupname</option>";
                                }
                                ?>
                              </select>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>Hosts</td>
                          <td>
                            <input style="width: 50%;" id="tbHost" class="form-control" type="text" name="host" value='<?php echo $tbHostVal; ?>' disabled><br>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#hostModal" type="button">Select</button>
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
      </section><!-- /.content -->

      <!-- Main content -->
      <section class="content no-print">
        <!-- PROBLEMS TABLE -->
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header">
                <h3 id="reportready" align="center">Please wait...</h3>
              </div>
              <div class="box-body">
                <div id="chooseprint" class="text-center" style="display: none;">
                  <button id="genreport_btn" onclick="savePDF();" class="btn btn-default margin"><i class="fa fa-print"></i> Generate Report (PDF)</button>
                  <button onclick="loadReportSchedModal();" class="btn btn-default margin" type="button" data-toggle="modal" data-target="#reportingModal"><i class="fa fa-clock"></i> Set Reporting Schedule</button>
                </div>
                <h4 align="center">Errors collected (<i>If present, try refresh this page again</i>):</h4>
                <div id="errorprint" style="text-align: center;">No errors.</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="invoice" id="reportdiv">

        <!-- title row -->
        <div class="row" id="report-title">
          <div class="col-xs-12">
            <h2 class="page-header">
              <i class="fa fa-globe"></i> Linux Overview - Report
              <small class="pull-right" id="date-report">Date: <?php echo date("d/m/Y", strtotime("now")); ?></small>
            </h2>
          </div><!-- /.col -->
        </div>

        <script>
          html2canvas(document.getElementById('report-title')).then(function(canvas) {
            var img = canvas.toDataURL();
            tablepng[0] = img;
          })
        </script>


        <script>
          //initializers
          var counthost = <?php echo $counthost; ?>;
          var hostArr = '<?php echo $hostArr; ?>';
          var timefrom = '<?php echo $_GET["timefrom"]; ?>';
          var timetill = '<?php echo $_GET["timetill"]; ?>';
          var groupid = '<?php echo $groupid . "&"; ?>';
          var groupname = '<?php echo $groupname . "&"; ?>';

          if (timefrom == "" && timetill == "") {
            var timerange = "";
          } else if (timetill == "") {
            var timerange = "&timefrom=" + timefrom;
          } else {
            var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
          }
        </script>

        <!-- info row -->
        <div class="row invoice-info" id="report-dateuser">
          <div class="col-sm-4 invoice-col">
            <b>Report Period: </b><?php echo date("d/m/Y h:i:s A", $timefrom) . " - " . date("d/m/Y h:i:s A", $timetill); ?><br />
            <!-- <br/>
              <b>User: </b><?php echo $zabUser; ?> -->
          </div><!-- /.col -->
        </div><!-- /.row -->

        <script>
          html2canvas(document.getElementById('report-dateuser')).then(function(canvas) {
            var img = canvas.toDataURL();
            tablepng[1] = img;
          })
        </script>


        <div class="row" id="report-info">
          <br><br>
          <div class="col-xs-12 table-responsive">
            <p class="lead">Info</p>
            <div id="getabout"></div>
            <script>
              $("#getabout").load("reporting/linux/getabout.php?groupid=" + groupid + "&" + hostArr, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Info: ";
                  $("#getabout").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                } else {
                  html2canvas(document.getElementById('report-info')).then(function(canvas) {
                    var img = canvas.toDataURL();
                    tablepng[2] = img;
                  })
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row" id="report-uptime">
          <div class="col-xs-12 table-responsive">
            <p class="lead">Uptime</p>
            <div id="uptime"></div>
            <script>
              $("#uptime").load("reporting/linux/uptime.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Uptime: ";
                  $("#uptime").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                } else {
                  html2canvas(document.getElementById('report-uptime')).then(function(canvas) {
                    var img = canvas.toDataURL();
                    tablepng[3] = img;
                  })
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row" id="report-spaceutil">
          <div class="col-xs-12 table-responsive">
            <p class="lead">Space Utilization (%)</p>
            <div id="spaceutil"></div>
            <script>
              $("#spaceutil").load("reporting/linux/spaceutil.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Space Utilization (%): ";
                  $("#spaceutil").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                } else {
                  html2canvas(document.getElementById('report-spaceutil')).then(function(canvas) {
                    var img = canvas.toDataURL();
                    tablepng[4] = img;
                  })
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row" id="report-forecastmem">
          <div class="col-xs-12 table-responsive">
            <p class="lead">Forecast Used Memory</p>
            <div id="forecast_memory"></div>
            <script>
              $("#forecast_memory").load("reporting/linux/forecast_memory.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Forecast Memory: ";
                  $("#forecast_memory").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                } else {
                  html2canvas(document.getElementById('report-forecastmem')).then(function(canvas) {
                    var img = canvas.toDataURL();
                    tablepng[5] = img;
                  })
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row" id="report-forecastdisk">
          <div class="col-xs-12 table-responsive">
            <p class="lead">Forecast Free Disk</p>
            <div id="forecast_disk"></div>
            <script>
              $("#forecast_disk").load("reporting/linux/forecast_disk.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Forecast Free Disk: ";
                  $("#forecast_disk").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                } else {
                  html2canvas(document.getElementById('report-forecastdisk')).then(function(canvas) {
                    var img = canvas.toDataURL();
                    tablepng[6] = img;
                  })
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row">
          <div class="col-xs-12 table-responsive">
            <div id="cpuutil"></div>
            <script>
              $("#cpuutil").load("reporting/linux/cpuutil.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "CPU Utilization (%): ";
                  $("#cpuutil").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row">
          <div class="col-xs-12 table-responsive">
            <div id="memory_util"></div>
            <script>
              $("#memory_util").load("reporting/linux/memory_util.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Memory Utilization (%): ";
                  $("#memory_util").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row">
          <div class="col-xs-12 table-responsive">
            <div id="running_proc"></div>
            <script>
              $("#running_proc").load("reporting/linux/running_proc.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Number of running processes: ";
                  $("#running_proc").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row">
          <div class="col-xs-12 table-responsive">
            <div id="bits_sent"></div>
            <script>
              $("#bits_sent").load("reporting/linux/bits_sent.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Bits Sent: ";
                  $("#bits_sent").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row">
          <div class="col-xs-12 table-responsive">
            <div id="bits_received"></div>
            <script>
              $("#bits_received").load("reporting/linux/bits_received.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Bits Received: ";
                  $("#bits_received").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

        <div class="row">
          <div class="col-xs-12 table-responsive">
            <div id="spaceutil_graph"></div>
            <script>
              $("#spaceutil_graph").load("reporting/linux/spaceutil_graph.php?groupid=" + groupid + hostArr + timerange, function(response, status, xhr) {
                if (status == "error") {
                  var msg = "Space Utilization Graph: ";
                  $("#spaceutil_graph").html(msg + xhr.status + " " + xhr.statusText);
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  } else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }

                  $('#errorprint').html(printerror);
                }
              });
            </script>
          </div><!-- /.col -->
        </div><!-- /.row -->

      </section>
      <div class="clearfix"></div>
    </div><!-- /.content-wrapper -->
    <?php //include('footer.php'); 
    ?>

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

  <!-- Group Modal -->
  <div class="modal fade" id="groupModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="example-modal">
      <div class="modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" onclick="resetGroupForm()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Select Host Group</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; height:400px;">
              <form id="groupForm">
                <p><i>To select "All", click "Apply" wtihout checking on the checkboxes</i></p>
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    //get hostgroupid and name
                    $params = array(
                      "output" => array("groupid", "name"),
                      "selectHosts" => "extend",
                      "search" => array("name" => "Linux")
                    );
                    //call api problem.get only to get eventid
                    $result = $zbx->call('hostgroup.get', $params);
                    foreach ($result as $hostgroup) {
                      $getgroupid = $hostgroup["groupid"];
                      $getgroupname = $hostgroup["name"];

                      if (!empty($hostgroup["hosts"])) {
                        print "<tr>";
                        print "<td><input type='checkbox' name='groupid[]' value='$getgroupid'></td>";
                        print "<td>$getgroupname</td>";
                        print "<tr>";
                      } else {
                        continue;
                      }
                    }
                    ?>
                  </tbody>
                </table>
            </div>
            <input type="hidden" name="timefrom" value="<?php echo $timefrom; ?>">
            <input type="hidden" name="timetill" value="<?php echo $timetill; ?>">
            <div class="modal-footer">
              <button type="button" onclick="resetGroupForm()" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Apply</button>
            </div>
            </form>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->
    </div><!-- /.example-modal -->
  </div>

  <!-- Host  Modal -->
  <div class="modal fade" id="hostModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="example-modal">
      <div class="modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" onclick="resetHostForm()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Select Host</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; height:400px;">
              <form id="hostForm">
                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                <p><i>To select "All", click "Apply" wtihout checking on the checkboxes</i></p>
                <?php
                //get current groupid
                if (!empty($groupid)) {
                  foreach ($groupid as $value) {
                    echo '<input type="hidden" name="groupid[]" value="' . $value . '">';
                  }
                }
                ?>
                <table class="table table-bordered table-hover">
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
                    $result = $zbx->call('host.get', $params);
                    foreach ($result as $host) {
                      $gethostid = $host["hostid"];
                      $gethostname = str_replace("Zabbix", "Synthesis", $host["name"]);

                      print "<tr>";
                      print "<td><input type='checkbox' name='hostid[]' value='$gethostid'></td>";
                      print "<td>$gethostname</td>";
                      print "<tr>";
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

  <!-- Reporting Schedule Modal -->
  <div class="modal fade" id="reportingModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="example-modal">
      <div class="modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Schedule Reporting</h4>
            </div>
            <div id="reportingForm">
              <div class="modal-body">
                <!-- Loading Gif -->
                <div class="overlay" style="text-align: center;">
                  <i class="fa fa-refresh fa-spin"></i>
                </div>
              </div>
            </div>
            <script>
              function loadReportSchedModal() {
                var report_url = '<?php echo $report_url; ?>';
                $("#reportingForm").load("reporting_sched_form.php?report_url=" + report_url);
              }
            </script>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->
    </div><!-- /.example-modal -->
  </div>

  <script>
    //print report function
    function printReport() {
      //first, print the report from html
      setTimeout(function() {
        window.print();
      }, 1000);

      //second, print the graphs
      // if (confirm("Info:\nGraphs images will be exported into a separate document.\n\nExport graphs?")) {
      //   savePDF();
      // } 
    }
  </script>

  <script>
    //print report function
    function printReport() {
      //first, print the report from html
      setTimeout(function() {
        window.print();
      }, 1000);

      //second, print the graphs
      // if (confirm("Info:\nGraphs images will be exported into a separate document.\n\nExport graphs?")) {
      //   savePDF();
      // } 
    }
  </script>

  <script>
    var gen_report = <?php echo $gen_report ?>;

    //print graphs function ; assemble into one pdf
    function savePDF() {

      Promise.all(graphpng).then(function(res) {

        var pdfMake = res[0];

        // pdfmake is ready
        // Create document template
        doc = {
          pageSize: "A4",
          pageOrientation: "portrait",
          pageMargins: [30, 30, 30, 30],
          footer: {
            columns: [{
              image: 'data:image/png;base64,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',
              alignment: 'center',
              fit: [50, 50],
              margin: [0, 0, 0, 30]
            }, ]
          },
          content: []
        };

        // throw new Error("stop");
        for (var index = 0; index < tablepng.length; index++) {
          doc.content.push({
            image: tablepng[index],
            width: 530
          });
          // console.log(res[index]);
        }

        // chart snapshots
        for (var index = 1; index < graphpng.length; index++) {
          doc.content.push({
            image: res[index],
            width: 530
          });
          // console.log(res[index]);
        }

        // console.log(JSON.stringify(doc));

        //report name
        var reportname = '<?php echo $reportname; ?>';
        if (reportname == "") {
          reportname = groupname.substring(0, groupname.length - 1);
        }

        var filename = new Date().toJSON().slice(0, 10) + "_" + reportname;

        //if gen report == 0, open pdf
        if (gen_report == 0) {
          pdfMake.createPdf(doc).open();
        } else if (gen_report == 1) {

          var pdfDocGenerator = pdfMake.createPdf(doc);

          pdfDocGenerator.getBase64((data) => {

            $.post("report_gen_pdf.php", {
              name: filename,
              pdfbase: data
            });

          });
        }

      }).catch(e => {
        console.log(e);
      });
    }

    $(document).ready(function() {

      if (gen_report == 1) {
        // Check count initially
        checkCount();
      }

      // Custom function to check count and perform action
      function checkCount() {
        if (countcheck === 10) {
          // Perform action when count reaches 10
          console.log("ready to generate");
          setTimeout(savePDF, 5000)

        } else {
          // Wait and check again after a delay
          setTimeout(checkCount, 1000); // Check every 1 second
          console.log("not ready");
        }
      }

    });
  </script>

  <script>
    //reset group form
    function resetGroupForm() {
      $('#groupForm').trigger("reset");
    }
    //reset host form
    function resetHostForm() {
      $('#hostForm').trigger("reset");
    }
  </script>

  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>