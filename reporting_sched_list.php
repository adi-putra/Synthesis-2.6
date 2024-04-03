<?php

//Author: Adiputra
  
include "session.php";

//open file to return report json 
$genreport_file = fopen("reporting/gen_report.json", "r") or die("Unable to open file!");
$genreport_data =  fread($genreport_file,filesize("reporting/gen_report.json"));
fclose($genreport_file);

//decode report json 
$genreport_data = json_decode($genreport_data, true);
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
            Reporting Schedule List
            <small></small>
          </h1>
          <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Setup</li>
          </ol> -->
        </section>

        <!-- Main content -->
        <section class="content">

            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <table id="reportsched_table" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Report Name</th>
                                        <th>Send to user group(s)</th>
                                        <th>Send to user(s)</th>
                                        <th>Schedule</th>
                                        <th>Period</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;

                                    //list all available report schedule
                                    foreach ($genreport_data as $data) {

                                      print '<tr>';

                                      print '<td>'.$count.'</td>';
                                      
                                      $data_url = substr(strstr($data["url"], "synthesis/"), strlen("synthesis/"));

                                      print '<td><a href="'.$data_url.'" target="_blank">'.$data["name"].'</a></td>';

                                      //user group column
                                      print "<td>";
                                      $params = array(
                                        "output" => array("usrgrpid", "name")
                                      );
                                      //call api
                                      $result = $zbx->call('usergroup.get', $params);
                                      foreach ($result as $usrgrp) {
                                        if (in_array($usrgrp["usrgrpid"], $data["usrgrpids"])) {
                                          print $usrgrp["name"]."<br>";
                                        }
                                      }
                                      print "</td>";

                                      //user column
                                      print "<td>";
                                      $params = array(
                                        "output" => array("userid", "username")
                                      );
                                      //call api
                                      $result = $zbx->call('user.get', $params);
                                      foreach ($result as $user) {
                                        if (in_array($user["userid"], $data["userids"])) {
                                          print $user["username"]."<br>";
                                        }
                                      }
                                      print "</td>";

                                      
                                      //schedule 
                                      print "<td>";
                                      if ($data["schedule"] == 1) {
                                        print "Daily";
                                      }
                                      else if ($data["schedule"] == 2) {
                                        print "Weekly";
                                      }
                                      else {
                                        print "Monthly";
                                      }
                                      print "</td>";

                                      //period 
                                      print "<td>";
                                      if ($data["period"] == 1) {
                                        print "Previous day";
                                      }
                                      else if ($data["period"] == 2) {
                                        print "Previous week";
                                      }
                                      else {
                                        print "Previous month";
                                      }
                                      print "</td>";

                                      print '<td>
                                              <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-block dropdown-toggle" data-toggle="dropdown">
                                                  <i class="fa fa-gear"></i>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                  <li><a href="#" onclick="loadReportSchedModal('.$data["reportid"].');" data-toggle="modal" data-target="#reportingModal">Edit</a></li>
                                                  <li><a href="#" onclick="testReportSchedModal('.$data["reportid"].');">Test</a></li>
                                                  <li><a href="#" onclick="delReportSched('.$data["reportid"].');">Delete</a></li>
                                                </ul>
                                              </div>
                                            </td>';

                                      print '</tr>';

                                      $count++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer"> 
                        </div>
                    </div>
                </div>
            </div>
          
        </section><!-- /.content -->

      </div><!-- ./wrapper -->

      <!-- Reporting Schedule Modal -->
      <div class="modal fade" id="reportingModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="example-modal">
          <div class="modal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">Please enter details for this scheduled report.</h4>
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
                  function loadReportSchedModal(reportid) {
                    $("#reportingForm").load("reporting_sched_form.php?report_id=" + reportid);
                  } 
                </script>
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
          </div><!-- /.modal -->
        </div><!-- /.example-modal -->
      </div>

      <!-- Test Reporting Schedule Modal -->
      <div class="modal fade" id="testsend_modal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="example-modal">
          <div class="modal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">Reporting Schedule Test.</h4>
                </div>
                  <div class="modal-body">
                    <!-- Loading Gif -->
                    <div id="reporting_sched_loader" class="overlay" style="text-align: center;"></div>
                  </div>
                  <p align="center" id="reporting_sched_status"></p>
                <script>
                  function testReportSchedModal(reportid) {

                    if (confirm("Make sure SMTP has been setup. Proceed to test this report?")) {

                      $('#testsend_modal').modal('show');
                      $("#reporting_sched_loader").html('<i class="fa fa-refresh fa-spin"></i>');
                      $("#reporting_sched_status").html("Generating report. Make sure all configurations are correct.");

                      $.ajax({
                        url: "report_gen.php?report_id=" + reportid,
                        success: function(result) {
                          $("#reporting_sched_status").html("Report has been generated. Sending to emails now.");
                          $.ajax({
                            url: "report_sendmail.php?report_id=" + reportid,
                            success: function(result) {
                              // smtp error
                              if (result != "") {
                                $("#reporting_sched_status").html(result);
                                $("#reporting_sched_loader").html('<i class="fa fa-circle-xmark" style="color: red;"></i>');
                              }
                              //smtp success
                              else {
                                $("#reporting_sched_status").html("Report successfully sent. Please check emails.");
                                $("#reporting_sched_loader").html('<i class="fa fa-circle-check" style="color: green;"></i>');
                              }
                            },
                            error: function(xhr, status, error) {
                              $("#reporting_sched_status").html(error);
                              $("#reporting_sched_loader").html('<i class="fa fa-circle-xmark" style="color: red;"></i>');
                            }
                          });
                        },
                        error: function(xhr, status, error) {
                          $("#reporting_sched_status").html(error);
                          $("#reporting_sched_loader").html('<i class="fa fa-circle-xmark" style="color: red;"></i>');
                        }
                      });
                    }

                    
                  } 
                </script>
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
          </div><!-- /.modal -->
        </div><!-- /.example-modal -->
      </div>
      
      <?php include("footer.php"); ?>

<?php
//$zbx->logout();//logout from zabbix API
?>

  </body>
</html>

<script>
  $(document).ready( function () {
      $('#reportsched_table').DataTable();
  });

  function delReportSched(report_id) {

    if (confirm("Confirm delete scheduled report?")) {
      location.assign("reporting_sched_delete.php?report_id=" + report_id);
    }

  }
</script>
