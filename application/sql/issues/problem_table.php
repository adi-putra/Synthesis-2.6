<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];
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

?>
<html>

<head>
	<style>
		.popover {
			width: auto;
			height: auto;
			max-width: none;
			max-height: none;
		}

		.popover-content {
			font-size: 11px;
		}

		.example-modal .modal {
			position: relative;
			top: 250px;
			bottom: auto;
			right: auto;
			left: 150px;
			display: block;
			z-index: 1;
		}

		.example-modal .modal {
			background: transparent !important;
		}
	</style>
</head>
<table id="problemstable" class="display" cellspacing="0" width="100%">
	<tbody>
		<p class="text-danger">Under Developement...</p>
	</tbody>
</table>


</html>