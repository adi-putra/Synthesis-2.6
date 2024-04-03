<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


//Author: Adiputra
  
include "session.php";

// get url and groupid
if (isset($_GET["report_url"])) {
  $url = $_GET["report_url"];
  $url_pos = stripos($url, "synthesis");
  $url = "http://localhost/".substr($url, $url_pos);
  $url_pos = stripos($url, "groupid") + 8;
  $groupid = substr($url, $url_pos);
}

// print $groupid;

// get reportid
$reportid = $_GET["report_id"] ?? 0;

//open file to return report json 
$genreport_file = fopen("reporting/gen_report.json", "r") or die("Unable to open file!");
$genreport_data =  fread($genreport_file,filesize("reporting/gen_report.json"));
fclose($genreport_file);

//decode report json 
$genreport_data = json_decode($genreport_data, true);

//count json data
$genreport_data_count = count($genreport_data);

//schedule reporting properties
$report_id = "";
$report_url = "";
$report_name = "";
$report_usrgrpids = [];
$report_userids = [];
$report_hostids = [];

// check if the report has been setup and call out its properties
if ($genreport_data_count != 0) {
  foreach ($genreport_data as $data) {

    if ($data["url"] == $url || $data["reportid"] == $reportid) {
      $report_id = $data["reportid"];
      $report_name = $data["name"];
      $report_usrgrpids = $data["usrgrpids"];
      $report_userids = $data["userids"];
      $report_url = $data["url"];
      $report_hostids = $data["hostids"];
      $report_schedule = $data["schedule"];
      $report_period = $data["period"];
  
      //schedule name
      if ($report_schedule == 1) {
        $report_schedule_name = "Daily";
      }
      else if ($report_schedule == 2) {
        $report_schedule_name = "Weekly";
      }
      else {
        $report_schedule_name = "Monthly";
      }
  
      //period name
      if ($report_period == 1) {
        $report_period_name = "Previous day";
      }
      else if ($report_period == 2) {
        $report_period_name = "Previous week";
      }
      else {
        $report_period_name = "Previous month";
      }
    }
  }
}

// check if report id and report url is null or blank
if ($report_id == "" && $report_url == "") {
  $report_id = $genreport_data_count + 1;
  $report_url = $url;
}

//get groupid from url
$url = $report_url;
$url_pos = stripos($url, "synthesis");
$url = "http://localhost/".substr($url, $url_pos);
$url_pos = stripos($url, "groupid") + 8;
$groupid = substr($url, $url_pos);

// print $report_id;
// print $groupid;
// print json_encode($report_hostids);
?>


