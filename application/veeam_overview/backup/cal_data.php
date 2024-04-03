<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
ini_set("memory_limit", "-1");

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

$hostid = $_GET["hostid"];

//time variables
$timefrom = strtotime($_GET['start']);
$timetill = strtotime($_GET['end']);

// $timefrom = strtotime("today");
// $timetill = strtotime("now");

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//display time format
$diff = $timetill - $timefrom;

//store itemid array
$itemid = array();
$count2 = 0;
$series = array();
$color = array();


foreach ($hostid as $hostID) {

    $params = array(
    "output" => array("name"),
    "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    //last run time job
    $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("name" => "Veeam Backup Job List"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    if (!empty($result)) {
        foreach ($result as $item) {

          // $itemname = str_replace("Zabbix","Synthesis",$item["name"]);

          $itemid[] = array(
              "id" => $item["itemid"], 
              "name" => $item["name"],
              "hostname" => $gethostname,
          );

        }
    }
}

// print "<pre>";
// print json_encode($itemid, JSON_PRETTY_PRINT);
// print "</pre>";

$itemdata = array();
$sub_itemdata = array();

$itemseries = "";

$counthost = 0;


foreach ($itemid as $item) {

  $params = array(
      "output" => "extend",
      "itemids" => $item["id"],
      "history" => 4,
      "sortfield" => "clock",
      "sortorder" => "DESC",
      "time_from" => $timefrom,
      "time_till" => $timetill
  );
  //call api history.get with params
  $result1 = $zbx->call('history.get', $params);
  if (!empty($result1)) {

    //check last value to prevent repeating values
    $check_lastval = null;

      foreach ($result1 as $history1) {
        
        $count_str = 0;
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $history1["value"]) as $line){
          if ($count_str == 0 || $count_str == 1) {
            $count_str++;
            continue;
          }
          else {
            $line = str_replace('"', '', $line);
            $array_str = explode(',', $line); //split string into array seperated by ', '
            for ($i=0; $i < count($array_str); $i++) { //loop over values

              $gethostveeam = str_replace("Zabbix","Synthesis",$array_str[0]);
              
              $job_lastbackstart = str_replace('/', '-', $array_str[2]);
              $job_lastbackstart_unix = strtotime($job_lastbackstart);
              $job_lastbackstart_txt = date('d-m-Y h:i:s A', strtotime($job_lastbackstart));
              $job_lastbackstart_iso = new DateTime($job_lastbackstart_txt);
              $job_lastbackstart_iso = $job_lastbackstart_iso->format(DateTime::ATOM);
              
              $job_lastbackend = str_replace('/', '-', $array_str[3]);
              $job_lastbackend_unix = strtotime($job_lastbackend);
              $job_lastbackend_txt = date('d-m-Y h:i:s A', strtotime($job_lastbackend));
              $job_lastbackend_iso = new DateTime($job_lastbackend_txt);
              $job_lastbackend_iso = $job_lastbackend_iso->format(DateTime::ATOM);

              $job_duration = gmdate("H:i:s", $job_lastbackend_unix - $job_lastbackstart_unix);

              //color based on backup result
              if ($array_str[1] == "Success") {
                $color = "#00873E";
              }
              else {
                if ($array_str[1] == "None" && stripos($job_lastbackend_iso, "1990") !== false) {
                  $color = "#E10600";
                }
                else {
                  continue;
                }
              }

              if ($job_lastbackstart_iso !== $check_lastval) {
                $itemdata[] = array(
                  "name" => $gethostveeam." ".$array_str[1]." ".$job_lastbackstart_txt." ".$job_lastbackend_txt,
                  "title" => $gethostveeam,
                  "start" => $job_lastbackstart_iso,
                  "end" => $job_lastbackend_iso,
                  "desc" => "<b>Host: </b>".$item["hostname"]."<br>"."<b>Job: </b>".$gethostveeam."<br>"."<b>Backup Start: </b>".$job_lastbackstart_txt."<br>"."<b>Backup End: </b>".$job_lastbackend_txt."<br>"."<b>Result: </b>".$array_str[1]."<br>"."<b>Duration: </b>".$job_duration,
                  "color" => $color
                );
              }

              $check_lastval = $job_lastbackstart_iso;

            }
            
            $count_str++;
          }
        } 

        // $itemdata[] = array(
        //   "title" => $job_name,
        //   "start" => $job_lastbackstart,
        //   "end" => $job_lastbackend,
        //   "color" => 
        // );
      }
  }
  // $series[$count2] = array(
  //     "name" => $item["hostname"].": ".$item["name"],
  //     "data" => $itemdata
  // );
  // $count2++;
}

// $itemdata = array_unique($itemdata);

// $itemseries = json_encode($itemdata, JSON_PRETTY_PRINT);
// $itemseries .= $itemdata;

// for ($i=0; $i < $count2; $i++) { 
//     $color[] = "#".random_color();
// }

$itemseries = [];
foreach($itemdata as $item) {
    $hash = $item["name"];
    $itemseries[$hash] = $item;
}

// $itemseries = array_map("unserialize", array_unique(array_map("serialize", $itemdata)));

function _group_by($array, $key) {
  $return = array();
  foreach($array as $val) {
      $return[$val[$key]][] = $val;
  }
  return $return;
}

$itemseries = json_encode(array_values($itemseries));

// print "<pre>";
echo $itemseries;
// print json_encode(_group_by($itemseries, 'date_vm'), JSON_PRETTY_PRINT);
// print "</pre>";

// die();
?>