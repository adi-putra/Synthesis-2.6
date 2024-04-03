<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10713");
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

//display time format
$diff = $timetill - $timefrom;

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

<table  class="table table-bordered table-striped">
    <tr>
        <th>Name</th>
        <td><b>Value</b></td>
    </tr>
    <tr>
        <th style="width:30%">Memory Size</th>
        <td>
            <?php
            foreach ($hostid as $hostID) {
                $params = array(
                    "output" => array("itemid", "name", "error", "lastvalue"),
                    "hostids" => $hostID,
                    "search" => array("name" => "VMware: Memory Size"), //seach id contains specific word
                );
                //call api method
                $result = $zbx->call('item.get', $params);
                foreach ($result as $item) {
                    echo formatBytes($item["lastvalue"])."<br>";
                    echo $item["error"];
                }
            }
            ?>
        </td>
    </tr>
</table>