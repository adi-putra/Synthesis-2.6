<?php

include 'session.php';


$params = array(
  "output" => array("groupid", "name"),
  "search" => array("name" => "sql")
);
$result = $zbx->call('hostgroup.get', $params);
foreach ($result as $row) {
  $getgroupid = $row["groupid"];
}

$groupid = $_GET["groupid"] ?? $getgroupid;

$hostids = array();
$params = array(
  "output" => array("hostid", "host", "name"),
  "groupids" => $groupid
);
//call api
$result = $zbx->call('host.get', $params);
foreach ($result as $row) {
  $gethostid = $row["hostid"];
  $hostids = $gethostid;
}

$hostid = $_GET["hostid"] ?? $hostids;


if (isset($_POST['submit'])) {
  // Execute this code if the submit button is pressed.
  $timefrom1 = $_POST['timefrom'];
  $timefrom = strtotime($timefrom1);
  $timetill1 = $_POST['timetill'];
  $timetill = strtotime($timetill1);
} else {
  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();
}

//display time format
$diff = $timetill - $timefrom;
if ($diff == 3600) {
  $status = "Last 1 hour";
} else if ($diff < 86400) {
  $status = "Today";
} else if ($diff == 86400) {
  $status = "Last 1 day";
} else if ($diff == 172800) {
  $status = "Last 2 days";
} else if ($diff == 604800) {
  $status = "Last 7 days";
} else if ($diff >= 2678400 and $diff < 7948800) {
  $status = "Last 1 month";
}

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

