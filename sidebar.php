<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <li class="header">MAIN NAVIGATION</li>

      <li class="treeview">
        <a href="dashboard.php">
          <i class="fa fa-fw fa-home"></i> <span>Dashboard</span>
        </a>
      </li>

      <li class="treeview">
        <?php
        $prob_timerange = "&timefrom=" . strtotime("today") . "&timetill=" . strtotime("now");
        //link for problems page
        $problem_link = "problems.php?" . $prob_timerange;
        echo "<a href='problems.php?'>";
        ?>
        <i class="fa fa-exclamation-triangle"></i> <span>Problems</span>
        </a>
      </li>

      <li class="treeview">
        <a href='hostlist.php?'>
          <i class="fa fa-fw fa-desktop"></i> <span>Host</span>
        </a>
      </li>

      <li class="treeview">
        <a href='overview_list.php?'>
          <i class="fa fa-fw fa-sitemap"></i> <span>Overview</span>
        </a>
      </li>

      <li class="treeview">
        <a href="url_monitoring_host.php">
        <i class="fa fa-fw fa-link"></i> <span>URL</span>
        </a>
      </li>

      <li class="treeview">
        <a href="#">
          <i class="fa fa-file"></i> <span>Reporting</span> <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li><a href="reporting_list.php"><i class="fa fa-circle-o"></i> Reporting List</a></li>
          <li><a href="reporting_sched_list.php"><i class="fa fa-circle-o"></i> Schedule Report </a></li>
        </ul>
      </li>

      <li class="treeview">
        <a href="map.php">
          <i class="fa-solid fa-diagram-project"></i> <span> &nbsp;&nbsp;Map</span>
        </a>
      </li>

      <?php
      //permissions and rights
      if ($zabUtype !== "3") {
        $accessAdminPage = "display: none";
      } else {
        $accessAdminPage = "display: all";
      }
      ?>


      <li class="treeview" style="<?php echo $accessAdminPage; ?>">
        <a href="#">
          <i class="fa fa-user-gear"></i> <span>Administration </span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li>
            <a href="#"><i class="fa fa-circle-o"></i> Host <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
              <!-- <li><a href="#"><i class="fa fa-circle-o"></i> Hosts</a></li> -->
              <li><a href="hostgroup_list.php"><i class="fa fa-circle-o"></i> Host Groups</a></li>
            </ul>
          </li>
          <li>
            <a href="#"><i class="fa fa-circle-o"></i> User <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
              <li><a href="user.php"><i class="fa fa-circle-o"></i> User List</a></li>
              <li><a href="usergroup.php"><i class="fa fa-circle-o"></i> User Groups</a></li>
            </ul>
          </li>
          <li><a href="smtp_setup.php"><i class="fa fa-circle-o"></i> SMTP Setup</a></li>
          <li><a href="maintenance.php"><i class="fa fa-circle-o"></i> Maintenance</a></li>
        </ul>
      </li>

      <li class="treeview" style="<?php echo $access_license; ?>">
        <a href="license_check.php">
          <i class="fa-solid fa-lock"></i> <span> &nbsp;&nbsp;Check License</span>
        </a>
      </li>

      <li class="treeview">
        <a href="logout.php" onclick="if (!confirm('Are you sure you want to log out?')) { return false }">
          <i class="fa fa-fw fa-power-off"></i> <span>Logout</span>
        </a>
      </li>


  </section>
  <!-- /.sidebar -->
</aside>