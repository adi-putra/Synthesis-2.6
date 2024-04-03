<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Synthesis</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />

    <!-- FontAwesome -->
    <link rel="stylesheet" href="bootstrap/fontawesome-free-6.1.1-web/css/all.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="login-page">
    <div id="login_failed" style="float: right; width: 30%; display: none;">
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" onclick="hide_loginfailed();">&times;</button>
        <h4><i class="icon fa fa-ban"></i> Failed!</h4>
        <p id="p_loginfailed">Incorrect username or password.</p>
      </div>             
    </div>
    <div id="login_success" style="float: right; width: 30%; display: none;">
      <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" onclick="hide_loginsuccess();">&times;</button>
        <h4><i class="icon fa fa-check"></i> Success!</h4>
        <p>Succesfully login.</p>
      </div>             
    </div>
    <div id="login_nouserpass" style="float: right; width: 30%; display: none;">
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" onclick="hide_loginnouserpass();">&times;</button>
        <h4><i class="icon fa fa-ban"></i> Failed!</h4>
        <p id="p_loginnouserpass">Please enter username/password.</p>
      </div>             
    </div>
    <div class="login-box">
      <div class="login-logo">
        <a href="#"></a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <div class="login-logo"><img src="dist/img/Synthesis_Logo_White.png" alt="Synthesis Logo" width="auto" height="auto"></div>
        <form action="login_exec.php" method="get">
          <div class="form-group">
            <div class="input-group">
              <input class="form-control" type="text" name="user_name" id="user_name" placeholder="Username" onkeydown="handleEnter(event)">
              <div class="input-group-addon">
                <i class="fa fa-user"></i>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="input-group" id="show_hide_password">
              <input class="form-control" type="password" name="user_password" id="user_password" placeholder="Password" onkeydown="handleEnter(event)">
              <div class="input-group-addon">
                <i class="fa fa-eye-slash" style="cursor: pointer;"></i>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-12">
              <button type="button" onclick="login();" class="btn btn-primary btn-block btn-flat">Sign In</button>
            </div><!-- /.col -->
          </div>
        </form>

        <div class="social-auth-links text-center" style="display: none;">
          <p>- OR -</p>
          <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in using Facebook</a>
          <a href="#" class="btn btn-block btn-social btn-google-plus btn-flat"><i class="fa fa-google-plus"></i> Sign in using Google+</a>
        </div><!-- /.social-auth-links -->

        <a href="#" style="display: none;">I forgot my password</a><br>
        <a href="register.html" class="text-center" style="display: none;">Register a new membership</a>

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.3 -->
    <script src="plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
    </script>
  </body>
</html>

<script>
  //close login success modal
  function hide_loginsuccess() {
    $("#login_success").hide();
  }

  //close login failed modal
  function hide_loginfailed() {
    $("#login_failed").hide();
  }

  //close login no user pass modal
  function hide_loginnouserpass() {
    $("#login_nouserpass").hide();
  }

  //login function
  function login() {

    //hide previous shown login status
    hide_loginsuccess()
    hide_loginfailed()
    hide_loginnouserpass();

    //get username and password
    var getusername = $("#user_name").val();
    var getpassword = $("#user_password").val();

    //if no username or password entered
    if (getusername == "" || getpassword == "") {
      $("#login_nouserpass").toggle();
      return;
    }

    //post to login check 
    $.post("login_check.php",
    {
      username: getusername,
      password: getpassword
    },

    //function check if data return "incorrect user name or password"
    function(data, status){
      //login fail
      if (data == "Successfully login") {
        $("#login_success").toggle();
        //alert("Data: " + data + "\nStatus: " + status);
        location.assign("login_exec.php?username=" + getusername + "&password=" + getpassword);
      } 
      //login success
      else {
        $("#p_loginfailed").html(data);
        $("#login_failed").toggle();
        //alert("Data: " + data + "\nStatus: " + status);
      }
    });
  }

  function handleEnter(e) {
    if(e.keyCode === 13){
        login();
    }
  }

  $("#show_hide_password i").on('click', function(event) {
      event.preventDefault();
      if($('#show_hide_password input').attr("type") == "text"){
          $('#show_hide_password input').attr('type', 'password');
          $('#show_hide_password i').addClass( "fa-eye-slash" );
          $('#show_hide_password i').removeClass( "fa-eye" );
      }else if($('#show_hide_password input').attr("type") == "password"){
          $('#show_hide_password input').attr('type', 'text');
          $('#show_hide_password i').removeClass( "fa-eye-slash" );
          $('#show_hide_password i').addClass( "fa-eye" );
      }
  });
</script>
