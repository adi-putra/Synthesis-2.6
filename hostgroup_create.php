<?php

include 'session.php';

//only allow admin to see user list
if ($zabUtype !== "3") {
  //display error message box
  print '<script>alert("You do not have access to this page!");</script>';
  //go to login page
  print '<script>window.location.assign("dashboard.php");</script>';
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

      <?php

      if (isset($_POST['addgroup'])) {
          
        if(isset($_POST['groupname'])){

            $groupname = $_POST['groupname'];
            $params = array(
                "name" => $groupname,
            );
            // call api
            $result = $zbx->call('hostgroup.create',$params);
            
            if($result){

                // echo '<div class="alert" style="padding: 20px;background-color: green ;color: white;">New host group successfully added!</div>'; 
                // header("Refresh: 5;");
                //display message box Record Been Added
                print '<script>alert("Successfully added host group.");</script>';

                //go to hostgroup.php page
                print '<script>window.location.assign("hostgroup_list.php");</script>';

            }
        }
      }

      ?>
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
            Hosts Group
            <small> Create</small>
          </h1>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="hostgroup_list.php">Host Group</li></a>
          <li class="active">Host Group Create</li>
        </ol>
        <!-- Select Host Dropdown -->
        <br>
        <div class="row">
          <div class="col-md-12">
            <!-- general form elements disabled -->
            <div class="box box-primary">
              <div class="box-body">
                <form action="" method="post" enctype="multipart/form-data">
                  <table class="table table-bordered table-striped">
                    <tr>
                      <th>Group Name</th>
                      <td><input style="width: 50%;"  name="groupname" type="text" class="form-control" placeholder="Enter group name" required/></td>
                    </tr>
                  </table>
                  <button type="submit" name="addgroup" class="btn btn-success margin" value="addgroup">Submit</button>
                  <button type="reset" class="btn btn-default margin">Reset</button>
                </form>
              </div><!-- /.box-body -->
            </div><!-- /.box -->
          </div><!--/.col (right) -->
        </div>
      </section>

    </div><!-- /.content-wrapper -->

    <?php include("footer.php"); ?>

    </div><!-- ./wrapper -->

    <!-- Bootstrap 3.3.2 JS -->
  <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  <!-- DATA TABES SCRIPT -->
  <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/select/1.2.1/js/dataTables.select.min.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/v/dt/dt-1.10.16/r-2.2.1/datatables.min.js"></script>
  <!-- Morris.js charts -->
  <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  <script src="plugins/morris/morris.min.js" type="text/javascript"></script>


  <script src="https://cdn.jsdelivr.net/momentjs/2.14.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">

  <!-- SlimScroll -->
  <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
  <!-- FastClick -->
  <script src='plugins/fastclick/fastclick.min.js'></script>
  <!-- AdminLTE App -->
  <script src="dist/js/app.min.js" type="text/javascript"></script>
  
  <!-- Flexmonster pivot lib -->
  <script src="flexmonster/flexmonster.js"></script>

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

    $(function() {
        $('div.alert').hide().fadeIn().delay(3000).fadeOut('slow');
    });
  </script>


  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>