<!DOCTYPE html>
<html>

  <body class="skin-blue">

    <div class="modal-body">
      <form id="reportschedForm">
        <input type="hidden" name="report_id" value="<?php echo $report_id; ?>" />
        <input type="hidden" name="report_url" value="<?php echo $report_url; ?>" />
        <table class="table table-bordered">
            <tr>
              <th>Name</th>
              <td><input class="form-control" name="report_name" type="text" value="<?php echo $report_name; ?>" required /></td>
            </tr>
            <tr>
              <th>Host(s) in group <i>(default: all)</i></th>
              <td>
                <select id="hostid_select" class="form-control" name="hostids[]" multiple="multiple" style="width: 100%;" required>
                  <?php
                  $params = array(
                      "output" => "extend",
                      "groupids" => $groupid
                  );
                  //call api problem.get only to get eventid
                  $result = $zbx->call('host.get',$params);
                  foreach ($result as $host) {
                    if (in_array($host["hostid"], $report_hostids)) {
                      print '<option value='.$host["hostid"].' selected>'.$host["name"].'</option>';
                    }
                    else {
                      print '<option value='.$host["hostid"].'>'.$host["name"].'</option>';
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>Email to user group(s)</th>
              <td>
                <select id="usrgrpid_select" class="form-control" name="usrgrpid[]" multiple="multiple" style="width: 100%;" required>
                  <?php
                  $params = array(
                      "output" => "extend"
                  );
                  //call api problem.get only to get eventid
                  $result = $zbx->call('usergroup.get',$params);
                  foreach ($result as $usrgrp) {
                    if (in_array($usrgrp["usrgrpid"], $report_usrgrpids)) {
                      print '<option value='.$usrgrp["usrgrpid"].' selected>'.$usrgrp["name"].'</option>';
                    }
                    else {
                      print '<option value='.$usrgrp["usrgrpid"].'>'.$usrgrp["name"].'</option>';
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>Email to user(s)</th>
              <td>
                <select id="userid_select" class="form-control" name="userid[]" multiple="multiple" style="width: 100%;" required>
                  <?php
                  $params = array(
                      "output" => "extend"
                  );
                  //call api problem.get only to get eventid
                  $result = $zbx->call('user.get',$params);
                  foreach ($result as $user) {
                    if (in_array($user["userid"], $report_userids)) {
                      print '<option value='.$user["userid"].' selected>'.$user["username"].'</option>';
                    }
                    else {
                      print '<option value='.$user["userid"].'>'.$user["username"].'</option>';
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>Schedule</th>
              <td>
                <select id="schedule_select" class="form-control" name="report_schedule" required>
                  <option value="<?php echo $report_schedule; ?>"><?php echo $report_schedule_name; ?></option>
                  <option disabled>──────────</option>
                  <option value="1">Daily</option>
                  <option value="2">Weekly</option>
                  <option value="3">Monthly</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>Period</th>
              <td>
                <select id="period_select" class="form-control" name="report_period" required>
                  <option value="<?php echo $report_period; ?>"><?php echo $report_period_name; ?></option>
                  <option disabled>──────────</option>
                  <option value="1">Previous day</option>
                  <option value="2">Previous week</option>
                  <option value="3">Previous month</option>
                </select>
              </td>
            </tr>
        </table>
      </form>
    </div>
    <div class="modal-footer">
      <button id="closeReportSchedModal" type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
      <button id="submitReportSchedForm" class="btn btn-success">Apply</button>
    </div>
  </body>
</html>

<!-- page script -->
<script type="text/javascript">
  $(document).ready(function() {
      $('#usrgrpid_select').select2();
      $('#userid_select').select2();
      $('#hostid_select').select2();

      //submit report sched form
      $("#submitReportSchedForm").click(function(){

        var execute_form = true;

        //get values from form
        var get_reportid = document.getElementsByName("report_id")[0].value;
        var get_reporturl = document.getElementsByName("report_url")[0].value;
        var get_reportname = document.getElementsByName("report_name")[0].value;
        var get_hostid = JSON.stringify($('#hostid_select').val());
        var get_usrgrpid = JSON.stringify($('#usrgrpid_select').val());
        var get_userid = JSON.stringify($('#userid_select').val());
        var get_schedule = $('#schedule_select').find(":selected").val();
        var get_period = $('#period_select').find(":selected").val();

        if (get_reportname == "") {
          alert("Please enter report name.");
          execute_form = false;
        }

        if (get_usrgrpid.length == 0 && get_userid.length == 0) {
          alert("Please add at least one user group or user.");
          execute_form = false;
        }

        if (get_schedule.length == 0) {
          alert("Please choose one schedule.");
          execute_form = false;
        }

        if (get_period.length == 0) {
          alert("Please choose one period.");
          execute_form = false;
        }

        if (execute_form == true) {
          // execute form
          $.post("reporting_sched_update.php", 
          {
            report_id: get_reportid,
            report_name: get_reportname,
            report_url: get_reporturl,
            hostid: get_hostid,
            usrgrpid: get_usrgrpid,
            userid: get_userid,
            report_schedule: get_schedule,
            report_period: get_period
          },
          function(data, status) {
            // alert(data);
            alert("Successfully updated scheduled report.");
          });

          $("#closeReportSchedModal").click();
        }

      });
  });

  //Timepicker
  $(".timepicker").timepicker({
    showInputs: false
  });
</script>