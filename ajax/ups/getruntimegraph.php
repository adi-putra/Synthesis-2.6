<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];

function secondsToTime($seconds) {
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%H: %I: %S');
}

// Get hour
function secondsToHour($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	$hourcount = $dtF->diff($dtT)->format('%h');
	return $hourcount;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Windows Overview</title>
</head>

<body>
  <div class="row">
    <div class="col-md-12">
      <!-- CPU Util table -->
      <table id="runtimetable" class="table table-bordered table-striped">
        <caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
        <thead>
          <tr>
            <th>Host Name</th>
            <th>Runtime</th>
            <th>Hh:mm:ss</th>
          </tr>
        </thead>
        <tbody>
          <?php

          foreach ($hostid as $hostID) {

            $params = array(
              "output" => array("name"),
              "hostids" => $hostID
            );
            //call api
            $result = $zbx->call('host.get', $params);
            foreach ($result as $row) {
              $hostname = $row["name"];
            }

            //Current CPU Utilization Loader
            $params = array(
              "output" => array("itemid", "name", "key_"),
              "hostids" => $hostID,
              "search" => array("key_" => "upsAdvBatteryRunTimeRemaining") //seach id contains specific word
            );

            //call api method
            $result = $zbx->call('item.get', $params);
            foreach ($result as $item) {
              $itemid = $item["itemid"];
            }

            if (isset($itemid) == false) {
              print "<td>No data</td>";
            } else {
              $params = array(
                "output" => array("lastvalue"),
                "itemids" => $itemid
              );

              //call api history.get with params
              $result = $zbx->call('item.get', $params);
              foreach ($result as $row) {
                $runtime = $row["lastvalue"];
                $runtimeduration = secondsToTime($runtime);
                $exHour = secondsToHour($runtime);
                if ($exHour == 0) {
									$status = "red";
									$loader = "progress-bar progress-bar-danger";
								} else if ($exHour >= 1 && $exHour < 3) {
									$status = "orange";
									$loader = "progress-bar progress-bar-warning";
								} else if ($exHour >= 3) {
									$status = "seagreen";
									$loader = "progress-bar progress-bar-success";
								}
								// (Actual Value / Max Value) * 100 , max value is total seconds withtin a day
								$bar = ($runtime / 86400) * 100;
              }
            }
            print '<tr>
                    <td><a href="hostdetails_ups.php?hostid=' . $hostID . '#performance" target="_blank">' . $hostname . '</a></td>
                      <td>
                        <div class="progress progress-lg progress-striped active">
                          <div class="' . $loader . '" style="width: ' . $bar . '%;"></div>
                        </div>
                      </td>
                      <td style="color: white; background-color: ' . $status . ';">' . $runtimeduration . '</td>
	                  </tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>

<script>
  $(function() {
    $('#runtimetable').DataTable({
      "order": [
        [2, "desc"]
      ],
      "scrollY": "400px",
      scrollCollapse: true,
      "paging": true
    });
  });
</script>