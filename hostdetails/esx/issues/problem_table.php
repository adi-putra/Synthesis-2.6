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
  }
  else if ($seconds >= 86400) {
	return $dtF->diff($dtT)->format('%a days');
  }
}

?>
<html>
<table id="problemstable" class="display" cellspacing="0" width="100%">
	<thead>
	        <tr>
	          <th></th>
	          <th>Time</th>
	          <th>Problem</th>
	          <th>Severity</th>
	          <th>Acknow.</th>
			  <th class="none">Duration</th>
			  <th class="none">Message</th>
			  <th class="none">Description</th>
	        </tr>
	      </thead>
	      <tbody>
	        <?php
	        $params = array(
	        "output" => array("eventid", "severity", "acknowledged", "name", "clock"),
	        "hostids" => $hostid
	      );
	    //call api problem.get only to get eventid
	    $result = $zbx->call('problem.get',$params);
	    //count id
	    $id = 1;

	      foreach ($result as $v) {
	        $clock = $v["clock"];
	        $clock1 = date("Y-m-d\ H:i:s A\ ", $clock);
	        print "<tr>
	              <td></td>";
	        $id = $id + 1;
	        print "<td>$clock1</td>";

	        $name = $v['name'];
	        $severity = $v['severity'];
	        $acknowledged = $v['acknowledged'];
			$eventid = $v['eventid'];

			//get alert message for the problem
			$params = array(
				"output" => array("message"),
				"eventids" => $eventid
			  );
			//call api problem.get only to get eventid
			$result = $zbx->call('alert.get',$params);

			foreach ($result as $alert) {
				$message_str = $alert["message"];
				//capture operational data value only
				$startpoint_str = stripos($message_str, "operational data");
				$endpoint_str = stripos($message_str, "original problem");
				$message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
				//$message = substr($message_str, 0, -$endpoint_str);
				//$message = rtrim($message, "Original");
			}

			//get description for the problem
			$params = array(
				"output" => array("comments"),
				"search" => array("description" => array("$name"))
			  );
			//call api problem.get only to get eventid
			$result = $zbx->call('trigger.get',$params);

			foreach ($result as $trigger) {
				$description = $trigger["comments"];
			}

	        print "<td>$name</td>";

	        //severity
	        print "<td style='text-align: center; padding: 0;'>";
	        if ($severity == 1) {
	          print "<button class='btn bg-blue margin' style='margin: 0px;'>Info</button>";
	        }
	        else if ($severity == 2) {
	          print "<button class='btn bg-yellow margin' style='margin: 0px;'>Warning</button>";
	        }
	        else if ($severity == 3) {
	          print "<button class='btn bg-orange margin' style='margin: 0px;'>Average</button>";
	        }
	        else if ($severity == 4) {
	          print "<button class='btn bg-red margin' style='margin: 0px;'>High</button>";
	        }
	        else if ($severity == 5) {
	          print "<button class='btn bg-red margin' style='margin: 0px;'>Danger</button>";
	        }
	        else {
	          print "<button class='btn bg-white margin' style='margin: 0px;'>Not Classified</button>";
	        }
	        print "</td>";

	        //acknowledge
	        print "<td style='text-align: center; padding: 0;'>";
	        if ($acknowledged == 1) {
	          print "<button class='btn bg-white margin' style='margin: 0px;'>Yes</button>";
	        }
	        else {
	          print "<button class='btn bg-white margin' style='margin: 0px;'>No</button>";
	        }
			

			//problem duration
			$time_diff = time() - $clock;
			$duration = secondsToTime($time_diff);
			print "<td>$duration</td>";

			//problem message
			print "<td>$message</td>";

			//problem description
			print "<td>$description</td>";

			//end
	        print "</td></tr>";
	  
	        }
	        
	        ?>
	    </tbody>
</table>
</html>

<script>
	$(document).ready(function (){
    var table = $('#problemstable').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        columnDefs: [ {
            className: 'control',
            orderable: false,
            targets:   0
        } ],
        order: [ 1, 'desc' ]
    } );
});
</script>