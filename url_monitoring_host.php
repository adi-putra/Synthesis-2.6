<?php

  include "session.php";

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();

?>
<!DOCTYPE html>
<html>
  <?php include("head.php");?>
  <body class="skin-blue">
    <div class="wrapper">
      
      <?php include("header.php");?>
      <?php include('sidebar.php'); ?>


      <!--start script -->
      <script>

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
      </script>
      <!-- end script -->

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>Web Scenario<small>Website List</small></h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Web Monitoring</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row"> 
              <div class="col-md-4">
                <!-- small box -->
                <div class="small-box bg-aqua">
                  <div class="inner">
                    <h3>
                      <div id="total_web"></div>
                      <script>

                        loadTotalWeb();
                        function loadTotalWeb(){
                          gettotal_web_xhr = $.ajax({
                            url: "urldetails/url/cards/total_web.php", 
                            success: function(result) {
                              $("#total_web").html(result);

                            },
                            complete: function() {
                              if (diff <= 604800 && timetill == "") {
                                setTimeout(loadTotalWeb, 60000);
                              }
                            }
                          });
                        }
                      </script>
                    </h3> 
                    <p>
                      Total URL
                    </p>
                    <div class="icon">
                      <i class="fa fa-link"></i>
                    </div>
                  </div>
                  <!-- <a href="#cpu_util" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                  </a> -->
                </div>
              </div><!-- ./col -->
              
              <div class="col-md-4">
                <!-- small box -->
                <div class="small-box bg-green">
                  <div class="inner">
                    <h3>
                      <div id="total_ok"></div>
                      <script>
                        loadTotalOk();
                        function loadTotalOk(){
                          gettotal_ok_xhr = $.ajax({
                            url: "urldetails/url/cards/total_ok.php", 
                            success: function(result) {
                              $("#total_ok").html(result);

                            },
                            complete: function() {
                              if (diff <= 604800 && timetill == "") {
                                setTimeout(loadTotalOk, 60000);
                              }
                            }
                          });
                        }
                      </script>
                    </h3>
                    <p>
                      Total OK
                    </p>
                    <div class="icon">
                      <i class="fa fa-thumbs-up"></i>
                    </div>
                  </div>
                  <!-- <a href="#memory_util" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                  </a> -->
                </div>
              </div><!-- ./col -->
              <div class="col-md-4">
                <!-- small box -->
                <div class="small-box bg-red">
                  <div class="inner">
                    <h3>
                      <div id="total_down"></div>
                      <script>
                        loadTotalDown();
                        function loadTotalDown(){
                          gettotal_down_xhr = $.ajax({
                            url: "urldetails/url/cards/total_down.php", 
                            success: function(result) {
                              $("#total_down").html(result);

                            },
                            complete: function() {
                              if (diff <= 604800 && timetill == "") {
                                setTimeout(loadTotalDown, 60000);
                              }
                            }
                          });
                        }
                      </script>
                    </h3>
                    <p>
                      Total Down
                    </p>
                    <div class="icon">
                      <i class="fa fa-thumbs-down"></i>
                    </div>
                  </div>
                  <!-- <a href="#num_processes" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                  </a> -->
                </div>
              </div><!-- ./col -->
              
          </div>

          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-body">

                  <div id="url_group_list">
                    <div class="overlay">
                      <i class="fa fa-refresh fa-spin"></i>
                    </div>
                  </div>
                  <script>
                    loadGroupList();
                    function loadGroupList(){
                      geturl_group_list_xhr = $.ajax({
                        url: "urldetails/url/urllist/urlgroup_list.php", 
                        success: function(result) {
                          $("#url_group_list").html(result);

                        },
                        complete: function() {
                          if (diff <= 604800 && timetill == "") {
                            setTimeout(loadGroupList, 60000);
                          }
                        }
                      });
                    }
                  </script>
                    
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
        
        </div><!-- /.content-wrapper -->
      <?php include("footer.php");?>

    </div><!-- ./wrapper -->
    
    <!-- page script -->

    <script>
      //abort ajax request if user exit the page
      //put this script at the end of page
      window.onbeforeunload = function(){
        
        gettotal_web_xhr.abort();
        gettotal_ok_xhr.abort();
        gettotal_down_xhr.abort();
        geturl_group_list_xhr.abort();
      }
    </script>
<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>