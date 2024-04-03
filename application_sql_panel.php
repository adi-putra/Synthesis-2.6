<?php
include 'session.php';
?>
<!-- Cards -->
<!-- <div class="col-md-12 panel-scroller" style="max-height: 336px;overflow-y: scroll;"> -->
<div class="col-md-12 panel-scroller">

  <div class="row">
    <div class="col-lg-3 col-xs-6" id="current_responsetime">
      <script>
        loadCurrentResponseTime();
        var current_responsetime = setInterval(loadCurrentResponseTime, 1000);

        function loadCurrentResponseTime() {
          $("#current_responsetime").load("application/sql/cards/current_responsetime.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>
    <div class="col-lg-3 col-xs-6" id="current_failjob">
      <script>
        timefrom = '<?php echo $timefrom; ?>';
        timetill = '<?php echo $timetill ?>';
        loadCurrentFailJob();
        var current_failjob = setInterval(loadCurrentFailJob, 60000);

        function loadCurrentFailJob() {
          $("#current_failjob").load("application/sql/cards/current_failjob.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>
    <div class="col-lg-3 col-xs-6" id="current_backupstatus">

      <script>
        loadCurrentBackupStat();
        var current_backupstatus = setInterval(loadCurrentBackupStat, 60000);

        function loadCurrentBackupStat() {
          $("#current_backupstatus").load("application/sql/cards/current_backupstatus.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>

    </div>
    <div class="col-lg-3 col-xs-6" id="today_problems">
      <script>
        loadCurrentProblems();
        var today_problems = setInterval(loadCurrentProblems, 60000);

        function loadCurrentProblems() {
          $("#today_problems").load("application/sql/cards/today_problems.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>
  </div>
  <div class="row">

    <div class="col-lg-2 col-xs-6" id="current_processtime">
      <script>
        loadCurrentProcessTime();
        var current_processtime = setInterval(loadCurrentProcessTime, 60000);

        function loadCurrentProcessTime() {
          $("#current_processtime").load("application/sql/cards/current_processtime.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_usedmem">
      <script>
        loadCurrentUsedMem();
        var current_usedmem = setInterval(loadCurrentUsedMem, 60000);

        function loadCurrentUsedMem() {
          $("#current_usedmem").load("application/sql/cards/current_usedmem.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_deadlock">
      <script>
        loadCurrentDeadlock();
        var current_deadlock = setInterval(loadCurrentDeadlock, 60000);

        function loadCurrentDeadlock() {
          $("#current_deadlock").load("application/sql/cards/current_deadlock.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_userconn">
      <script>
        loadCurrentUserConn();
        var current_userconn = setInterval(loadCurrentUserConn, 60000);

        function loadCurrentUserConn() {
          $("#current_userconn").load("application/sql/cards/current_userconn.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_memclerk">
      <script>
        loadCurrentMemClerk();
        var current_memclerk = setInterval(loadCurrentMemClerk, 60000);

        function loadCurrentMemClerk() {
          $("#current_memclerk").load("application/sql/cards/current_memclerk.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_backupthrough">
      <script>
        loadCurrentBackupThrough();
        var current_backupthrough = setInterval(loadCurrentBackupThrough, 60000);

        function loadCurrentBackupThrough() {
          $("#current_backupthrough").load("application/sql/cards/current_backupthrough.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-2 col-xs-6" id="current_buffercache">
      <script>
        loadCurrentBufferCache();
        var current_buffercache = setInterval(loadCurrentBufferCache, 60000);

        function loadCurrentBufferCache() {
          $("#current_buffercache").load("application/sql/cards/current_buffercache.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_checkpoint">
      <script>
        loadCurrentCheckpoint();
        var current_checkpoint = setInterval(loadCurrentCheckpoint, 60000);

        function loadCurrentCheckpoint() {
          $("#current_checkpoint").load("application/sql/cards/current_checkpoint.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_pagelookup">
      <script>
        loadCurrentPageLookup();
        var current_pagelookup = setInterval(loadCurrentPageLookup, 60000);

        function loadCurrentPageLookup() {
          $("#current_pagelookup").load("application/sql/cards/current_pagelookup.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_freelist">
      <script>
        loadCurrentFreeList();
        var current_freelist = setInterval(loadCurrentFreeList, 60000);

        function loadCurrentFreeList() {
          $("#current_freelist").load("application/sql/cards/current_freelist.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_memgrant">
      <script>
        loadCurrentMemGrant();
        var current_memgrant = setInterval(loadCurrentMemGrant, 60000);

        function loadCurrentMemGrant() {
          $("#current_memgrant").load("application/sql/cards/current_memgrant.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_freespacetempdb">
      <script>
        loadCurrentFreeSpaceDb();
        var current_freespacetempdb = setInterval(loadCurrentFreeSpaceDb, 60000);

        function loadCurrentFreeSpaceDb() {
          $("#current_freespacetempdb").load("application/sql/cards/current_freespacetempdb.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-2 col-xs-6" id="current_acttemptbl">
      <script>
        loadCurrentActTempTbl();
        var current_acttemptbl = setInterval(loadCurrentActTempTbl, 60000);

        function loadCurrentActTempTbl() {
          $("#current_acttemptbl").load("application/sql/cards/current_acttemptbl.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_verstore">
      <script>
        loadCurrentVerStore();
        var current_verstore = setInterval(loadCurrentVerStore, 60000);

        function loadCurrentVerStore() {
          $("#current_verstore").load("application/sql/cards/current_verstore.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_ple">
      <script>
        loadCurrentPLE();
        var current_ple = setInterval(loadCurrentPLE, 60000);

        function loadCurrentPLE() {
          $("#current_ple").load("application/sql/cards/current_ple.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_totallog">
      <script>
        loadCurrentTotalLog();
        var current_totallog = setInterval(loadCurrentTotalLog, 60000);

        function loadCurrentTotalLog() {
          $("#current_totallog").load("application/sql/cards/current_totallog.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_logflush">
      <script>
        loadCurrentLogFlush();
        var current_logflush = setInterval(loadCurrentLogFlush, 60000);

        function loadCurrentLogFlush() {
          $("#current_logflush").load("application/sql/cards/current_logflush.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>

    <div class="col-lg-2 col-xs-6" id="current_transdelay">
      <script>
        loadCurrentTransDelay();
        var current_transdelay = setInterval(loadCurrentTransDelay, 60000);

        function loadCurrentTransDelay() {
          $("#current_transdelay").load("application/sql/cards/current_transdelay.php?hostid=" + hostid + "&timefrom=" + timefrom + "&timetill=" + timetill);
        }
      </script>
    </div>
  </div>


</div>

<script>
  function openTab2() {
    $('#tab_1').removeClass("active");
    $('#tab_2').addClass("active");

    $('#tab_1li').removeClass("active");
    $('#tab_2li').addClass("active");
  }

  $thisurl = window.location.pathname.split("/").pop();

  function openIssueTab() {
    if ($thisurl == "application_sql_issues.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_issues.php";
    }
  }

  function openGeneralTab() {
    if ($thisurl == "application_sql_general.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_general.php";
    }
  }

  function openStatTab() {
    if ($thisurl == "application_sql_stat.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_stat.php";
    }
  }

  function openDbSizeTab() {
    if ($thisurl == "application_sql_dbsize.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_dbsize.php";
    }
  }

  function openPerformanceTab() {
    if ($thisurl == "application_sql_performance.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_performance.php";
    }
  }

  function openDbLatencyTab() {
    if ($thisurl == "application_sql_dblatency.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_dblatency.php";
    }
  }

  function openDbIOTab() {
    if ($thisurl == "application_sql_dbio.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_dbio.php";
    }
  }

  function openAvailRepTab() {
    if ($thisurl == "application_sql_availrep.php") {
      openTab2();
    } else {
      window.location.href = "application_sql_availrep.php";
    }
  }
</script>