function formatBytes($bytes, $precision = 2)
{
  $units = array('B', 'KB', 'MB', 'GB', 'TB');

  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);

  // Uncomment one of the following alternatives
  $bytes /= pow(1024, $pow);
  // $bytes /= (1 << (10 * $pow)); 

  return round($bytes, $precision) . ' ' . $units[$pow];
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
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          <?php
          $params = array(
            "output" => array("name"),
            "hostids" => $hostid,
            "selectGroups" => array("name"),
            "selectInterfaces" => array("ip")
          );

          $result = $zbx->call('host.get', $params);
          foreach ($result as $row) {
            $hostname = $row["name"];

            foreach ($row["groups"] as $group) {
              //if group is in windows, print windows logo with it...also for other brand
              if (stripos($group["name"], 'sql')) {
                $hostgroup = $group['name'] . " <i class='fa fa-cube'></i>";
              } else {
                $hostgroup = $group['name'];
              }
            }
            foreach ($row["interfaces"] as $interface) {
              $hostip = $interface['ip'];
              break;
            }
            print "$hostname";
          }
          ?>
          <small>Latest Data</small>
        </h1>
        <?php
        print $hostip . ', ' . $hostgroup;
        ?>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
        <br><br>
        <!-- Select Host Dropdown -->
        <div class="form-group">
          <select class="form-control" name="selecthost" onchange="location = this.value;" style="display: inline-block; width: auto;">
            <option><?php echo $hostname ?></option>
            <?php
            $params = array(
              "output" => array("hostid", "name"),
              "selectGroups" => array("name")
            );
            $result = $zbx->call('host.get', $params);
            foreach ($result as $row) {
              $gethostid = $row["hostid"];
              //if hostname is not same as current hostname, get hostname
              if ($row["name"] != $hostname) {
                $gethostname = $row["name"];

                //get group name
                foreach ($row["groups"] as $group) {
                  $getgroupname = $group["name"];
                  if (stripos($getgroupname, "sql")) {
                    print "<option value='application_sql_availrep.php?hostid=" . $gethostid . "'>$gethostname</option>";
                  }
                }
              } else {
                continue;
              }
            }
            ?>
          </select>
        </div>
      </section>

      <script>
        // Graph Button
        function graphBtn() {
          var timefrom = document.getElementsByName("timefrom")[0].value;
          var timetill = document.getElementsByName("timetill")[0].value;
          //if value is blank
          if (timefrom === "" || timetill === "") {
            alert("Zero/Wrong inputs detected!");
            return false;
          } else {
            timefrom = moment(timefrom, "D-M-Y hh:mm:ss A").unix();
            timetill = moment(timetill, "D-M-Y hh:mm:ss A").unix();

            fromTime = displayTime(timefrom);
            tillTime = displayTime(timetill);

            var timefrom = document.getElementsByName("timefrom")[0].value;
            timefrom = moment(timefrom, "D-M-Y hh:mm:ss A").unix();
            var timetill = document.getElementsByName("timetill")[0].value;
            timetill = moment(timetill, "D-M-Y hh:mm:ss A").unix();
            loadAllGraph(timefrom, timetill);
          }
        }

        function graphBtnSide() {
          graphBtn();
        }
      </script>

      <script>
        // Get Time Status, HostID, Time From and Till 
        getTimeStat = '<?php echo $status; ?>';
        if (getTimeStat == "Today" || getTimeStat == "Last 1 hour") {
          var intervalOn = true;
        }
        //take time value from server
        var hostid = '<?php echo $hostid; ?>';
        var timefrom = '<?php echo $timefrom ?>';
        var timetill = '<?php echo $timetill ?>';

        //take current time value
        var currTime = '<?php echo time(); ?>';
        var timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;

        // Load All Graph Function
        function loadAllGraph(timefrom, timetill) {
          intervalOn = false; //if this function starts, stop interval
          timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;

          return true;
        }


        // Quick Stats Graphs
        function loadProcessorTime(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#processor_time").load("application/sql/stats/processor_time.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#processor_time").load("application/sql/stats/processor_time.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadUsedMemGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#used_memory").load("application/sql/stats/used_memory.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#used_memory").load("application/sql/stats/used_memory.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadDeadlockGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#deadlock").load("application/sql/stats/deadlock.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#deadlock").load("application/sql/stats/deadlock.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadUserConnGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#user_connection").load("application/sql/stats/user_connection.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#user_connection").load("application/sql/stats/user_connection.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadMemClerkGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#memory_clerk").load("application/sql/stats/memory_clerk.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#memory_clerk").load("application/sql/stats/memory_clerk.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadBackupGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#backup_throughput").load("application/sql/stats/backup_throughput.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#backup_throughput").load("application/sql/stats/backup_throughput.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadBufferCacheGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#buffer_cache").load("application/sql/stats/buffer_cache.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#buffer_cache").load("application/sql/stats/buffer_cache.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadCheckpointGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#checkpoint").load("application/sql/stats/checkpoint.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#checkpoint").load("application/sql/stats/checkpoint.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadPageLookupGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#page_lookup").load("application/sql/stats/page_lookup.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#page_lookup").load("application/sql/stats/page_lookup.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadFreeListStallGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#free_liststall").load("application/sql/stats/free_liststall.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#free_liststall").load("application/sql/stats/free_liststall.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadMemoryGrantGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#memory_grant").load("application/sql/stats/memory_grant.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#memory_grant").load("application/sql/stats/memory_grant.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadFreeSpaceTDBGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#freespace_tdb").load("application/sql/stats/freespace_tdb.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#freespace_tdb").load("application/sql/stats/freespace_tdb.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadActiveTempTblGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#active_temptbl").load("application/sql/stats/active_temptbl.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#active_temptbl").load("application/sql/stats/active_temptbl.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadVersionStoreGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#version_store").load("application/sql/stats/version_store.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#version_store").load("application/sql/stats/version_store.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadPLEGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#ple").load("application/sql/stats/ple.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#ple").load("application/sql/stats/ple.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadTotalLogUsedGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#totallog_used").load("application/sql/stats/totallog_used.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#totallog_used").load("application/sql/stats/totallog_used.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        // Performance Graphs
        function loadSQLStatGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#sql_stat").load("application/sql/performance/sql_stat.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#sql_stat").load("application/sql/performance/sql_stat.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadUserConn1Graph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#user_connection1").load("application/sql/performance/user_connection.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#user_connection1").load("application/sql/performance/user_connection.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadAccMethodGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#access_method").load("application/sql/performance/access_method.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#access_method").load("application/sql/performance/access_method.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadLogFlushGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#log_flushes").load("application/sql/performance/log_flushes.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#log_flushes").load("application/sql/performance/log_flushes.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadReplicaMirrorGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#replica_mirror").load("application/sql/performance/replica_mirror.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#replica_mirror").load("application/sql/performance/replica_mirror.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadTransDelayGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#trans_delay").load("application/sql/performance/trans_delay.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#trans_delay").load("application/sql/performance/trans_delay.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadDBActivityGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#db_activity").load("application/sql/performance/db_activity.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#db_activity").load("application/sql/performance/db_activity.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadWaitStatGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#wait_stat").load("application/sql/performance/wait_stat.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#wait_stat").load("application/sql/performance/wait_stat.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadErrorsGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#errors").load("application/sql/performance/errors.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#errors").load("application/sql/performance/errors.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        // Database I/O Graphs
        function loadDataReadGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#data_read").load("application/sql/database_io/data_read.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#data_read").load("application/sql/database_io/data_read.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadDataWriteGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#data_write").load("application/sql/database_io/data_write.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#data_write").load("application/sql/database_io/data_write.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadLogReadGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#log_read").load("application/sql/database_io/log_read.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#log_read").load("application/sql/database_io/log_read.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadLogWriteGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#log_write").load("application/sql/database_io/log_write.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#log_write").load("application/sql/database_io/log_write.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        // Database Latency Graphs
        function loadDataReadLatencyGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#data_readlatency").load("application/sql/database_latency/data_readlatency.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#data_readlatency").load("application/sql/database_latency/data_readlatency.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadDataWriteLatencyGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#data_writelatency").load("application/sql/database_latency/data_writelatency.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#data_writelatency").load("application/sql/database_latency/data_writelatency.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadLogReadLatencyGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#log_readlatency").load("application/sql/database_latency/log_readlatency.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#log_readlatency").load("application/sql/database_latency/log_readlatency.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadLogWriteLatencyGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#log_writelatency").load("application/sql/database_latency/log_writelatency.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#log_writelatency").load("application/sql/database_latency/log_writelatency.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        // Availability Replica Graphs
        function loadInByteGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#in_byte").load("application/sql/avail_replica/in_byte.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#in_byte").load("application/sql/avail_replica/in_byte.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadOutByteGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#out_byte").load("application/sql/avail_replica/out_byte.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#out_byte").load("application/sql/avail_replica/out_byte.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadReceiveRepGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#receive_replica").load("application/sql/avail_replica/receive_replica.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#receive_replica").load("application/sql/avail_replica/receive_replica.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        function loadSendRepGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            //if timetill is same as current time, get new timetill every graph reload
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#send_replica").load("application/sql/avail_replica/send_replica.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#send_replica").load("application/sql/avail_replica/send_replica.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }

        // Load Disk Space Graph
        function loadDiskSpaceGraph(hostid, timerange) {
          var interval = setInterval(function() {
            timefrom = '<?php echo $timefrom; ?>';
            if (timetill == currTime) {
              timetill = Math.floor(Date.now() / 1000);
            } else {
              timetill = '<?php echo $timetill ?>';
            }
            timerange = "&timefrom=" + timefrom + "&timetill=" + timetill;
            $("#diskspace").load("application/sql/capacity/diskspace.php?hostid=" + hostid + timerange);
          }, 60000);

          //load chart 
          $("#diskspace").load("application/sql/capacity/diskspace.php?hostid=" + hostid + timerange);

          //if interval is set true, start interval
          if (intervalOn == true) {
            $('#submitDate').click(function() {
              clearInterval(interval);
            });
            interval;
          }
        }
      </script>

      <div class="content row">
        <!-- Custom Tabs -->
        <div class="nav-tabs-custom mytabs">
          <ul class="nav nav-tabs">
            <li id="tab_1li"><a data-target="#tab_1" data-toggle="tab">Latest</a></li>
            <li id="tab_2li" class="active"><a data-target="#tab_2" data-toggle="tab">History</a></li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane " id="tab_1">
              <!-- Cards -->
              <div class="row">
                <?php include("application_sql_panel.php"); ?>
              </div>

            </div>

            <div class="tab-pane active" id="tab_2">

              <!-- Main content -->
              <section class="content applicationsql">

                <!-- Cards -->

                <div class="row">
                  <?php include("application_sql_panel.php"); ?>
                </div>

                <div class="row">
                  <div class="col-xs-12">
                    <div class="box" style="border:none;">
                      <div class="box-header">
                        <h3 class="box-title"><?php echo $hostname; ?></h3>
                      </div>
                      <div class="box-body">
                        <div class="col-sm-12">
                          <div class="box" style="background-color: #3c8dbc;">
                            <div class="box-header">
                              <h3 class="box-title" style="color: white;"><?php if (isset($_POST['submit'])) {
                                                                            echo $daterange = $timefrom1 . ' - ' . $timetill1;
                                                                          } else {
                                                                            echo $status;
                                                                          } ?></h3>
                              <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                              </div>
                            </div>
                            <div class="box-body">
                              <form action="application_sql_availrep.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" method="post">
                                <div class="row">
                                  <div class="col-lg-4 col-md-4 col-sm-12 text-center" style="padding-bottom: 15px;">
                                    <div class="input-group" style="padding-right: 15px;padding-left: 15px;">
                                      <div class="input-group-addon" style="background:none;border:none;padding:0;">
                                        <button type="button" class="btn btn-success btn-flat dropdown-toggle" data-toggle="dropdown">Range
                                          <span class="caret"></span>
                                          <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                          <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("today"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Today</a></li>
                                          <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 hour"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 1 hour</a></li>
                                          <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 day"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 1 day</a></li>
                                          <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-2 days"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 2 days</a></li>
                                          <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 week"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 7 days</a></li>
                                          <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 month"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 1 month</a></li>
                                        </ul>
                                      </div>
                                      <input type="text" class="form-control" value="<?php if (isset($_POST['submit'])) {
                                                                                        echo "";
                                                                                      } else {
                                                                                        echo $status;
                                                                                      } ?>" disabled>
                                    </div>
                                  </div>
                                  <div class="col-lg-8 col-md-8 col-sm-12 text-center">
                                    <div class="col-lg-5 col-md-5 col-sm-12">
                                      <div class="form-group">
                                        <div class='input-group date' id='datetimepicker6'>
                                          <input type='text' class="form-control" name="timefrom" value="<?php if (isset($_POST['timefrom'])) {
                                                                                                            echo $_POST['timefrom'];
                                                                                                          } else {
                                                                                                            echo date('d-m-Y h:i A', $timefrom);
                                                                                                          } ?>" placeholder="From:" />
                                          <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                          </span>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12">
                                      <div class="form-group">
                                        <div class='input-group date' id='datetimepicker7'>
                                          <input type='text' class="form-control" name="timetill" value="<?php if (isset($_POST['timetill'])) {
                                                                                                            echo $_POST['timetill'];
                                                                                                          } else {
                                                                                                            echo date('d-m-Y h:i A', $timetill);
                                                                                                          } ?>" placeholder="To:" />
                                          <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                          </span>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2 col-sm-12">
                                      <input type="submit" name="submit" id="submitDate" onclick="graphBtn();" value="Apply" class="btn btn-block"></input>
                                    </div>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>



                        <div class="row" id="mycontent-host">
                          <div class="col-xs-12">
                            <aside class="col-lg-2 col-md-3 col-sm-12">
                              <div class="collapsed-box affix-top" id="mysidebar-host" style="z-index: 1;">
                                <div class="box-header with-border">

                                </div>
                                <div class="box-body show">
                                  <ul class="nav nav-pills nav-stacked application-nav">
                                    <form action="application_sql_availrep.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $timefrom; ?>&timetill=<?php echo $timetill; ?>" method="post">
                                      <h3 class="side-box-title">Date</h3>
                                      <li>
                                        <div class="dropdown" style="margin-bottom: 10px;">
                                          <button type="button" class="btn btn-block btn-default btn-flat dropdown-toggle side-range-btn" data-toggle="dropdown">Range: <?php if (isset($_POST['submit'])) {
                                                                                                                                                                          echo "";
                                                                                                                                                                        } else {
                                                                                                                                                                          echo $status;
                                                                                                                                                                        } ?>
                                            <span class="caret" style="margin-top: 7px;"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                          </button>
                                          <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1" id="range-dropdown">
                                            <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("today"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Today</a></li>
                                            <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 hour"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 1 hour</a></li>
                                            <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 day"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 1 day</a></li>
                                            <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-2 days"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 2 days</a></li>
                                            <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 week"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 7 days</a></li>
                                            <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo strtotime("-1 month"); ?>&timetill=<?php echo strtotime("+2 minutes"); ?>">Last 1 month</a></li>

                                          </ul>
                                        </div>
                                      </li>
                                      <li>
                                        <div class='input-group date' id='datetimepicker8' style="margin-bottom: 10px;">
                                          <input type='text' class="form-control" name="timefrom" value="<?php if (isset($_POST['timefrom'])) {
                                                                                                            echo $_POST['timefrom'];
                                                                                                          } else {
                                                                                                            echo date('d-m-Y h:i A', $timefrom);
                                                                                                          } ?>" placeholder="From:" />
                                          <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                          </span>
                                        </div>
                                      </li>
                                      <li>
                                        <div class='input-group date' id='datetimepicker9' style="margin-bottom: 10px;">
                                          <input type='text' class="form-control" name="timetill" value="<?php if (isset($_POST['timetill'])) {
                                                                                                            echo $_POST['timetill'];
                                                                                                          } else {
                                                                                                            echo date('d-m-Y h:i A', $timetill);
                                                                                                          } ?>" placeholder="To:" />
                                          <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                          </span>
                                        </div>
                                        <input type="submit" name="submit" onclick="graphBtnSide();" value="Apply" class="btn btn-block btn-success" style="margin-bottom: 10px;"></input>
                                      </li>
                                    </form>
                                    <h3 class="side-box-title">Filters</h3>
                                    <!-- <div class="sub-group" > -->
                                    <ul class="nav nav-tabs nav-stacked mynav1">
                                      <li><a href="application_sql_issues.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Issues</a></li>
                                      <!-- <li class="active"><input type="checkbox" class="sub-box" value="issues" checked><a href="#issues">Problems</a></li>
                              <li><input type="checkbox" class="sub-box" value="winsyslog" checked><a href="#winsyslog">System Log</a></li>
                              <li><input type="checkbox" class="sub-box" value="winapplog" checked><a href="#winapplog">Application Log</a></li>
                              <li><input type="checkbox" class="sub-box" value="jobfailed" checked><a href="#jobfailed">Job Failed</a></li>
                              <li><input type="checkbox" class="sub-box" value="backupstatus" checked><a href="#backupstatus">SQL Backup Failed</a></li> -->
                                      <li><a href="application_sql_general.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">General</a></li>
                                      <li><a href="application_sql_stat.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Quick Stats</a></li>
                                      <li><a href="application_sql_dbsize.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Database Size</a></li>
                                      <li><a href="application_sql_performance.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Performance</a></li>
                                      <li><a href="application_sql_dbio.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Database I/O</a></li>
                                      <li><a href="application_sql_dblatency.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Database Latency</a></li>
                                      <li class="active"><a href="application_sql_availrep.php?hostid=<?php echo $hostid; ?>&timefrom=<?php echo $_GET["timefrom"] ?? strtotime("today"); ?>&timetill=<?php echo $timetill; ?>">Availability Replica</a></li>
                                    </ul>
                                    <!-- </div> -->
                                  </ul>
                                </div>
                              </div>
                            </aside>


                            <div class="col-lg-10 col-md-9 col-sm-12" id="content-wrapper-host">

                              <!-- Availability Replica -->

                              <h3>Availability Replica</h3>

                              <div class="box box-solid box-default">
                                <div class="box-header">
                                  <h3 class="box-title">IN | Bytes Received from Replica/sec Graph</h3>
                                  <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                  </div>
                                </div>
                                <div class='box-body'>
                                  <div id="in_byte"></div>
                                  <script>
                                    loadInByteGraph(hostid, timerange);
                                  </script>
                                </div>
                              </div>

                              <div class="box box-solid box-default">
                                <div class="box-header">
                                  <h3 class="box-title">OUT | Bytes Sent to Replica/sec Graph</h3>
                                  <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                  </div>
                                </div>
                                <div class='box-body'>
                                  <div id="out_byte"></div>
                                  <script>
                                    loadOutByteGraph(hostid, timerange);
                                  </script>
                                </div>
                              </div>


                              <div class="box box-solid box-default">
                                <div class="box-header">
                                  <h3 class="box-title">Receives from Replica/sec Graph</h3>
                                  <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                  </div>
                                </div>
                                <div class='box-body'>
                                  <div id="receive_replica"></div>
                                  <script>
                                    loadReceiveRepGraph(hostid, timerange);
                                  </script>
                                </div>
                              </div>

                              <div class="box box-solid box-default">
                                <div class="box-header">
                                  <h3 class="box-title">Sends to Replica/sec Graph</h3>
                                  <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                                  </div>
                                </div>
                                <div class='box-body'>
                                  <div id="send_replica"></div>
                                  <script>
                                    loadSendRepGraph(hostid, timerange);
                                  </script>
                                </div>
                              </div>


                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

            </div>
          </div>
        </div>
      </div>







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

      // // When Upper checkbox change
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
  </script>




  <?php
  $zbx->logout(); //logout from zabbix API
  ?>

</body>

</html>