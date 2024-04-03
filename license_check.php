<?php

    //Author: Adiputra


//   ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
//   include 'session.php';

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();
  //display time format
  $diff = $timetill - $timefrom;
  if ($diff == 3600) {
    $status = "Last 1 hour";
  }
  else if ($diff < 86400) {
    $status = "Today";
  }
  else if ($diff == 86400) {
    $status = "Last 1 day";
  }
  else if ($diff == 172800) {
    $status = "Last 2 days";
  }
  else if ($diff == 604800) {
    $status = "Last 7 days";
  }
  else if ($diff == 2592000) {
    $status = "Last 30 days";
  }
?>
<!DOCTYPE html>
<html>
  
  <?php include("head.php"); ?>

  <body class="skin-blue">
    <div class="wrapper">
      
    <?php include('header.php'); ?>

    <?php //include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            License
            <small>Synthesis</small>
          </h1>
        </section>

        <?php
        //check file is encoded
        $encoded_check = json_encode(ioncube_file_is_encoded());

        //print $encoded_check;
        ?>

          <section class="content">
            <div class="row">
              <div class="col-md-12">
                <div class="box">
                  <div class="box-header">
                    <h3 class="box-title"><b>License properties</b></h3>
                  </div>
                    <div class="box-body">
                      <table class="table table-bordered table-hover">
                        <tr>
                          <th>License Status</th>
                            <?php
                            if ($encoded_check == "false") {
                              print '<td>File not encoded.</td>';
                            }
                            else {
                              //check license
                              $license_check = json_encode(ioncube_license_has_expired());

                              if ($license_check == "true") {
                                print '<td style="color: red;">Expired</td>';
                              }
                              else if ($license_check == "false") {
                                print '<td style="color: green;">Active</td>';
                              }
                            }
                            ?>
                        </tr>
                        <tr>
                          <th>Expiration Date</th>
                          <td>
                            <?php
                             //check license exp date
                             $file_info = ioncube_file_info();

                             if (!empty($file_info)) {
                               $license_expdate = $file_info["FILE_EXPIRY"];
                               $license_expdate = date("d/m/Y h:i A", $license_expdate);

                               print $license_expdate;
                             }
                             else {
                              print "File not encoded.";
                             }
                            ?>
                          </td>
                        </tr>
                        <tr>
                          <th>License matches server</th>
                          <?php 
                          if ($encoded_check == "false") {
                            print '<td>File not encoded.</td>';
                          }
                          else {
                            $license_prop = json_encode(ioncube_license_matches_server()); 

                            if ($license_prop == "true") {
                              print '<td style="color: green">Valid server</td>';
                            }
                            else {
                              print '<td style="color: red">Invalid server</td>';
                            }
                          }
                          ?>
                        </tr>
                      </table>
                    </div>
                    <div class="box-footer">
                      <button type="button" class="btn btn-success" data-toggle="modal" data-target="#licModal" data-toggle="tooltip" data-placement="right" title="Change License">Change License</button></button>
                    </div>
                </div>
                <h4><i>This is a private page. Click "Synthesis" on the page header above to return back to dashboard.</i></h4>
              </div>
            </div>
          </section>

        </div><!-- ./wrapper -->
        
      <?php
      include("footer.php");
      ?>

    <!-- Modal to load form -->
    <div class="modal fade" id="licModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="example-modal">
        <div class="modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="license_change.php" method="post">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Change License</h4>
              </div>
              <div class="modal-body">
                <p>Paste the license code that was given from your provider below.</p>
                <textarea class="form-control" rows="5" cols="10" name="license_text"></textarea>
              </div>
              <div class="modal-footer">
                <button type="reset" class="btn btn-default pull-left">Reset</button>
                <button type="submit" class="btn btn-success">Apply</button>
              </div>
              </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div><!-- /.example-modal -->
    </div>
    

<?php
// $zbx->logout();//logout from zabbix API
?>

  </body>
</html>