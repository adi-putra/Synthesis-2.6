<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$groupid = $_GET["groupid"] ?? 16;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title>Windows Overview</title>
</head>
<body>
	<table id="problems" class="table table-bordered table-striped">
        <thead>
            <tr>
            	<th>Time</th>
                <th>Severity</th>
                <th>Host</th>
                <th>Problem</th>
                <th>Acknowledge</th>
            </tr>
            <?php
            $params = array(
				"output" => "extend",
				"groupids" => $groupid,
				"selectHosts" => array("hostid","name"),
				"sortfield" => array("clock"),
				"sortorder" => "DESC"
			);

			//call api method
			$result = $zbx->call('event.get',$params);
			foreach ($result as $event) {
				$eventId = $event["eventid"];
				$eventTime = date("d-m-y h:i:s A", $event["clock"]);
				$eventName = $event["name"];
				$eventAck = $event["acknowledged"];
				$eventSvrty = $event["severity"];
				if ($eventSvrty == 1) {
					$eventSvrty = "<button class='btn bg-aqua'>Info</button>";
				}
				elseif ($eventSvrty == 2) {
					$eventSvrty = "<button class='btn bg-yellow'>Warning</button>";
				}
				elseif ($eventSvrty == 3) {
					$eventSvrty = "<button class='btn bg-orange'>Average</button>";
				}
				elseif ($eventSvrty == 4) {
					$eventSvrty = "<button class='btn bg-light red'>High</button>";
				}
				elseif ($eventSvrty == 5) {
					$eventSvrty = "<button class='btn bg-red'>Disaster</button>";
				}
				else {
					$eventSvrty = "<button class='btn bg-default'>Unclassified</button>";
				}
				foreach ($event["hosts"] as $host) {
					$hostId = $host["hostid"];
					$hostName = $host["name"];
				}

				print "<tr>";
				print "<td>$eventTime</td>";
				print "<td>$eventSvrty</td>";
				print "<td>$hostName</td>";
				print "<td>$eventName</td>";
				print "<td>$eventAck</td>";
				print "</tr>";
			}
            ?>
        </thead>
    </table>
</body>

<script type="text/javascript">
	$(document).ready(function() {
	    $('#problems').DataTable( {
	        "scrollY":        "500px",
	        "scrollCollapse": true,
	        "paging":         false
	    } );
	} );
</script>
</html>