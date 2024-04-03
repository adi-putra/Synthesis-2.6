<?php
ob_start();

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
      if (isset($_GET['groupid'])) {

        $groupid = $_GET['groupid'];

        //edit
        if (isset($_POST['editgroup'])) {

          if (isset($_POST['groupname'])) {
            $groupname = $_POST['groupname'];
            $params = array(
              "groupid" => $groupid,
              "name" => $groupname,
            );
            // call api
            $result = $zbx->call('hostgroup.update', $params);

            if ($result) {

              // echo '<div class="alert" style="padding: 20px;background-color: green ;color: white;">Host group has been updated!</div>';
              //header("Refresh: 3;");
              //display message box Record Been Added
              print '<script>alert("Successfully update host group.");</script>';

              //go to hostgroup.php page
              print '<script>window.location.assign("hostgroup_list.php");</script>';

            }
          }
        }

        //delete
        if (isset($_POST['deletegroup'])) {

          $params = array(
            $groupid
          );
          // call api
          $result = $zbx->call('hostgroup.delete', $params);
          if ($result) {

            // echo '<div class="alert" style="padding: 20px;background-color: green ;color: white;">Host group has been deleted!</div>';
            // header("Location: http://192.168.1.118/synthesis/hostgroup_list.php");
            // header('Refresh: 3; URL=hostgroup_list.php');
            print '<script>alert("Successfully delete host group.");</script>';

            //go to hostgroup.php page
            print '<script>window.location.assign("hostgroup_list.php");</script>';
            
            // exit();
          }
        }
      }

      //get current group name
      $params = array(
        "output" => array("groupid", "name"),
        "filter" => ["groupid" => [$groupid]]
      );
      $res = $zbx->call('hostgroup.get', $params);
      foreach ($res as $group) {
        $groupname = $group['name'];

        // print '<input  name="groupname" type="text" value="' . $groupname . '" class="form-control" placeholder="Enter group name" required/>';
      }
      ?>
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Hosts Group
          <small> Edit</small>
        </h1>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="hostgroup_list.php">Host Group</li></a>
          <li class="active">Host Group Edit</li>
        </ol>
        <!-- Select Host Dropdown -->
        <br>
        <div class="row">
          <div class="col-md-12">
            <!-- general form elements disabled -->
            <div class="box box-primary">
              <div class="box-body">
                <form action="hostgroup_edit.php?groupid=<?= $groupid ?>" method="post" enctype="multipart/form-data">
                  <table class="table table-bordered table-striped">
                    <tr>
                      <th>Group Name</th>
                      <td>
                        <input  name="groupname" type="text" value="<?php echo $groupname; ?>" class="form-control" placeholder="Enter group name" required/>
                      </td>
                    </tr>
                  </table>
                  <button type="submit" name="editgroup" class="btn btn-success margin" value="editgroup">Update</button>
                  <button type="reset" class="btn btn-default margin" value="editgroup">Reset</button>
                  <a href="hostgroup_edit.php?groupid=<?php echo $groupid; ?>" onclick="return confirm('Confirm delete this host group?')">
                    <button type="submit" name="deletegroup" class='btn btn-danger margin'>Delete</button>
                  </a>
                </form>
              </div><!-- /.box-body -->
            </div><!-- /.box -->
          </div><!--/.col (right) -->
        </div>
      </section>

    </div><!-- /.content-wrapper -->

    <?php include("footer.php"); ?>

  </div><!-- ./wrapper -->

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