<?php

include 'session.php';
//$httpid = $_GET['httpid'];
$webgroup = $_GET['webgroup'];
$webgroup =  strtok($webgroup," ");


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
          <?= $webgroup ;?>
        </h1>

        <ol class="breadcrumb">
          <li><a href="url_monitoring_host.php"><i class="fa fa-dashboard"></i>Web Monitoring</a></li>
          <li class="active">Web Monitoring Details</li>
        </ol>
        <br><br>
        <!-- Select Host Dropdown -->
        <div class="form-group">

          <select class="form-control" name="selecthost" onchange="location = this.value;" style="display: inline-block; width: auto;">
            <option><?php echo $webgroup ?></option>
            <?php
              
                $params = array(
                  "output" => "extend",
                  "selectSteps" => "extend",
                  "selectTags" => "extend"
                );

                $result = $zbx->call('httptest.get', $params);
                foreach ($result as $item) {
                  $http_id = $item['httptestid'];
                  $host_id = $item['hostid'];

                  //get hostname for each web
                  $params = array(
                    "output" => "extend",
                    "hostids" => $host_id
                  );
        
                  $res_host = $zbx->call('host.get', $params);
                  if(!empty($res_host)){
                    $webgroups[] = $item['name'];
                  }
                }
                $uniqueValues = [];
                foreach ($webgroups as $getwebgroup) {
                    $uniqueValues[$getwebgroup] = $getwebgroup;
                }

                foreach ($uniqueValues as $getwebgroup) {

                  $getwebgroup = strtok($getwebgroup," ");
                  if ($getwebgroup != $webgroup) {
                    print "<option value='url_monitoring_details.php?webgroup=" . $getwebgroup . "'>$getwebgroup</option>";
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
        // Get Time Status, httpid, Time From and Till 
        getTimeStat = '<?php echo $status; ?>';
        if (getTimeStat == "Today" || getTimeStat == "Last 1 hour") {
          var intervalOn = true;
        }
        //take time value from server
       
        var webgroup = '<?php echo $webgroup; ?>';
        var timefrom = '<?php echo $timefrom ?>';
        var timetill = '<?php echo $timetill ?>';

        //take current time value
        var currTime = '<?php echo time(); ?>';
        var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;

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

        var url = "urldetails/url/performance/download_speed_scenario.php?webgroup=" + webgroup + timerange;

        // Load All Graph Function
        function loadAllGraph(timefrom, timetill) {
          interval = false; //if this function starts, stop interval
          timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;

          return true;
        }
      </script>
    

      <!-- Main content -->
      <section class="content">

        <!-- filter date -->
        <div class="row">
          <div class="col-md-12">
            <!-- Custom Tabs (Pulled to the right) -->
            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs pull-right">
                <li class="active"><a data-target="#tab_1-1" data-toggle="tab"><i class="fa fa-clock"></i> <?php echo $time_str; ?></a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active" id="tab_1-1">
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
              </div><!-- /.tab-content -->
            </div><!-- nav-tabs-custom -->
          </div><!-- /.col -->
        </div> <!-- /.row -->

        <!-- Cards -->
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body">
                <div class="row" id="mycontent-host">
                  <div class="col-xs-12">
                    <aside class="col-lg-2 col-md-3 col-sm-12">
                      <div class="collapsed-box affix-top" id="mysidebar-host" style="z-index: 1;">
                        <div class="box-header with-border">

                        </div>
                        <div class="box-body show">
                          <ul class="nav nav-pills nav-stacked overview-nav">
                            <form action="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" method="post">
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
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-1 hour"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 hour</a></li>
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-1 day"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 day</a></li>
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-2 days"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 2 days</a></li>
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-1 week"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 7 days</a></li>
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-1 month"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 1 month</a></li>
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-3 months"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 3 months</a></li>
                                    <li><a href="url_monitoring_details.php?httpid=<?php echo $httpid; ?>&timefrom=<?php echo strtotime("-6 months"); ?>&timetill=<?php echo strtotime("now"); ?>">Last 6 months</a></li>                                                                                                                            
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
                              <li><input type="checkbox" class="sub-box" value="systeminfo" checked><a href="#systeminfo">Web Info</a></li>
                              <li><input type="checkbox" class="sub-box" value="performance" checked><a href="#performance">Performance</a></li>
                            </div>
                          </ul>
                        </div>
                      </div>
                    </aside>

                    <div class="col-lg-10 col-md-9 col-sm-12" id="content-wrapper-host">
                      <!-- Issues/Problems -->

                      <!-- System Info -->
                      <div id="systeminfo" style="padding-bottom: 50px;">
                        <h3>Web Information</h3>
                        
                        <!-- web info -->
                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Web Info</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="web_info">
                              <div class="overlay">
                                <i class="fa fa-refresh fa-spin"></i>
                              </div>
                            </div>
                            <script>
                              loadWebInfo();
                              function loadWebInfo(){
                                getweb_info_xhr = $.ajax({
                                  url: "urldetails/url/systeminfo/webinfo_details.php?webgroup=" + webgroup, 
                                  success: function(result) {
                                    $("#web_info").html(result);

                                  },
                                  complete: function() {
                                    if (diff <= 604800 && timetill == "") {
                                      setTimeout(loadWebInfo, 60000);
                                    }
                                  }
                                });
                              }
                            </script>
                          </div>
                        </div>

                        <!-- last error Log -->
                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Last Error Messages Logs</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class="box-body">
                            <div id="err_log">
                              <div class="overlay">
                                <i class="fa fa-refresh fa-spin"></i>
                              </div>
                            </div>
                            <script>
                              loadErrorLog();
                              function loadErrorLog(){
                                geterr_log_xhr = $.ajax({
                                  url: "urldetails/url/systeminfo/error_messages_log.php?webgroup=" + webgroup + timerange, 
                                  success: function(result) {
                                    $("#err_log").html(result);

                                  },
                                  complete: function() {
                                    if (diff <= 604800 && timetill == "") {
                                      setTimeout(loadErrorLog, 60000);
                                    }
                                  }
                                });
                              }
                            </script>
                          </div>
                        </div>
                      </div>

                      <!-- Performance -->
                      <div id="performance" style="padding-bottom: 50px;">
                        <h3>Performance</h3>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Download Speed Graph</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class='box-body'>
                            <div id="download_speed_step">
                              <div class="overlay">
                                <i class="fa fa-refresh fa-spin"></i>
                              </div>
                            </div>
                            <script>
                              loadSpeed();
                              function loadSpeed(){
                                getdownload_speed_step_xhr = $.ajax({
                                  url: "urldetails/url/performance_group/download_speed_step.php?webgroup=" + webgroup + timerange, 
                                  success: function(result) {
                                    $("#download_speed_step").html(result);

                                  },
                                  complete: function() {
                                    if (diff <= 604800 && timetill == "") {
                                      setTimeout(loadSpeed, 60000);
                                    }
                                  }
                                });
                              }
                            </script>
                          </div>
                        </div>

                        <div class="box box-solid box-default">
                          <div class="box-header">
                            <h3 class="box-title">Response Time For Step Graph</h3>
                            <div class="box-tools pull-right">
                              <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            </div>
                          </div>
                          <div class='box-body'>
                            <div id='resp_time_step'>
                              <div class="overlay">
                                <i class="fa fa-refresh fa-spin"></i>
                              </div>
                            </div>
                            <script>

                              loadRespTime();
                              function loadRespTime(){
                                getdresp_time_step_xhr = $.ajax({
                                  url: "urldetails/url/performance_group/resp_time_step.php?webgroup=" + webgroup + timerange, 
                                  success: function(result) {
                                    $("#resp_time_step").html(result);

                                  },
                                  complete: function() {
                                    if (diff <= 604800 && timetill == "") {
                                      setTimeout(loadRespTime, 60000);
                                    }
                                  }
                                });
                              }
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

  <script>
    //abort ajax request if user exit the page
    //put this script at the end of page
    window.onbeforeunload = function(){
      
      getweb_info_xhr.abort();
      geterr_log_xhr.abort();
      getdownload_speed_step_xhr.abort();
      getdresp_time_step_xhr.abort();
    }
  </script>

  <?php
    $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>