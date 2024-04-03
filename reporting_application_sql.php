<?php

include 'session.php';
//ini_set('memory_limit', '-1');

//start page with a group id
//initiate group id
/*$params = array(
  "output" => array("groupid", "name"),
  "search" => array("name" => "sql"),
);
$result = $zbx->call('hostgroup.get', $params);
foreach ($result as $row) {
  $getgroupid = $row["groupid"];
}*/

$groupid = $_GET["groupid"];

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
if ($counthost < 30) {
  $chgDateEnable = "display: all;";
} else {
  $chgDateEnable = "display: none;";
}
$formlink = "application_sql.php?" . $hostArr;

$timerange = "&timefrom=" . $timefrom . "&timetill=" . $timetill;

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


if (isset($_POST['submit'])) {
  // Execute this code if the submit button is pressed.
  $timefrom1 = $_POST['timefrom'];
  $timefrom = strtotime($timefrom1);
  $timetill1 = $_POST['timetill'];
  $timetill = strtotime($timetill1);
} else {
  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? strtotime('+3 minutes');
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
<script>
  //variable to count all output when checked
  var countcheck = 0;

  //variable to store graph png
  var graphpng = [];

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
                                                  "search" => array("name" => "sql"),
                                                );
                                                $result = $zbx->call('hostgroup.get', $params);
                                                foreach ($result as $row) {
                                                  $getgroupid = $row["groupid"];
                                                  $getgroupname = $row["name"];
                                                  if ($row["name"] != $groupname) {
                                                    $getgroupname = $row["name"];
                                                  } else {
                                                    continue;
                                                  }
                                                  print "<option value='reporting_application_sql.php?groupid=" . $getgroupid . "'>$getgroupname</option>";
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
                  <script>
                    $(document).ready(function(){
                    //$('#reportdiv').hide();
                      $('#chooseprint').hide();
                    });
                  </script>
                    <div id="chooseprint">
                    <table align="center" class="table table-bordered table-striped" style="width: 50%;">
                      <tr>
                          <td style="text-align: center;"><button onclick="printReport();" class="btn btn-default"><i class="fa fa-print"></i> Print/Save Report As PDF</button></td>
                      </tr>
                    </table>
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
          <div class="row">
            <div class="col-xs-12">
              <h2 class="page-header">
                <i class="fa fa-globe"></i> SQL Overview - Summary Report
                <small class="pull-right" id="date-report">Date: <?php echo date("d/m/Y", strtotime("now")); ?></small>
              </h2>
            </div><!-- /.col -->
          </div>

          <script>
            //initializers
            var counthost = <?php echo $counthost; ?>;
            var hostArr = '<?php echo $hostArr; ?>';
            var timefrom = '<?php echo $_GET["timefrom"]; ?>';
            var timetill = '<?php echo $_GET["timetill"]; ?>';
            var groupid = '<?php echo $groupid."&"; ?>';

            if (timefrom == "" && timetill == "") {
                var timerange = "";
            }
            else if (timetill == "") {
                var timerange = "&timefrom=" + timefrom;
            }
            else {
                var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            }
            </script>

        <!-- info row -->
          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              
            </div><!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <address>
                
              </address>
            </div><!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <b>Report Period: </b><?php echo date("d/m/Y h:i:s A", $timefrom)." - ".date("d/m/Y h:i:s A", $timetill); ?><br/>
              <br/>
              <b>User: </b><?php echo $zabUser; ?>
            </div><!-- /.col -->
          </div><!-- /.row -->


        <div class="row invoice-info">
            <div class="col-xs-12 table-responsive">
            <p class="lead">Info</p>
            <div id="getabout"></div>
            <script>
             $("#getabout").load("reporting/sql/info/getabout.php?groupid=" + groupid + "&" + hostArr, function( response, status, xhr ) {
                if ( status == "error" ) {
                  var msg = "Info: ";
                  $( "#getabout" ).html( msg + xhr.status + " " + xhr.statusText );
                  countcheck = countcheck + 1;

                  //show error in errorprint div
                  if (printerror == "") {
                    printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                  }
                  else {
                    printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                  }
        
                  $('#errorprint').html(printerror);
                }
              });
            </script>
            </div><!-- /.col -->
        </div><!-- /.row -->


          <!-- Issues row 
          <div class="row">
            <div class="col-xs-4 table-responsive">
                <p class="lead">Unresolved Problems</p>
              <div id="problem_count"></div>
                <script>
                //$("#problem_count").load("reporting/sql/issues/problem_count.php?groupid=" + groupid + hostArr + timerange);
                </script>
            </div>
            <div class="col-xs-4 table-responsive">
              <p class="lead">Resolved Problems</p>
              <div id="resolved_count"></div>
                <script>
                //$("#resolved_count").load("reporting/sql/issues/resolved_count.php?groupid=" + groupid + hostArr + timerange);
                </script>
            </div>
            <div class="col-xs-4 table-responsive">
              <p class="lead">Closed Problems</p>
              <div id="closed_count"></div>
                <script>
                //$("#closed_count").load("reporting/sql/issues/closed_count.php?groupid=" + groupid + hostArr + timerange);
                </script>
            </div>
          </div> /.row -->

          <!-- Databases status row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
                <p class="lead">Database Status</p>
              <div id="db_status"></div>
                <script>
                $("#db_status").load("reporting/sql/database_status/db_status.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Database Status: ";
                    $( "#db_status" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
                      printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
          
                    $('#errorprint').html(printerror);
                  }
                });
                </script>
            </div><!-- /.col -->
          </div><!-- /.row -->

          <!-- Databases sizes row -->
          <div class="row">
            <div class="col-xs-6 table-responsive">
                <p class="lead">Total Data File Size</p>
              <div id="datafile_size"></div>
                <script>
                $("#datafile_size").load("reporting/sql/database_size/datafile_size.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Total Data File Size: ";
                    $( "#datafile_size" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
                      printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
          
                    $('#errorprint').html(printerror);
                  }
                });
                </script>
            </div><!-- /.col -->
            <div class="col-xs-6 table-responsive">
              <p class="lead">Total Log File Size</p>
              <div id="logfile_size"></div>
                <script>
                $("#logfile_size").load("reporting/sql/database_size/logfile_size.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Total Log File Size: ";
                    $( "#logfile_size" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
                      printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
          
                    $('#errorprint').html(printerror);
                  }
                });
                </script>
            </div><!-- /.col -->
          </div><!-- /.row -->

          <!-- SQL Backup Fail row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
                <p class="lead">SQL Backup Fail</p>
              <div id="backup_fail_count"></div>
                <script>
                $("#backup_fail_count").load("reporting/sql/sql_backup_fail/backup_fail_count.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "SQL Backup Fail: ";
                    $( "#backup_fail_count" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
                      printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
          
                    $('#errorprint').html(printerror);
                  }
                });
                </script>
            </div><!-- /.col -->
          </div><!-- /.row -->

          <!-- Job Fail row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
                <p class="lead">Fail Job</p>
              <div id="fail_job"></div>
                <script>
                $("#fail_job").load("reporting/sql/fail_job/fail_job.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Fail job: ";
                    $( "#fail_job" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
                      printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
          
                    $('#errorprint').html(printerror);
                  }
                });
                </script>
            </div><!-- /.col -->
          </div><!-- /.row -->

          <!-- Performances row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <div id="write_latency"></div>
                <script>
                $("#write_latency").load("reporting/sql/performance/write_latency.php?groupid=" + groupid + hostArr + timerange , function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Data || Write Latency graph: ";
                    $( "#write_latency" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
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
              <div id="read_latency"></div>
                <script>
                $("#read_latency").load("reporting/sql/performance/read_latency.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Data || Read Latency graph: ";
                    $( "#read_latency" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
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
              <div id="read_throughput"></div>
                <script>
                $("#read_throughput").load("reporting/sql/performance/read_throughput.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Data || Read Throughput graph: ";
                    $( "#read_throughput" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
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
              <div id="sql_stats"></div>
                <script>
                $("#sql_stats").load("reporting/sql/performance/sql_stats.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "SQL Stats graph: ";
                    $( "#sql_stats" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
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
              <div id="replicamirror_stats"></div>
                <script>
                $("#replicamirror_stats").load("reporting/sql/performance/replicamirror_stats.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Replica/Mirroring Stats graph: ";
                    $( "#replicamirror_stats" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
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
              <div id="user_connections"></div>
                <script>
                $("#user_connections").load("reporting/sql/performance/user_connections.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "User Connections graph: ";
                    $( "#user_connections" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
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
              <div id="sql_errors"></div>
                <script>
                $("#sql_errors").load("reporting/sql/performance/sql_errors.php?groupid=" + groupid + hostArr + timerange, function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "SQL Errors graph: ";
                    $( "#sql_errors" ).html( msg + xhr.status + " " + xhr.statusText );
                    countcheck = countcheck + 1;

                    //show error in errorprint div
                    if (printerror == "") {
                      printerror = msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
                    else {
                      printerror = printerror + msg + xhr.status + " " + xhr.statusText + "<br>";
                    }
          
                    $('#errorprint').html(printerror);
                  }
                });
                </script>
            </div><!-- /.col -->
          </div><!-- /.row -->
            
          <br><br>

          <!-- this row will not appear when printing -->
          <div class="row no-print">
            <div class="col-xs-12">
              <!-- <button onclick="printReport();" class="btn btn-default"><i class="fa fa-print"></i> Print/Save Report As PDF</button> -->
            </div>
          </div>
       
      </section>
        <div class="clearfix"></div>
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
                    "search" => array("name" => "SQL")
                );
                //call api problem.get only to get eventid
                $result = $zbx->call('hostgroup.get',$params);
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
            <h4 class="modal-title">Select Host Group</h4>
          </div>
          <div class="modal-body" style="overflow-y: auto; height:400px;">
            <form id="hostForm">
            <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
            <p><i>To select "All", click "Apply" wtihout checking on the checkboxes</i></p>
            <?php
            //get current groupid
            if (!empty($groupid)) {
                foreach($groupid as $value) {
                    echo '<input type="hidden" name="groupid[]" value="'. $value. '">';
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
                $result = $zbx->call('host.get',$params);
                foreach ($result as $host) {
                    $gethostid = $host["hostid"];
                    $gethostname = $host["name"];

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

<script>

  //print report function
  function printReport() {
    //first, print the report from html
    setTimeout(function(){window.print();},1000);

    //second, print the graphs
    if (confirm("Export graphs?")) {
      savePDF();
    } 
  }
</script>

<script>
  //print graphs function ; assemble into one pdf
  function savePDF() {
  
  Promise.all(graphpng).then(function(res) { 
    
    var pdfMake = res[0];
    
    // pdfmake is ready
    // Create document template
    var doc = {
      pageSize: "A4",
      pageOrientation: "portrait",
      pageMargins: [30, 30, 30, 30],
      content: []
    };
    
    for (let index = 1; index < graphpng.length; index++) {
      doc.content.push({
        image: res[index],
        width: 530
      });
    }
    
    var filename = new Date().toJSON().slice(0,10) + "_" + groupid + "_graphs";
    pdfMake.createPdf(doc).download(filename + ".pdf");
    
  });

  $('#reportready').html("Done!");
}
</script>

<script>
//reset group form
function resetGroupForm(){
    $('#groupForm').trigger("reset");
}
//reset host form
function resetHostForm(){
    $('#hostForm').trigger("reset");
}
</script>

  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>