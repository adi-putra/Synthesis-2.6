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

<?php
function checkservice($servicestatus)
{
  if ($servicestatus == 255) {
    $servicestatus = "<button class='btn btn-block btn-danger btn-sm' data-order='0'>NOT EXIST</button>";
  } else if ($servicestatus == 0) {
    $servicestatus = "<button class='btn btn-block btn-success btn-sm' data-order='8'>RUNNING</button>";
  } else if ($servicestatus == 1) {
    $servicestatus = "<button class='btn btn-block btn-success btn-sm' data-order='7'>AVAILABLE</button>";
  } else if ($servicestatus == 2) {
    $servicestatus = "<button class='btn btn-block btn-success btn-sm' data-order='6'>START PENDING</button>";
  } else if ($servicestatus == 3) {
    $servicestatus = "<button class='btn btn-block btn-warning btn-sm' data-order='4'>PAUSE PENDING</button>";
  } else if ($servicestatus == 4) {
    $servicestatus = "<button class='btn btn-block btn-success btn-sm' data-order='5'>CONTINUE PENDING</button>";
  } else if ($servicestatus == 5) {
    $servicestatus = "<button class='btn btn-block btn-danger btn-sm' data-order='3'>STOP PENDING</button>";
  } else if ($servicestatus == 6) {
    $servicestatus = "<button class='btn btn-block btn-danger btn-sm' data-order='1'>STOPPED</button>";
  } else if ($servicestatus == 7) {
    $servicestatus = "<button class='btn btn-block btn-danger btn-sm' data-order='2'>UNKOWN</button>";
  }
  print "<td>$servicestatus</td>";
}

?>

<body>
  <table id="dbservices_table" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Services</th>
        <th class="defaultSort">Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>SQL Server Services</th>
        <?php
        $params = array(
          "output" => array("itemid"),
          "hostids" => $hostid,
          "search" => array("name" => "SQL Server Services") //seach id contains specific word
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
            $servicestatus = $row["lastvalue"];
            checkservice($servicestatus);
          }
        }
        ?>
      </tr>
      <tr>
        <th>SQL Agent Services</th>
        <?php
        $params = array(
          "output" => array("itemid"),
          "hostids" => $hostid,
          "search" => array("name" => "SQL Agent Services") //seach id contains specific word
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
            "itemids" => $itemid,
            "sortfield" => "itemid",
            "sortorder" => "DESC"
          );

          //call api history.get with params
          $result = $zbx->call('item.get', $params);
          foreach ($result as $row) {
            $servicestatus = $row["lastvalue"];
            checkservice($servicestatus);
          }
        }
        ?>
      </tr>
      <tr>
        <th>SQL Server Port Availability</th>
        <?php
        $params = array(
          "output" => array("itemid"),
          "hostids" => $hostid,
          "search" => array("name" => "SQL Server Port Availability") //seach id contains specific word
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
            $servicestatus = $row["lastvalue"];
            checkservice($servicestatus);
          }
        }
        ?>
      </tr>
      <tr>
        <th>Analysis Services</th>
        <?php
        $params = array(
          "output" => array("itemid"),
          "hostids" => $hostid,
          "search" => array("name" => "Analysis Services") //seach id contains specific word
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
            $servicestatus = $row["lastvalue"];
            checkservice($servicestatus);
          }
        }
        ?>
      </tr>
      <tr>
        <th>Integration Services</th>
        <?php
        $params = array(
          "output" => array("itemid"),
          "hostids" => $hostid,
          "search" => array("name" => "Integration Services") //seach id contains specific word
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
            $servicestatus = $row["lastvalue"];
            checkservice($servicestatus);
          }
        }
        ?>
      </tr>
      <tr>
        <th>Reporting Services</th>
        <?php
        $params = array(
          "output" => array("itemid"),
          "hostids" => $hostid,
          "search" => array("name" => "Reporting Services") //seach id contains specific word
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
            $servicestatus = $row["lastvalue"];
            checkservice($servicestatus);
          }
        }
        ?>
      </tr>
    </tbody>
  </table>
</body>

</html>

<script type="text/javascript">
  $(document).ready(function() {
    $('#dbservices_table').dataTable();
  });
</script>