<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

// $hostid = $_GET["hostid"];
// $groupid = $_GET["groupid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

function secondsToTime($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	//return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
	if ($seconds < 86400) {
		return $dtF->diff($dtT)->format('%h hours %i minutes');
	} else if ($seconds >= 86400) {
		return $dtF->diff($dtT)->format('%a days');
	}
}



$params = array(
	"output" => array("eventid", "objectid", "severity", "acknowledged", "name", "clock"),
	// "hostids" => $hostid,
	// "groupids" => $groupid,
	"selectAcknowledges" => array("userid", "action", "clock", "message", "old_severity", "new_severity"),
	"time_from" => $timefrom,
	"time_till" => $timetill,
	"countOutput" => true
);
//call api problem.get only to get eventid
$result = $zbx->call('problem.get', $params);

echo $result;

?>