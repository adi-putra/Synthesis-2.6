<head>
    <meta charset="UTF-8">
    <?php $activePage = basename($_SERVER['PHP_SELF'], ".php");
    switch ($activePage) {
        case "dashboard":
            echo "<title>Dashboard: Synthesis</title>";
        case "problems":
            echo "<title>Unresolved Problems</title>";
            break;
        case "history":
            echo "<title>Resolved Problems (System)</title>";
            break;
        case "closedprob":
            echo "<title>Closed Problems (User)</title>";
            break;
        case "application_sql_availrep":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_dbio":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_dblatency":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_dbsize":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_general":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_panel":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_performance":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_stat":
            echo "<title>SQL Application</title>";
            break;
        case "application_sql_overview":
            echo "<title>SQL Overview</title>";
            break;
        case "application_sql_issues":
            echo "<title>SQL Application</title>";
            break;
        case "reporting_application_sql":
            echo "<title>SQL Reporting</title>";
            break;
        case "overview_windows1":
            echo "<title>Windows Overview</title>";
            break;
        case "overview_esx":
            echo "<title>ESX Overview</title>";
            break;
        case "overview_linux":
            echo "<title>Linux Overview</title>";
            break;
        case "overview_firewall":
            echo "<title>Firewall Overview</title>";
            break;
        case "overview_ups":
            echo "<title>UPS Overview</title>";
            break;
        case "overview_switches":
            echo "<title>Switches Overview</title>";
            break;
        case "hostdetails_windows":
            echo "<title>Windows Host Details</title>";
            break;
        case "hostdetails_linux":
            echo "<title>Linux Host Details</title>";
            break;
        case "hostdetails_esx":
            echo "<title>ESX Host Details</title>";
            break;
        case "hostdetails_ilo":
            echo "<title>ILO Host Details</title>";
            break;
        case "hostdetails_firewall":
            echo "<title>Firewall Host Details</title>";
            break;
        case "hostdetails_ups":
            echo "<title>UPS Host Details</title>";
            break;
        case "hostdetails_switches":
            echo "<title>Switch Host Details</title>";
            break;
        case "hostdetails_vm":
            echo "<title>VM Host Details</title>";
            break;
        case "application_nocvm":
            echo "<title>GSC VCenter</title>";
            break;
        case "application_vmware":
            echo "<title>Virtual Machines</title>";
            break;
        case "maintenance":
            echo "<title>Maintenance</title>";
            break;
        case "maintenance":
            echo "<title>Maintenance</title>";
            break;
        case "maintenance_edit":
            echo "<title>Maintenance Edit</title>";
            break;
        case "maintenance_create":
            echo "<title>Maintenance Create</title>";
            break;
        default:
            echo "<title>Synthesis</title>";
    }
    ?>
    <!--<meta http-equiv="refresh" content="60">-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 3.3.2 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- daterange picker -->
    <link href="plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- iCheck for checkboxes and radio inputs -->
    <link href="plugins/iCheck/all.css" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Color Picker -->
    <link href="plugins/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" />
    <!-- Bootstrap time Picker -->
    <link href="plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet" />
    <!-- Morris charts -->
    <link href="plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- Bootstrap time Picker -->
    <link href="plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet"/>
    
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />

    <!-- Playlist scripts -->
    <!-- <script src="pi/playlist.js"></script>
    <script>
    var play = '<?php echo $play; ?>' || '0';
    </script>
    <script src="pi/playlist_script.js"></script> -->

    <!-- Disable Keystrokes -->
    <script src="plugins/disable-keystrokes/disable_keys.js"></script>

    <!-- FontAwesome email netwitz adi -->
    <!--<script src="https://kit.fontawesome.com/4f726002a4.js"></script>-->
    <!--load all Font Awesome styles -->
    <link href="plugins/fontawesome_6.1.0/css/all.css" rel="stylesheet">
    <link href="plugins/fontawesome_6.1.0/css/brands.css" rel="stylesheet">
    <link href="plugins/fontawesome_6.1.0/css/solid.css" rel="stylesheet">

    <!-- jQuery 2.1.3 -->
    <script src="plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

    <!-- iCheck JS -->
    <script src="plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    
    <!-- DATA TABES SCRIPT -->
    <link rel="stylesheet" type="text/css" href="plugins/datatables/datatables.min.css"/>
    <script type="text/javascript" src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="plugins/datatables/dataTables.select.min.js"></script>
    <script type="text/javascript" src="plugins/datatables/r-2.2.1.datatables.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="plugins/pdfmake/pdfmake.min.js"></script>
    <script src="plugins/pdfmake/vfs_fonts.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <script src="plugins/datatables/plugins/file-size.js"></script>

    <!-- Resources AM4CORE -->
    <script src="plugins/amcharts/4/core.js"></script>
    <!-- <script src="https://cdn.amcharts.com/lib/4/core.js"></script> -->
    <script src="plugins/amcharts/4/charts.js"></script>
    <script src="plugins/amcharts/4/themes/material.js"></script>
    <script src="plugins/amcharts/4/plugins/timeline.js"></script>
    <script src="plugins/amcharts/4/plugins/bullets.js"></script>
    <script src="plugins/amcharts/4/themes/animated.js"></script>
    <script src="plugins/amcharts/4/themes/frozen.js"></script>

    <!-- InputMask -->
    <script src="plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
    <script src="plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
    <script src="plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>

    <!-- HTML2Canvas -->
    <script src="plugins/html2canvas/html2canvas.min.js" type="text/javascript"></script>
    
    <!-- SlimScroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>
    <!-- jQuery Knob -->
    <script src="plugins/knob/jquery.knob.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>

    <!-- Moment -->
    <script src="plugins/moment/moment.min.js"></script>

    <!-- Select2 -->
    <link href="plugins/select2/select2.min.css" rel="stylesheet" />
    <script src="plugins/select2/select2.min.js"></script>

    <!-- Bootstrap Datetimepicker -->
    <script src="plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css"/>

    <!-- bootstrap time picker -->
    <script src="plugins/timepicker/bootstrap-timepicker.min.js" type="text/javascript"></script>

    <!-- Flexmonster pivot lib -->
    <script src="plugins/flexmonster/flexmonster.js"></script>

    <!-- FabricJS -->
    <script src="plugins/fabric.js-2.6.0/dist/fabric.min.js"></script>

    <!-- AutosizeJS -->
    <script src="plugins/autosize/autosize.js"></script>
    
    <!-- Leaflet
    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <script src="leaflet/leaflet.js"></script> -->

    <!-- <script src="https://cdn.jsdelivr.net/momentjs/2.14.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"> -->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->


    <style>
        .noHover{
            pointer-events: none;
        }
        .example-modal .modal {
            position: relative;
            top: 250px;
            bottom: auto;
            right: auto;
            left: 150px;
            display: block;
            z-index: 1;
        }
        .example-modal .modal {
            background: transparent!important;
        }
        #mysidebar-host {
            float: left;
            z-index: 1;
        }

        .affix-top,
        .affix {
            position: static;
            top: 10px;
            width: 100%;
        }

        #mysidebar-host.affix {
            position: fixed;
            top: 10px;
            /* width: 50%; */
            width: 13vw;
        }


        #mysidebar-host.affix-bottom {
            position: absolute;
        }

        #mycontent-host h3.side-box-title {
            margin: 20px 0;
            color: #337ab7;
        }

        #mysidebar {
            float: left;
            position: fixed;
            z-index: 1;
        }

        #mycontent>#mysidebar {
            position: relative;
        }

        .sticky {
            position: fixed !important;
            top: 10px;
            z-index: 100;
        }

        .sticky+#mycontent {
            padding-top: -20px;
        }


        .nav-stacked input[type=checkbox] {
            margin-right: 10px;
        }

        .close-sidebar {
            animation: slide-out .2s forwards;
            -webkit-animation: slide-out .2s forwards;
            display: none;
        }

        .open-sidebar {
            animation: slide-in .2s forwards;
            -webkit-animation: slide-in .2s forwards;
            display: block;
        }

        .content-graph-wrapper {
            margin-left: 3% !important;
        }

        .filter {
            display: none;
        }


        /* Keyframe Slide */

        @keyframes slide-in {
            0% {
                -webkit-transform: translateX(-100%);
            }

            100% {
                -webkit-transform: translateX(0%);
            }
        }

        @keyframes slide-out {
            0% {
                transform: translateX(0%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Webkit Slide */

        @-webkit-keyframes slide-in {
            0% {
                -webkit-transform: translateX(-100%);
            }

            100% {
                -webkit-transform: translateX(0%);
            }
        }

        @-webkit-keyframes slide-out {
            0% {
                transform: translateX(0%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Reset AdminLTE design of Filters on 'a' tag */
        ul.overview-nav li.active a {
            background-color: transparent;
            border-left: none;
            width: 80%;
        }

        .nav-pills>li>a:hover,
        .nav>li>a:focus,
        .nav>li>a:hover {
            background-color: transparent;
        }


        /* Reassign custom design of Filters on 'li' tag */
        ul.overview-nav li:hover {
            background-color: #f4f4f4;
        }

        ul.overview-nav>li:first-child:hover,
        ul.overview-nav>li:first-child:active {
            background-color: transparent !important;
        }

        ul.overview-nav li.active {
            background-color: #f4f4f4;
            border-top: 0;
            border-left: 3px solid #3c8dbc;
            margin-left: -3px;
        }

        ul.overview-nav li a {
            padding: 10px;
            position: relative;
            display: inline-block;
            margin-left: -8px;
            color: #000;
            width: 80%;
        }

        ul.overview-nav li p {
            padding: 0px 10px;
            position: relative;
            display: inline-block;
            margin-left: -8px;
            font-weight: bold;
        }

        ul.overview-nav input[type=checkbox] {
            margin-left: 15px;
        }



        #sidebar-collapse {
            position: fixed;
            z-index: 9999;
        }

        #sidebar-collapse>div {
            float: left;
            position: absolute;
            padding: 0;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            z-index: 9999;
        }

        #sidebar-collapse>div>h3>a {
            padding: 23% 6%;
            letter-spacing: 3px;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            #mysidebar {
                position: relative !important;
                padding-right: 15px;
                margin-bottom: 20px;
            }

            .content-graph-wrapper {
                margin-left: 0% !important;
            }

            .affix-top,
            .affix,
            #mysidebar-host.affix,
            #mysidebar-host.affix-bottom {
                position: static !important;
                width: 100%;
            }

            #content-wrapper-host {
                float: none !important;
                background: transparent !important;
            }

        }

        #content-wrapper-host {
            float: right;
            z-index: 0;
            background: white;
        }



        .row-header {
            padding: 0 31px;
            display: flex;
        }

        .row-header h3 {
            display: inline;
        }

        .row-header>a,
        .row-header>button {
            margin-left: auto;
            width: auto;
        }

        ul#range-dropdown li a {
            margin-left: 0;
            color: #777;
            padding: 3px 20px;
            width: 100%;
        }

        .side-range-btn {
            text-align: left;
            display: flex;
            justify-content: space-between;
        }

        #mysidebar-host form li:hover {
            background-color: transparent;
        }

        #mysidebar-host.affix-top form {
            display: none;
        }

        #mysidebar-host.affix form {
            display: block;
        }

        .nav-tabs-custom.mytabs>.nav-tabs>li>a {
            font-size: 20px;
        }

        .nav-tabs-custom.mytabs>.nav-tabs>li.active>a {

            background: #3c8dbc;
            color: white;
        }

        .nav-tabs-custom.mytabs>.nav-tabs>li.active {
            border-top-color: transparent;
        }
    </style>

</head>