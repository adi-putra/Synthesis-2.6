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

  <table id="enclosure_table" class="table table-bordered table-striped">
    <tr>
      <th>Current Temperature</th>
      <td>
        <?php
        //Current Temperature
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Current Temperature") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Current Voltage</th>
      <td>
        <?php
        //Current Voltage
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Current Voltage") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Electronic</th>
      <td>
        <?php
        //Electronic
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Electronic") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Failed Electronic</th>
      <td>
        <?php
        //Failed Electronic
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Failed Electronic") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Failed Fans</th>
      <td>
        <?php
        //Failed Fans
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Failed Fans") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Failed Power Supplies</th>
      <td>
        <?php
        //Failed Power Supplies
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Failed Power Supplies") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Fans Speed</th>
      <td>
        <?php
        //Fans Speed
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Fans Speed") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Over Temperature fail</th>
      <td>
        <?php
        //Over Temperature fail
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Over Temperature fail") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Over Temperature warn</th>
      <td>
        <?php
        //Over Temperature warn
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Over Temperature warn") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Over Voltage fail</th>
      <td>
        <?php
        //Over Voltage fail
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Over Voltage fail") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Over Voltage warn</th>
      <td>
        <?php
        //Over Voltage warn
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Over Voltage warn") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Product ID</th>
      <td>
        <?php
        //Product ID
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Product ID") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Product Model</th>
      <td>
        <?php
        //Product Model
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Product Model") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Under Temperature fail</th>
      <td>
        <?php
        //Under Temperature fail
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Under Temperature fail") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Under Temperature warn</th>
      <td>
        <?php
        //Under Temperature warn
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Under Temperature warn") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Under Voltage fail</th>
      <td>
        <?php
        //Under Voltage fail
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Under Voltage fail") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
    <tr>
      <th>Under Voltage warn</th>
      <td>
        <?php
        //Under Voltage warn
        $params = array(
          "output" => array("itemid", "name", "lastvalue"),
          "hostids" => $hostid,
          "search" => array("name" => "Under Voltage warn") //seach id contains specific word
        );
        //call api method
        $result = $zbx->call('item.get', $params);
        if (empty($result)) {
          print "No data";
        } else {
          foreach ($result as $item) {
            $result = $item['lastvalue'];
          }
        }

        if ($result == "") {
          $result = "No Data";
        }

        print $result;
        ?>
      </td>
    </tr>
  </table>


</body>

</html>
