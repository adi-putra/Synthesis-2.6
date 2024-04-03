<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
  "output" => array("name"),
  "hostids" => $hostid,
  "selectInterfaces"
);
//call api method
$result = $zbx->call('host.get', $params);
foreach ($result as $host) {
  $hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
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

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<body>

  <table class="table table-bordered table-striped">
    <tr>
      <th>Autosupport Status</th>
      <td>
        <?php
        //Autosupport Status
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Autosupport Status") //seach id contains specific word
        );

        //call api problem.get only to get eventid
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print '<button class="btn btn-block btn-default">No data</button>';
        } else {
          foreach ($result as $item) {
            if (stripos($item["lastvalue"], "message") !== false) {
              continue;
            } else {
              $status = $item['lastvalue'];
            }
          }
        }

        if ($status == 1) {
          $status = '<button class="btn btn-block btn-success">OK</button>';
        } else {
          $status = '<button class="btn btn-block btn-danger">Failed</button>';
        }

        print $status;
        ?>
      </td>
    </tr>
    <tr>
      <th>Autosupport Status Message</th>
      <td>
        <?php
        //Autosupport Status
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Autosupport Status Message") //seach id contains specific word
        );

        //call api problem.get only to get eventid
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $status = $item['lastvalue'];
          }
        }

        print $status;
        ?>
      </td>
    </tr>
    <tr>
      <th>Autosupport Successful Sends</th>
      <td>
        <?php
        //Autosupport Status
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Autosupport Successful Sends") //seach id contains specific word
        );

        //call api problem.get only to get eventid
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            if (stripos($item["lastvalue"], "message") !== false) {
              continue;
            } else {
              $status = $item['lastvalue'];
            }
          }
        }

        print $status;
        ?>
      </td>
    </tr>
    <tr>
      <th>Autosupport Failed Sends</th>
      <td>
        <?php
        //Autosupport Status
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Autosupport Failed Sends") //seach id contains specific word
        );

        //call api problem.get only to get eventid
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            if (stripos($item["lastvalue"], "message") !== false) {
              continue;
            } else {
              $status = $item['lastvalue'];
            }
          }
        }

        print $status;
        ?>
      </td>
    </tr>
  </table>

</body>

</html>