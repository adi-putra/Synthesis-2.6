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

$timefrom = $_GET['timefrom'] ?? strtotime('today');
$timetill = $_GET['timetill'] ?? time();

$params = array(
  "output" => "extend",
  "search" => array("name" => "Email SMTP")
);

$result = $zbx->call('mediatype.get', $params);
foreach ($result as $mediatype) {
  $mediatype_id = $mediatype["mediatypeid"];
  $mediatype_name = $mediatype["name"];
  $mediatype_type = $mediatype["type"];
  $mediatype_smtpserver = $mediatype["smtp_server"];
  $mediatype_smtphelo = $mediatype["smtp_helo"];
  $mediatype_smtpemail = $mediatype["smtp_email"];
  $mediatype_smtpport = $mediatype["smtp_port"];
  $mediatype_smtpauth = $mediatype["smtp_authentication"];
  $mediatype_smtpusername = $mediatype["username"];
  $mediatype_smtppassword = $mediatype["passwd"];
  $mediatype_status = $mediatype["status"];
  $mediatype_smtp_security = $mediatype["smtp_security"];
  $mediatype_smtp_verify_peer = $mediatype["smtp_verify_peer"];
  $mediatype_smtp_verify_host = $mediatype["smtp_verify_host"];
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
          SMTP Setup
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
          <li class="active">Setup</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <form action="smtp_update.php" method="post" onsubmit="return confirm('Confirm update?');">
                <input type="hidden" name="mediatype_id" value="<?php echo $mediatype_id; ?>" />
                <div class="box-body">
                  <table class="table table-bordered table-striped">
                    <tr>
                      <th>SMTP Server</th>
                      <td><input style="width: 50%;" class="form-control" type="text" name="mediatype_smtpserver" value="<?php echo $mediatype_smtpserver; ?>" required /></td>
                    </tr>
                    <tr>
                      <th>SMTP Server Port</th>
                      <td><input style="width: 50%;" class="form-control" type="text" name="mediatype_smtpport" value="<?php echo $mediatype_smtpport; ?>" /></td>
                    </tr>
                    <tr>
                      <th>SMTP Helo</th>
                      <td><input style="width: 50%;" class="form-control" type="text" name="mediatype_smtphelo" value="<?php echo $mediatype_smtphelo; ?>" required /></td>
                    </tr>
                    <tr>
                      <th>SMTP Email</th>
                      <td><input style="width: 50%;" class="form-control" type="email" name="mediatype_smtpemail" value="<?php echo $mediatype_smtpemail; ?>" required /></td>
                    </tr>
                    <tr>
                      <th>SMTP Authentication</th>
                      <td>
                        <?php
                        if ($mediatype_smtpauth == 1) {
                          $smtpauth_div = "display: all;";
                          print '<input id="smtp_noauth" type="radio" name="mediatype_smtpauth" value="0">
                                <label for="smtp_noauth">
                                  None
                                </label>
                                <br>
                                <input id="smtp_withauth" type="radio" name="mediatype_smtpauth" value="1" checked>
                                <label for="smtp_withauth">
                                  With Username and Password
                                </label>';
                        } else {
                          $smtpauth_div = "display: none;";
                          print '<input id="smtp_noauth" type="radio" name="mediatype_smtpauth" value="0" checked>
                                <label for="smtp_noauth">
                                  None
                                </label>
                                <br>
                                <input id="smtp_withauth" type="radio" name="mediatype_smtpauth" value="1">
                                <label for="smtp_withauth">
                                  With Username and Password
                                </label>';
                        }
                        ?>

                        <!-- SMTP Authentication Div -->
                        <div id="smtp_userpass" class="margin" style="<?php echo $smtpauth_div; ?>">
                          <table class="table table-bordered table-striped" style="width: auto;">
                            <tr style="display: none;">
                              <th>Username</th>
                              <td><input style="width: auto;" class="form-control" type="text" name="mediatype_smtpusername" value="<?php echo $mediatype_smtpusername; ?>" /></td>
                            </tr>
                            <tr>
                              <th>Password</th>
                              <td><input style="width: auto;" class="form-control" type="password" name="mediatype_smtppassword" value="<?php echo $mediatype_smtppassword; ?>" /></td>
                            </tr>
                          </table>
                        </div>

                      </td>
                    </tr>
                    <tr>
                      <th>Connection Security</th>
                      <td>
                        <?php
                        if ($mediatype_smtp_security == 0) {
                          $smtp_security_0 = "checked";
                          $smtp_security_div = "display: none;";
                        } else if ($mediatype_smtp_security == 1) {
                          $smtp_security_1 = "checked";
                          $smtp_security_div = "display: all;";
                        } else if ($mediatype_smtp_security == 2) {
                          $smtp_security_2 = "checked";
                          $smtp_security_div = "display: all;";
                        }
                        ?>
                        <input type="radio" name="mediatype_smtp_security" value="0" <?php echo $smtp_security_0; ?> />
                        <label>None</label><br>
                        <input type="radio" name="mediatype_smtp_security" value="1" <?php echo $smtp_security_1; ?> />
                        <label>TLS</label><br>
                        <input type="radio" name="mediatype_smtp_security" value="2" <?php echo $smtp_security_2; ?> />
                        <label>SSL</label>

                        <!-- SMTP Connection Security Div -->
                        <?php
                        if ($mediatype_smtp_security == 1 || $mediatype_smtp_security == 2) {
                          if ($mediatype_smtp_verify_peer == 1) {
                            $smtp_verify_peer = "checked";
                          }
                          if ($mediatype_smtp_verify_host == 1) {
                            $smtp_verify_host = "checked";
                          }
                        }
                        ?>
                        <div id="smtp_security_div" class="margin" style="<?php echo $smtp_security_div; ?>">
                          <table class="table table-bordered table-striped" style="width: auto;">
                            <tr>
                              <th>Verify Peer
                              <th>
                              <td>
                                <input type="checkbox" name="mediatype_smtp_verify_peer" value="1" <?php echo $smtp_verify_peer; ?> />
                              </td>
                            </tr>
                            <tr>
                              <th>Verify Host
                              <th>
                              <td>
                                <input type="checkbox" name="mediatype_smtp_verify_host" value="1" <?php echo $smtp_verify_host; ?> />
                              </td>
                            </tr>
                          </table>
                        </div>

                      </td>
                    </tr>
                    <tr>
                      <th>Status</th>
                      <td>
                        <?php
                        if ($mediatype_status == 0) {
                          print '<input id="mediatype_status" type="checkbox" name="mediatype_status" value="0" checked />';
                        } else {
                          print '<input id="mediatype_status" type="checkbox" name="mediatype_status" value="0" />';
                        }
                        ?>
                      </td>
                    </tr>
                  </table>
                </div><!-- /.box-body -->
                <div class="box-footer">
                  <button class="btn btn-primary margin" type="reset">Reset</button>
                  <button class="btn btn-success margin" type="submit">Update</button>
                  <button class="btn btn-default margin" data-toggle="modal" data-target="#testSMTPModal" type="button">Test Current Setup</button>
                </div>
              </form>
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- ./wrapper -->

    <?php include("footer.php"); ?>

    <!-- Test SMTP Modal -->
    <div class="modal fade" id="testSMTPModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" onclick="resetTestSMTP();" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Test SMTP Setup</h4>
              </div>
              <div class="modal-body">
                <form id="testSMTP_form" onsubmit="return false;">
                  <table class="table table-bordered table-hover">
                    <tr>
                      <th>Send to</th>
                      <td><input class="form-control" type="email" id="smtptest_sendto" name="testsmtp_sendto" value="synthesis@example.com" required /></td>
                    </tr>
                    <tr>
                      <th>Subject</th>
                      <td><input class="form-control" type="text" id="smtptest_subject" name="testsmtp_subject" value="Synthesis: Test SMTP" required /></td>
                    </tr>
                    <tr>
                      <th>Message</th>
                      <td><textarea class="form-control" rows="5" cols="10" id="smtptest_message" name="testsmtp_message" required>Test Message</textarea></td>
                    </tr>
                  </table>
              </div>
              <div class="modal-footer">
                <button type="button" onclick="resetTestSMTP();" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button onclick="testSMTP();" type="submit" class="btn btn-success pull-right">Test</button>
              </div>
              </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

    <!-- Test Result SMTP Modal -->
    <div class="modal fade" id="resultSMTPModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" onclick="resetResultSMTP();" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Test SMTP Result</h4>
              </div>
              <div class="modal-body">
                <div id="resultSMTP_div">Please wait...</div>
              </div>
              <div class="modal-footer">
                <button type="button" onclick="resetResultSMTP();" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>

    <!-- page script -->
    <script type="text/javascript">
      $(function() {
        $("#example1").dataTable();
      });
    </script>
    <script>
      //Flat red color scheme for iCheck
      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_flat-blue'
      });
    </script>
    <script>
      //toggle smtp auth div
      $(document).ready(function() {
        $('input[name=mediatype_smtpauth]').change(function() {
          if (this.value == 1) {
            $("#smtp_userpass").show();
          } else {
            $("#smtp_userpass").hide();
          }
        });
        $('input[name=mediatype_smtp_security]').change(function() {
          if (this.value == 0) {
            $("#smtp_security_div").hide();
          } else {
            $("#smtp_security_div").show();
          }
        });
      });

      //checkbox status
      var smtp_status = "<?php echo $mediatype_status; ?>";
      if (smtp_status == 0) {
        $('#mediatype_status').prop('checked', true);
      }

      function testSMTP() {
        //get values from smtp setup
        var get_smtpserver = document.getElementsByName("mediatype_smtpserver")[0].value;
        var get_smtpport = document.getElementsByName("mediatype_smtpport")[0].value;
        var get_smtpemail = document.getElementsByName("mediatype_smtpemail")[0].value;
        var get_smtpauth = $('input[name="mediatype_smtpauth"]:checked').val();
        if (get_smtpauth == 1) {
          //var get_smtpusername = document.getElementsByName("mediatype_smtpusername")[0].value;
          var get_smtppassword = document.getElementsByName("mediatype_smtppassword")[0].value;
        }
        var get_smtpsecurity = $('input[name="mediatype_smtp_security"]:checked').val();

        //get values from test smtp modal
        var get_smtpsendto = $('#smtptest_sendto').val();
        var get_smtpsubject = $('#smtptest_subject').val();
        var get_smtpmessage = $('textarea#smtptest_message').val();

        /*console.clear();
        console.log(get_smtpserver);
        console.log(get_smtpport);
        console.log(get_smtpemail);
        console.log(get_smtpauth);
        //console.log(get_smtpusername);
        console.log(get_smtppassword);
        console.log(get_smtpsecurity);
        console.log(get_smtpsendto);
        console.log(get_smtpsubject);
        console.log(get_smtpmessage);*/

        $.post("smtp_test.php", {
            smtp_server: get_smtpserver,
            smtp_auth: get_smtpauth,
            smtp_password: get_smtppassword,
            smtp_port: get_smtpport,
            smtp_email: get_smtpemail,
            smtp_security: get_smtpsecurity,
            smtp_sendto: get_smtpsendto,
            smtp_subject: get_smtpsubject,
            smtp_message: get_smtpmessage,
          },
          function(data, status) {
            //console.clear();
            //console.log("Data: " + data + "\nStatus: " + status);
            $('#resultSMTP_div').html(data);
          });

        $('#testSMTPModal').modal('hide');
        $('#resultSMTPModal').modal('show');
      }

      function resetTestSMTP() {
        $('#testSMTP_form').trigger("reset");
      }

      function resetResultSMTP() {
        $('#resultSMTP_div').html("Please wait...");
        $('#testSMTP_form').trigger("reset");
      }
    </script>

    <?php
    //$zbx->logout();//logout from zabbix API
    ?>

</body>

</html>