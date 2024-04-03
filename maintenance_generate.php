<?php 
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include "session.php";

$main_name = $_POST["main_name"];
$main_activesince = $_POST["main_activesince"];
$main_activetill = $_POST["main_activetill"];
$main_desc = $_POST["main_desc"];
$period = $_POST["period"];
$main_groups = $_POST["main_groups"] ?? array();
$main_hosts = $_POST["main_hosts"] ?? array();

$main_activesince = strtotime($main_activesince);
$main_activetill = strtotime($main_activetill);

$period_array = array();
$count = 0;

foreach ($period as $p) {
    if ($p["timeperiod_type"] == 0) {
        // Remove specific keys
        unset($p['every']);
        unset($p['start_time']);
        unset($p['day']);
        unset($p['dayofweek']);
        unset($p['month']);
    }
    else if ($p["timeperiod_type"] == 2) {
        // Remove specific keys
        unset($p['start_date']);
        unset($p['day']);
        unset($p['dayofweek']);
        unset($p['month']);
    }
    else if ($p["timeperiod_type"] == 3) {
        // Remove specific keys
        unset($p['start_date']);
        unset($p['day']);
        unset($p['month']);
    }
    else if ($p["timeperiod_type"] == 4) {
        // Remove specific keys
        unset($p['start_date']);
    }

    $period_array[$count] = $p;
    $count++;
}

// print $main_id."<br>";
// print $main_name."<br>";
// print $main_activesince."<br>";
// print $main_activetill."<br>";
// print $main_desc."<br>";
// print json_encode($period_array)."<br>";
// print json_encode($main_groups)."<br>";
// print json_encode($main_hosts)."<br>";

if (empty($main_groups) && empty($main_hosts)) {
    //display alert popup
    echo '<script language="javascript">';
    echo 'alert("Process aborted. Please select at least one host group/host.");';
    echo 'history.back();';
    echo '</script>';
}
else if (empty($period_array)) {
    //display alert popup
    echo '<script language="javascript">';
    echo 'alert("Process aborted. Please create at least one maintenance period.");';
    echo 'history.back();';
    echo '</script>';
}
else {
    $params = array(
        "name" => $main_name,
        "maintenance_type" => 1,
        "description" => $main_desc,
        "active_since" => $main_activesince,
        "active_till" => $main_activetill,
        "timeperiods" => $period_array,
        "groupids" => $main_groups,
        "hostids" => $main_hosts
    );

    // echo "<pre>";
    // echo json_encode($params, JSON_PRETTY_PRINT);
    // echo "</pre>";
    
    $zbx->call('maintenance.create',$params);
    
    // display success popup
    echo '<script language="javascript">';
    echo 'alert("Successfully Created!");';
    echo 'location.assign("maintenance.php");';
    echo '</script>';
}
?>