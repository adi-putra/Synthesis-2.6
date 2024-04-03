<?php
//Author: Adiputra

include "session.php";

//only allow admin to see user list
if ($zabUtype !== "3") {
  //display error message box
  print '<script>alert("You do not have access to this page!");</script>';
  //go to login page
  print '<script>window.location.assign("dashboard.php");</script>';
}

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%ad %hh %im');
}

function secondsToClock($seconds)
{
  $clocktime = gmdate("H:i", $seconds);
  return $clocktime;
}

function getDayofWeek($number) {
  $arr = array('Sunday', 'Saturday', 'Friday', 'Thursday', 'Wednesday', 'Tuesday', 'Monday');
  $addzero = "";

  if (strlen($number) != 7) {
    $minus = 7 - strlen($number);
    for ($i=0; $i < $minus; $i++) { 
      $addzero .= "0";
    }
    $search2 = $addzero.$number;
  }
  else {
    $search2 = $number;
  }

  $search_arr = str_split($search2);
  $out2 = array();
  foreach($search_arr as $key => $value){
      if($value == 1){
          $out2[] = $arr[$key];
      }
  }

  return "(".implode(", ", array_reverse($out2)).")";//Monday, Wednesday, Friday
  //return $search2;
}

function getMonth($number) {
  $arr1 = array("January","February","March","April","May","June","July","August","September","October","November","December");
  $arr = array_reverse($arr1);
  $addzero = "";

  if (strlen($number) != 12) {
    $minus = 12 - strlen($number);
    for ($i=0; $i < $minus; $i++) { 
      $addzero .= "0";
    }
    $search2 = $addzero.$number;
  }
  else {
    $search2 = $number;
  }

  $search_arr = str_split($search2);
  $out2 = array();
  foreach($search_arr as $key => $value){
      if($value == 1){
          $out2[] = $arr[$key];
      }
  }

  return "(".implode(", ", array_reverse($out2)).")";//January, February
  //return $search2;
}
?>
<!DOCTYPE html>
<html>
  <?php include("head.php"); ?>
  <body class="skin-blue">
    <div class="wrapper">
      
    <?php include("header.php"); ?>
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Maintenance Create
            <small></small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Maintenance</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
           <form action="maintenance_generate.php" method="post" onsubmit="return confirm('Confirm update?');">
             <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#tab_1" data-toggle="tab">Maintenance</a></li>
                  <li><a href="#tab_2" data-toggle="tab">Periods</a></li>
                  <li><a href="#tab_3" data-toggle="tab">Host</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_1">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Name</th>
                            <td><input class="form-control" style="width: 50%;" type="text" name="main_name" value="" required /></td>
                        </tr>
                        <tr>
                            <th>Active from</th>
                            <td>
                              <input style="width: 50%;" type='datetime-local' class="form-control" name="main_activesince" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <th>Active till</th>
                            <td>
                              <input style="width: 50%;" type='datetime-local' class="form-control" name="main_activetill" value="" required />
                            </td>
                        </tr>
                        <tr>
                          <th>Description</th>
                          <td><textarea class="form-control" id="main_desc" name="main_desc" rows="4" cols="5" style="width: 50%;"></textarea></td>
                        </tr>
                    </table>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_2">
                    <table class="table table-bordered table-striped">
                      <tr>
                        <th>Periods</th>
                        <td>
                          <table id="periodtable" class="table table-bordered table-hover">
                            <tr>
                              <th>Period Type</th>
                              <th>Schedule</th>
                              <th>Period</th>
                            </tr>
                          </table>
                          <button id="addperiod_btn" class="btn btn-sm btn-flat btn-primary" data-toggle="modal" data-target="#periodModal" type="button">Add +</button>
                          <button id="clearperiod_btn" class="btn btn-sm btn-flat btn-danger" type="button">Clear all</button>
                        </td>
                      </tr>
                    </table>
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_3">
                    <table class="table table-bordered table-striped">
                      <tr>
                        <th>Host Group(s)</th>
                        <td>
                          <table id="groupTable" class="table table-bordered table-hover">
                            <tr>
                              <th>Name</th>
                            </tr>
                          </table>
                          <button id="addgroup_btn" class="btn btn-sm btn-flat btn-primary" data-toggle="modal" data-target="#groupModal" type="button">Add +</button>
                          <button id="cleargroup_btn" class="btn btn-sm btn-flat btn-danger" type="button">Clear all</button>
                        </td>
                      </tr>
                      <tr>
                        <th>Host(s)</th>
                        <td>
                          <table id="hostTable" class="table table-bordered table-hover">
                            <tr>
                              <th>Name</th>
                            </tr>
                          </table>
                          <button id="addhost_btn" class="btn btn-sm btn-flat btn-primary" data-toggle="modal" data-target="#hostModal" type="button">Add +</button>
                          <button id="clearhost_btn" class="btn btn-sm btn-flat btn-danger" type="button">Clear all</button>
                        </td>
                      </tr>
                    </table>
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- nav-tabs-custom -->
              <button class="btn btn-success" type="submit">Create</button>
              </form>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- ./wrapper -->

      <?php include("footer.php"); ?>

    <!-- Add One time Period Modal -->
    <div class="modal fade" id="periodModal" tabindex="-1" role="dialog" aria-labelledby="operationModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Choose Period Type</h4>
              </div>
              <div class="modal-body">
                <table class="table table-bordered table-striped">
                  <tr>
                    <th>Period Type</th>
                    <td>
                      <select class="form-control" id="periodType_select" name="periodType_select">
                        <option value="0">One time only</option>
                        <option value="2">Daily</option>
                        <option value="3">Weekly</option>
                        <option value="4">Monthly</option>
                      </select>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="GoToPeriodType();">Apply</button>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

    <!-- Add One time Period Modal -->
    <div class="modal fade" id="onetimeModal" tabindex="-1" role="dialog" aria-labelledby="operationModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
            <form id="onetimeform">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add Period (One time only)</h4>
              </div>
              <div class="modal-body" style="overflow-y: auto; height:400px;">
                <table class="table table-bordered table-striped">
                  <tr>
                    <th>Start Date</th>
                    <td>
                      <input id="onetime_startdate" type='datetime-local' class="form-control" name="onetime_startdate" />
                    </td>
                  </tr>
                  <tr>
                    <th>Maintenance Period Length</th>
                    <td>
                      <input type="number" class="form-control "id="onetime_periodday" name="onetime_periodday" min="0" max="999" value="0" />
                      <label for="onetime_periodday">Days</label>
                      <input type="number" class="form-control "id="onetime_periodhour" name="onetime_periodhour" min="0" max="23" value="1" />
                      <label for="onetime_periodhour">Hours</label>
                      <input type="number" class="form-control "id="onetime_periodminute" name="onetime_periodminute" min="0" max="59" value="0" />
                      <label for="onetime_periodminute">Minutes</label>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="modal-footer">
                <button id="onetime_reset" type="reset" class="btn btn-default pull-left">Reset</button>
                <button type="button" class="btn btn-success" onclick="addOneTime();">Apply</button>
              </div>
              </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

    <!-- Add Daily Period Modal -->
    <div class="modal fade" id="dailyModal" tabindex="-1" role="dialog" aria-labelledby="operationModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <form id="dailyform">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add Period (Daily)</h4>
              </div>
              <div class="modal-body" style="overflow-y: auto; height:400px;">
                <table class="table table-bordered table-striped">
                  <tr>
                    <th>Every day(s)</th>
                    <td>
                      <input id="daily_every" type='number' class="form-control" name="daily_every" min="0" max="99" value="1"/>
                    </td>
                  </tr>
                  <tr>
                    <th>Start Time</th>
                    <td>
                      <input id="daily_starttime" type='time' class="form-control" name="daily_starttime" />
                    </td>
                  </tr>
                  <tr>
                    <th>Maintenance Period Length</th>
                    <td>
                      <input type="number" class="form-control "id="daily_periodday" name="daily_periodday" min="0" max="999" value="0" />
                      <label for="onetime_periodday">Days</label>
                      <input type="number" class="form-control "id="daily_periodhour" name="daily_periodhour" min="0" max="23" value="1" />
                      <label for="onetime_periodhour">Hours</label>
                      <input type="number" class="form-control "id="daily_periodminute" name="daily_periodminute" min="0" max="59" value="0" />
                      <label for="onetime_periodminute">Minutes</label>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="modal-footer">
                <button id="daily_reset" type="reset" class="btn btn-default pull-left">Reset</button>
                <button type="button" class="btn btn-success" onclick="addDaily();">Apply</button>
              </div>
              </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

    <!-- Add Weekly Period Modal -->
    <div class="modal fade" id="weeklyModal" tabindex="-1" role="dialog" aria-labelledby="operationModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <form id="weeklyform">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add Period (Weekly)</h4>
              </div>
              <div class="modal-body" style="overflow-y: auto; height:400px;">
                <table class="table table-bordered table-striped">
                  <tr>
                    <th>Every week(s)</th>
                    <td>
                      <input id="weekly_every" type='number' class="form-control" name="weekly_every" min="0" max="99" value="1"/>
                    </td>
                  </tr>
                  <tr>
                    <th>Day of week</th>
                    <td>
                      <input type="checkbox" name="weekly_dayofweek" value="1" />
                      <label>Monday</label><br>
                      <input type="checkbox" name="weekly_dayofweek" value="2" />
                      <label>Tuesday</label><br>
                      <input type="checkbox" name="weekly_dayofweek" value="4" />
                      <label>Wednesday</label><br>
                      <input type="checkbox" name="weekly_dayofweek" value="8" />
                      <label>Thursday</label><br>
                      <input type="checkbox" name="weekly_dayofweek" value="16" />
                      <label>Friday</label><br>
                      <input type="checkbox" name="weekly_dayofweek" value="32" />
                      <label>Saturday</label><br>
                      <input type="checkbox" name="weekly_dayofweek" value="64" />
                      <label>Sunday</label><br>
                    </td>
                  </tr>
                  <tr>
                    <th>Start Time</th>
                    <td>
                      <input id="weekly_starttime" type='time' class="form-control" name="weekly_starttime" />
                    </td>
                  </tr>
                  <tr>
                    <th>Maintenance Period Length</th>
                    <td>
                      <input type="number" class="form-control "id="weekly_periodday" name="weekly_periodday" min="0" max="999" value="0" />
                      <label for="weekly_periodday">Days</label>
                      <input type="number" class="form-control "id="weekly_periodhour" name="weekly_periodhour" min="0" max="23" value="1" />
                      <label for="weekly_periodhour">Hours</label>
                      <input type="number" class="form-control "id="weekly_periodminute" name="weekly_periodminute" min="0" max="59" value="0" />
                      <label for="weekly_periodminute">Minutes</label>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="modal-footer">
                <button id="weekly_reset" type="reset" class="btn btn-default pull-left">Reset</button>
                <button type="button" class="btn btn-success" onclick="addWeekly();">Apply</button>
              </div>
              </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

    <!-- Add Monthly Period Modal -->
    <div class="modal fade" id="monthlyModal" tabindex="-1" role="dialog" aria-labelledby="operationModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <form id="monthlyform">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add Period (Monthly)</h4>
              </div>
              <div class="modal-body" style="overflow-y: auto; height:400px;">
                <table class="table table-bordered table-striped">
                  <tr>
                    <th>Month</th>
                    <td>
                      <input type="checkbox" name="monthly_month" value="1" />
                      <label>January</label><br>
                      <input type="checkbox" name="monthly_month" value="2" />
                      <label>February</label><br>
                      <input type="checkbox" name="monthly_month" value="4" />
                      <label>March</label><br>
                      <input type="checkbox" name="monthly_month" value="8" />
                      <label>April</label><br>
                      <input type="checkbox" name="monthly_month" value="16" />
                      <label>May</label><br>
                      <input type="checkbox" name="monthly_month" value="32" />
                      <label>June</label><br>
                      <input type="checkbox" name="monthly_month" value="64" />
                      <label>July</label><br>
                      <input type="checkbox" name="monthly_month" value="128" />
                      <label>August</label><br>
                      <input type="checkbox" name="monthly_month" value="256" />
                      <label>September</label><br>
                      <input type="checkbox" name="monthly_month" value="512" />
                      <label>October</label><br>
                      <input type="checkbox" name="monthly_month" value="1024" />
                      <label>November</label><br>
                      <input type="checkbox" name="monthly_month" value="2048" />
                      <label>December</label><br>
                    </td>
                  </tr>
                  <tr>
                    <th>Day of month</th>
                    <td>
                      <input id="monthly_dayofmonth" type='number' class="form-control" name="monthly_dayofmonth" min="0" max="31" value="0" />
                    </td>
                  </tr>
                  <tr>
                    <th>Start Time</th>
                    <td>
                      <input id="monthly_starttime" type='time' class="form-control" name="monthly_starttime" />
                    </td>
                  </tr>
                  <tr>
                    <th>Maintenance Period Length</th>
                    <td>
                      <input type="number" class="form-control "id="monthly_periodday" name="monthly_periodday" min="0" max="999" value="0" />
                      <label for="monthly_periodday">Days</label>
                      <input type="number" class="form-control "id="monthly_periodhour" name="monthly_periodhour" min="0" max="23" value="1" />
                      <label for="monthly_periodhour">Hours</label>
                      <input type="number" class="form-control "id="monthly_periodminute" name="monthly_periodminute" min="0" max="59" value="0" />
                      <label for="monthly_periodminute">Minutes</label>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="modal-footer">
                <button id="monthly_reset" type="reset" class="btn btn-default pull-left">Reset</button>
                <button type="button" class="btn btn-success" onclick="addMonthly();">Apply</button>
              </div>
              </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

        <!-- Host Group Modal -->
        <div class="modal fade" id="groupModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="example-modal">
            <div class="modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Select Host Group</h4>
                  </div>
                  <div class="modal-body">
                    <form id="groupForm">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th>Host Group</th>
                            <th>
                              <select class="form-control" name="group_select" id="group_select">
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

                                  if (!empty($hostgroup["hosts"])) {
                                      print '<option value="'.$getgroupid.'">'.$getgroupname.'</option>';
                                  } else {
                                      continue;
                                  }
                              }
                              ?>
                              </select>
                            </th>
                        </tr>
                    </table>
                  </div>
                  <div class="modal-footer">
                    <button id="group_reset" type="reset" class="btn btn-default pull-left">Reset</button>
                    <button type="button" class="btn btn-success" onclick="addGroup();">Apply</button>
                  </div>
                  </form>
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          </div><!-- /.example-modal -->
        </div>

        <!-- Host Modal -->
        <div class="modal fade" id="hostModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="example-modal">
            <div class="modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Select WAN</h4>
                  </div>
                  <div class="modal-body">
                  <form id="hostForm">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th>WAN</th>
                            <th>
                              <select class="form-control" name="host_select" id="host_select">
                              <?php
                              //get hostid and name
                              $params = array(
                                  "output" => array("hostid", "name")
                              );
                              //call api problem.get only to get eventid
                              $result = $zbx->call('host.get',$params);
                              foreach ($result as $host) {
                                if ($host["hostid"] != 10084) {
                                  $gethostid = $host["hostid"];
                                  $gethostname = $host["name"];
                                  
                                  print '<option value="'.$gethostid.'">'.$gethostname.'</option>';
                                }
                              }
                              ?>
                              </select>
                            </th>
                        </tr>
                    </table>
                    </div>
                    <div class="modal-footer">
                      <button id="host_reset" type="reset" class="btn btn-default pull-left">Reset</button>
                      <button type="button" class="btn btn-success" onclick="addHost();">Apply</button>
                    </div>
                  </form>
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          </div><!-- /.example-modal -->
        </div>

    <script>

      function clock_to_seconds(clock) {
        var hours = clock.substr(0,2);
        var minutes = clock.substr(3,4);

        var totalhours = hours * 3600;
        var totalminutes = minutes * 60;

        var totalseconds = totalhours + totalminutes;
        return totalseconds;
      }

      //Clear Period table
      $('#clearperiod_btn').click(function () {
          $("#periodtable").find("tr:gt(0)").remove();
      });

      //clear host group table
      $('#cleargroup_btn').click(function () {
          $("#groupTable").find("tr:gt(0)").remove();
      });

      //clear host table
      $('#clearhost_btn').click(function () {
          $("#hostTable").find("tr:gt(0)").remove();
      });
    
      
      //if addperiod btn is clicked reset all fields in all modals
      $("#addperiod_btn").click(function(){
        $('#onetime_reset').click();
        $('#daily_reset').click();
        $('#weekly_reset').click();
      });

      //if addgroup btn is clicked reset all fields in group modal
      $("#addgroup_btn").click(function(){
        $('#group_reset').click();
      });

      //if addhost btn is clicked reset all fields in host modal
      $("#addhost_btn").click(function(){
        $('#host_reset').click();
      });

      function GoToPeriodType() {
        var selected_periodtype = $('#periodType_select').val();
        
        if (selected_periodtype == 0) {
          $('#periodModal').modal('hide'); 
          $('#onetimeModal').modal('show'); 
        } 

        else if (selected_periodtype == 2) {
          $('#periodModal').modal('hide'); 
          $('#dailyModal').modal('show'); 
        }

        else if (selected_periodtype == 3) {
          $('#periodModal').modal('hide'); 
          $('#weeklyModal').modal('show'); 
        }

        else if (selected_periodtype == 4) {
          $('#periodModal').modal('hide'); 
          $('#monthlyModal').modal('show'); 
        }
        
      }

      function addOneTime() {
        var table = document.getElementById("periodtable");

        //count row in operation table
        var rowCount = $('#periodtable tr').size();

        //array count
        var arraycount = rowCount - 1;
        
        //get all input values
        var onetime_startdate = $('#onetime_startdate').val();
        var onetime_periodday = $('#onetime_periodday').val();
        var onetime_periodhour = $('#onetime_periodhour').val();
        var onetime_periodminute = $('#onetime_periodminute').val();

        //send values to input hidden
        var period_startdate = moment(onetime_startdate).unix();
        var period_period = (onetime_periodday * 86400) + (onetime_periodhour * 3600) + (onetime_periodminute * 60);
      

        //cell 1 innerhtml
        var cell1_innerhtml = "";
        cell1_innerhtml = cell1_innerhtml + "One time only";
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][timeperiod_type]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][every]" value="1" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][month]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][dayofweek]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][day]" value="1" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_time]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][period]" value="' + period_period + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_date]" value="' + period_startdate + '" />';

        //cell 2 innerhtml
        var cell2_innerhtml = moment(onetime_startdate).format('DD-MM-YYYY H:mm');

        //cell 3 innerhtml
        var cell3_innerhtml = onetime_periodday + "d " + onetime_periodhour + "h " + onetime_periodminute + "m";

        if (cell2_innerhtml != "Invalid date") {
          var row = table.insertRow(rowCount);

          // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
          var cell1 = row.insertCell(0);
          var cell2 = row.insertCell(1);
          var cell3 = row.insertCell(2);

          cell1.innerHTML = cell1_innerhtml;
          cell2.innerHTML = cell2_innerhtml;
          cell3.innerHTML = cell3_innerhtml;

          $('#onetimeModal').modal('hide');
        } 
        else {
          alert("Please insert date.");
        }
      }

      function addDaily() {
        var table = document.getElementById("periodtable");

        //count row in operation table
        var rowCount = $('#periodtable tr').size();

        //array count
        var arraycount = rowCount - 1;
        
        //get all input values
        var daily_starttime = $('#daily_starttime').val();
        var daily_every = $('#daily_every').val();
        var daily_periodday = $('#daily_periodday').val();
        var daily_periodhour = $('#daily_periodhour').val();
        var daily_periodminute = $('#daily_periodminute').val();

        //send values to input hidden
        var period_starttime = clock_to_seconds(daily_starttime);
        var period_every = $('#daily_every').val();
        var period_period = (daily_periodday * 86400) + (daily_periodhour * 3600) + (daily_periodminute * 60);

        //cell 1 innerhtml
        var cell1_innerhtml = "";
        cell1_innerhtml = cell1_innerhtml + "Daily";
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][timeperiod_type]" value="2" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][every]" value="' + period_every + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][month]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][dayofweek]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][day]" value="1" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_time]" value="' + period_starttime + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][period]" value="' + period_period + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_date]" value="0" />';

        //cell 2 innerhtml
        var cell2_innerhtml = "At " + daily_starttime + " every " + period_every + " day(s)";

        //cell 3 innerhtml
        var cell3_innerhtml = daily_periodday + "d " + daily_periodhour + "h " + daily_periodminute + "m";

        //check to validate all values before submit
        if (daily_starttime != "") {
          var row = table.insertRow(rowCount);

          // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
          var cell1 = row.insertCell(0);
          var cell2 = row.insertCell(1);
          var cell3 = row.insertCell(2);
          

          cell1.innerHTML = cell1_innerhtml;
          cell2.innerHTML = cell2_innerhtml;
          cell3.innerHTML = cell3_innerhtml;
          

          $('#dailyModal').modal('hide');
        } 
        else {
          alert("Please insert starting time.");
        }
      }

      function addWeekly() {
        var table = document.getElementById("periodtable");

        //count row in operation table
        var rowCount = $('#periodtable tr').size();

        //array count
        var arraycount = rowCount - 1;
        
        //get all input values
        var weekly_starttime = $('#weekly_starttime').val();
        var weekly_periodday = $('#weekly_periodday').val();
        var weekly_periodhour = $('#weekly_periodhour').val();
        var weekly_periodminute = $('#weekly_periodminute').val();


        //send values to input hidden
        var period_starttime = clock_to_seconds(weekly_starttime);
        var period_every = $('#weekly_every').val();
        var period_dayofweek = 0;
        $('input[name="weekly_dayofweek"]:checked').each(function(){ // iterate through each checked element.
          period_dayofweek += isNaN(parseInt($(this).val())) ? 0 : parseInt($(this).val());
        });
        var period_period = (weekly_periodday * 86400) + (weekly_periodhour * 3600) + (weekly_periodminute * 60);

        //cell 1 innerhtml
        var cell1_innerhtml = "";
        cell1_innerhtml = cell1_innerhtml + "Weekly";
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][timeperiod_type]" value="3" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][every]" value="' + period_every + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][month]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][dayofweek]" value="' + period_dayofweek + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][day]" value="1" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_time]" value="' + period_starttime + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][period]" value="' + period_period + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_date]" value="0" />';

        //cell 2 innerhtml
        var binaryDayofWeek = Number(period_dayofweek).toString(2);

        //get string day of week
        function getDayofWeek(number) {
          var result = null;
          var scriptUrl = "maintenance/binDayofWeek.php?number=" + number;
          $.ajax({
              url: scriptUrl,
              type: 'get',
              dataType: 'html',
              async: false,
              success: function(data) {
                  result = data;
              } 
          });
          return result;
        }

        var getDayofWeek = getDayofWeek(binaryDayofWeek);

        var cell2_innerhtml = "At " + weekly_starttime + " " + getDayofWeek + " every " + period_every + " week(s)";

        //cell 3 innerhtml
        var cell3_innerhtml = weekly_periodday + "d " + weekly_periodhour + "h " + weekly_periodminute + "m";

        //check to validate all values before submit
        if (weekly_starttime == "" || getDayofWeek == "()") {
          alert("Please insert starting time / day of week");
        }
        else {
          var row = table.insertRow(rowCount);

          // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
          var cell1 = row.insertCell(0);
          var cell2 = row.insertCell(1);
          var cell3 = row.insertCell(2);
          

          cell1.innerHTML = cell1_innerhtml;
          cell2.innerHTML = cell2_innerhtml;
          cell3.innerHTML = cell3_innerhtml;
          

          $('#weeklyModal').modal('hide');
        }
      }

      function addMonthly() {
        var table = document.getElementById("periodtable");

        //count row in operation table
        var rowCount = $('#periodtable tr').size();

        //array count
        var arraycount = rowCount - 1;
        
        //get all input values
        var monthly_starttime = $('#monthly_starttime').val();
        var monthly_periodday = $('#monthly_periodday').val();
        var monthly_periodhour = $('#monthly_periodhour').val();
        var monthly_periodminute = $('#monthly_periodminute').val();


        //send values to input hidden
        var period_starttime = clock_to_seconds(monthly_starttime);
        var period_dayofmonth = $('#monthly_dayofmonth').val();
        var period_month = 0;
        $('input[name="monthly_month"]:checked').each(function(){ // iterate through each checked element.
          period_month += isNaN(parseInt($(this).val())) ? 0 : parseInt($(this).val());
        });
        var period_period = (monthly_periodday * 86400) + (monthly_periodhour * 3600) + (monthly_periodminute * 60);

        //cell 1 innerhtml
        var cell1_innerhtml = "";
        cell1_innerhtml = cell1_innerhtml + "Monthly";
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][timeperiod_type]" value="4" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][every]" value="1" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][month]" value="' + period_month + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][dayofweek]" value="0" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][day]" value="' + period_dayofmonth + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_time]" value="' + period_starttime + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][period]" value="' + period_period + '" />';
        cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="period[' + arraycount + '][start_date]" value="0" />';

        //cell 2 innerhtml
        var binaryMonth = Number(period_month).toString(2);

        //get string day of month
        function getMonth(number) {
          var result = null;
          var scriptUrl = "maintenance/binMonth.php?number=" + number;
          $.ajax({
              url: scriptUrl,
              type: 'get',
              dataType: 'html',
              async: false,
              success: function(data) {
                  result = data;
              } 
          });
          return result;
        }

        var getMonth = getMonth(binaryMonth);

        var cell2_innerhtml = "At " + monthly_starttime + " on day " + period_dayofmonth + " of every " + getMonth;

        //cell 3 innerhtml
        var cell3_innerhtml = monthly_periodday + "d " + monthly_periodhour + "h " + monthly_periodminute + "m";

        //check to validate all values before submit
        if (monthly_starttime == "" || getMonth == "()") {
          alert("Please insert starting time / month");
        }
        else {
          var row = table.insertRow(rowCount);

          // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
          var cell1 = row.insertCell(0);
          var cell2 = row.insertCell(1);
          var cell3 = row.insertCell(2);
          

          cell1.innerHTML = cell1_innerhtml;
          cell2.innerHTML = cell2_innerhtml;
          cell3.innerHTML = cell3_innerhtml;
          

          $('#monthlyModal').modal('hide');
        }
      }

      /*function deletePeriod(x) {
          var cellAndRow = $(x).parents('td,tr');
          
          var cellIndex = cellAndRow[0].cellIndex
          var rowIndex = cellAndRow[1].rowIndex;
          
          document.getElementById("periodtable").deleteRow(rowIndex);
      }*/

      function addGroup() {
        var table = document.getElementById("groupTable");

        //count row in operation table
        var rowCount = $('#groupTable tr').size();

        //array count
        var arraycount = rowCount - 1;

        var selectgroup_text = $('#group_select').find(":selected").text();
        var selectgroup_val = $('#group_select').val();

        //check if selected group is already selected before
        var current_group = [];
        for (let index = 0; index < arraycount; index++) {
          current_group[index] = document.getElementsByName("main_groups[]")[index].value;
        }

        if (current_group.includes(selectgroup_val) == true) {
          alert("The selected host group is already in this maintenance operation.");
        } 
        else {
          var row = table.insertRow(rowCount);

          // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
          var cell1 = row.insertCell(0);

          var cell1_innerhtml = "";
          cell1_innerhtml = cell1_innerhtml + selectgroup_text;
          cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="main_groups[]" value="' + selectgroup_val + '" />';
        
          cell1.innerHTML = cell1_innerhtml;

          $('#groupModal').modal('hide');
        }
      }

      function addHost() {
        var table = document.getElementById("hostTable");

        //count row in operation table
        var rowCount = $('#hostTable tr').size();

        //array count
        var arraycount = rowCount - 1;

        var selecthost_text = $('#host_select').find(":selected").text();
        var selecthost_val = $('#host_select').val();

        //check if selected group is already selected before
        var current_host = [];
        for (let index = 0; index < arraycount; index++) {
          current_host[index] = document.getElementsByName("main_hosts[]")[index].value;
        }

        if (current_host.includes(selecthost_val) == true) {
          alert("The selected host is already in this maintenance operation.");
        } 
        else {
          var row = table.insertRow(rowCount);

          // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
          var cell1 = row.insertCell(0);

          var cell1_innerhtml = "";
          cell1_innerhtml = cell1_innerhtml + selecthost_text;
          cell1_innerhtml = cell1_innerhtml + '<input type="hidden" name="main_hosts[]" value="' + selecthost_val + '" />';
        
          cell1.innerHTML = cell1_innerhtml;

          $('#hostModal').modal('hide');
        }
      }
    </script>


    <!-- timepicker textbox -->
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
<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>