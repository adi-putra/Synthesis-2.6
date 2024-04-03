<?php

include 'session.php';

$hostid = $_GET['hostid'];
$groupid = $_GET["groupid"];

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

//get values for alerts and notifications
$ack_alert = $_GET['ack_alert'] ?? 0; 

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
} else if ($diff >= 7948800 and $diff < 15897600) {
  $status = "Last 3 months";
} else if ($diff >= 15897600) {
  $status = "Last 6 months";
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

          <!-- Alerts and Notifications -->
          <?php
          //acknowledgment alerts
          /*if ($ack_alert == 1) {
            print '<div class="row">
                    <div class="col-md-12">
                        <div class="box-body">
                          <div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h4>	<i class="icon fa fa-check"></i> Success!</h4>
                            Successfully perform acknowledgments on a problem.
                          </div>
                        </div><!-- /.box-body -->
                    </div><!-- /.col -->
                  </div>';
          }*/
          ?>

      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Problems - Closed Problems (User)
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Problems</li>
        </ol>
      </section>

      <?php
        //display time format string
        $time_str = date("d/m/Y H:i A", $timefrom)." - ".date("d/m/Y H:i A", $timetill);
        
        $hostArr = "";
        $groupArr = "";

        //if both arrays empty, let it empty
        if (!empty($hostid)) {
            foreach ($hostid as $hostID) {
                $hostArr .= "hostid[]=".$hostID."&";
            }
        }

        if (!empty($groupid)) {
            foreach ($groupid as $groupID) {
                $groupArr .= "groupid[]=".$groupID."&";
            }
        }

        $hist_timerange = "&timefrom=".strtotime("today")."&timetill=".strtotime("now");

        //link for history page
        $history_link = "history.php?".$groupArr.$hostArr.$hist_timerange;

        //link for problems page
        $problems_link = "problems.php?".$groupArr.$hostArr.$hist_timerange;
      ?>

      <script>
          //take time value from server
          var hostid = '<?php echo $hostArr; ?>';
          var groupid = '<?php echo $groupArr; ?>';
          var timefrom = '<?php echo $timefrom ?>';
          var timetill = '<?php echo $timetill ?>';
          
          //take current time value
          var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
      </script>

      <!-- Main content -->
      <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs (Pulled to the right) -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs pull-right">
                    <li><a href="#tab_1-1" data-toggle="tab"><i class="fa fa-clock"></i> <?php echo $time_str; ?></a></li>
                    <li class="active"><a href="#tab_2-2" data-toggle="tab"><i class="fa fa-filter"></i> Filter</a></li>
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
                    //get host group name and display in tbGroup
                    $tbGroupVal = "";
                    if (!empty($groupid)) {
                        foreach ($groupid as $groupID) {
                            $params = array(
                                "output" => array("groupid", "name"),
                                "groupids" => $groupID
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('hostgroup.get',$params);
                            foreach ($result as $hostgroup) {
                                $tbGroupVal .= $hostgroup["name"].", ";
                            }
                        }
                        $tbGroupVal = rtrim($tbGroupVal, ", ");
                    }
                    else {
                        $tbGroupVal = "All";
                    }

                    //get host group name and display in tbGroup
                    $tbHostVal = "";
                    if (!empty($hostid)) {
                        foreach ($hostid as $hostID) {
                            $params = array(
                                "output" => array("hostid", "name"),
                                "hostids" => $hostID
                            );
                            //call api problem.get only to get eventid
                            $result = $zbx->call('host.get',$params);
                            foreach ($result as $host) {
                                $tbHostVal .= $host["name"].", ";
                            }
                        }
                        $tbHostVal = rtrim($tbHostVal, ", ");
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
                                            <input style="width: 50%;" id="tbGroup" type="text" class="form-control" type="text" name="group" value='<?php echo $tbGroupVal; ?>' disabled><br>
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#groupModal" type="button">Select</button>
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
                                <a href='closedprob.php?&timefrom=<?php echo $timefrom;?>&timetill=<?php echo $timetill;?>'><button class="btn btn-default margin">Reset</button></a>
                            </div>
                        </div>
                    </div><!-- /.tab-pane -->
                    </div><!-- /.tab-content -->
                </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
        </div> <!-- /.row -->

        <!-- PROBLEMS TABLE -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-body">
                        <div class="row">
                        <div class="col-md-12">
                        <!-- Custom Tabs -->
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                            <li><a href='<?php echo $problems_link; ?>'>Unresolved Problems</a></li>
                            <li><a href='<?php echo $history_link; ?>'>Resolved Problems (System)</a></li>
                            <li  class="active"><a href="#tab_3" data-toggle="tabs"> Closed Problems (User)</a></li>
                            </ul>
                            <div class="tab-content">
                            <div class="tab-pane" id="tab_1">
                                
                            </div><!-- /.tab-pane -->
                            <div class="tab-pane" id="tab_2">
                                
                            </div><!-- /.tab-pane -->
                            <div class="tab-pane active" id="tab_3">
                                <div id="closedprob">
                                    <!-- Loading Gif -->
                                    <div class="overlay">
                                        <i class="fa fa-refresh fa-spin"></i>
                                    </div>
                                </div>
                                <script>
                                    //begin page by start the interval
                                    loadClosedTable();    
                                    //var closedprob = setInterval(loadClosedTable, 60000);               

                                    //ajax call to load content                                                                      
                                    function loadClosedTable() {
                                      $("#closedprob").load("problems/closedprob_table.php?" + groupid + hostid + timerange, function( response, status, xhr ) {
                                        if ( status == "error" ) {
                                          var msg = "Info: ";
                                          $( "#closedprob" ).html( msg + xhr.status + " " + xhr.statusText );
                                        }
                                      });
                                    }  

                                    //if click button acknowledge problem, stop interval
                                    function stopIntClosedTable() {
                                        console.log("stop prob int");
                                        clearInterval(closedprob);
                                    }

                                    //if done or close the ack form, start interval back
                                    function startIntClosedTable() {
                                        console.log("start prob int");
                                        //refresh problem table after 1 sec
                                        setTimeout(loadClosedTable, 1000);
                                        //closedprob = setInterval(loadClosedTable, 60000);
                                    }         
                                </script>
                            </div><!-- /.tab-pane -->
                            </div><!-- /.tab-content -->
                        </div><!-- nav-tabs-custom -->
                    </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Host Group Modal -->
        <div class="modal fade" id="groupModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="example-modal">
            <div class="modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" onclick="resetGroupForm()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Select Host Group</h4>
                  </div>
                  <div class="modal-body">
                    <form id="groupForm">
                    <!-- <p><i>To select "All", click "Apply" wtihout checking on the checkboxes</i></p> -->
                    <table class="table table-bordered table-hover" id="groupmodal_table">
                        <thead>
                            <tr>
                              <th><!-- Select All hosts cb --><input type="checkbox" id="checkall_groupid"> Select All</th>
                              <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        //get hostgroupid and name
                        $params = array(
                            "output" => array("groupid", "name"),
                            "selectHosts" => "extend"
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('hostgroup.get',$params);
                        foreach ($result as $hostgroup) {
                            $getgroupid = $hostgroup["groupid"];
                            $getgroupname = $hostgroup["name"];

                            $group_checked = "";
                            $group_checked_bool = 0;
                            
                            if (in_array($getgroupid, $groupid)) {
                              $group_checked = "checked";
                              $group_checked_bool = 1;
                            }

                            if (!empty($hostgroup["hosts"])) {
                                print "<tr>";
                                print "<td><input type='checkbox' name='groupid[]' value='$getgroupid' $group_checked><p style='visibility: hidden; display: none;'>".$group_checked_bool."</p></td>";
                                print "<td>$getgroupname</td>";
                                print "</tr>";
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


        <!-- Host Group Modal -->
        <div class="modal fade" id="hostModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="example-modal">
            <div class="modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" onclick="resetHostForm()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Select Host</h4>
                  </div>
                  <div class="modal-body">
                    <form id="hostForm">
                    <!-- <p><i>To select "All", click "Apply" wtihout checking on the checkboxes</i></p> -->
                    <?php
                    //get current groupid
                    if (!empty($groupid)) {
                        foreach($groupid as $value) {
                            echo '<input type="hidden" name="groupid[]" value="'. $value. '">';
                        }
                    }
                    ?>
                    <table class="table table-bordered table-hover" id="hostmodal_table">
                        <thead>
                            <tr>
                              <th><!-- Select All hosts cb --><input type="checkbox" id="checkall_hostid"> Select All</th>
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
        </script>
      </section>

                            





    </div><!-- /.content-wrapper -->
    <?php include("footer.php"); ?>
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

    //HOST AND GROUP MODAL

    //group modal form
    function groupform_check() {
      if ($("input:checkbox[name='groupid[]']:checked").length == $("input:checkbox[name='groupid[]']").length) {
        $("input:checkbox[name='groupid[]']").prop('checked', false);
      }
    }

    //host modal form
    function hostform_check() {
      if ($("input:checkbox[name='hostid[]']:checked").length == $("input:checkbox[name='hostid[]']").length) {
        $("input:checkbox[name='hostid[]']").prop('checked', false);
      }
    }

    //group modal checkbox check all 
    $("#checkall_groupid").click(function() {
      $("input:checkbox[name='groupid[]']").not(this).prop('checked', this.checked);
    });

    //host modal checkbox check all 
    $("#checkall_hostid").click(function() {
      $("input:checkbox[name='hostid[]']").not(this).prop('checked', this.checked);
    });

    $("#hostModal").show(); // Show the modal

    $('#hostModal').on('shown.bs.modal', function(e) {
      if (!$.fn.DataTable.isDataTable('#hostmodal_table')) {
        $('#hostmodal_table').DataTable({
          "autoWidth": true,
          "order": [
            [0, "desc"]
          ],
          'columnDefs': [{
            'targets': [0],
            /* column index */
            'orderable': false,
            /* true or false */
          }],
          "scrollY": '400px',
          "scrollCollapse": true,
          "paging": false
        });
      }
    });

    $("#hostModal").hide(); // Show the modal

    $("#groupModal").show(); // Show the modal

    $('#groupModal').on('shown.bs.modal', function(e) {
      if (!$.fn.DataTable.isDataTable('#groupmodal_table')) {
        $('#groupmodal_table').DataTable({
          "order": [
            [0, "desc"]
          ],
          'columnDefs': [{
            'targets': [0],
            /* column index */
            'orderable': false,
            /* true or false */
          }],
          "scrollY": '400px',
          "scrollCollapse": true,
          "paging": false
        });
      }
    });

    $("#groupModal").hide(); // Show the modal
  </script>


  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>