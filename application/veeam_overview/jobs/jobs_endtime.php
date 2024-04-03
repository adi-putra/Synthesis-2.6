<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$hostid = $_GET["hostid"];

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
</head>

<body>
	<?php
    //count host
	$countHost = count($hostid);

	//col classes
	if ($countHost == 1) {
		$colclass = "col-md-12";
		$divider = 1;
	}
	else if ($countHost == 2) {
		$colclass = "col-md-6";
		$divider = 2;
	}
	else if ($countHost == 3) {
		$colclass = "col-md-4";
		$divider = 3;
	}
	else if ($countHost >= 4) {
		$colclass = "col-md-3";
		$divider = 4;
	}

	//counters
	$count = 1;

	foreach ($hostid as $hostID) {

		//get hostname
		$params = array(
		"output" => array("name"),
		"hostids" => $hostID,
		);
		//call api method
		$result = $zbx->call('host.get',$params);
		foreach ($result as $host) {
			$gethostname = $host["name"];
		}
		
		//start print table
		if ($count % $divider == 0) {
			print '<div class="row">
				<div class="'.$colclass.'">
				<table class="table table-condensed">
				<caption align="center">'.$gethostname.'</caption>';
		}
		else {
			print '<div class="'.$colclass.'">
				<table class="table table-condensed">
				<caption>'.$gethostname.'</caption>';
		}

		//get db status
		$params = array(
		"output" => array("itemid", "name", "lastvalue"),
		"hostids" => $hostID,
		"search" => array("key_" => 'vbr[LastEndTime'),//seach id contains specific word
		);
		//call api method
		$result = $zbx->call('item.get',$params);
        if (empty($result)) {
            print '<tr>
				<td style="width:40%">No data</td>
				</tr>';
        }
        else {
            foreach ($result as $item) {
            
                $item_name = substr($item["name"], 20);

                $jobs_endtime = date("d-m-Y h:i A", $item["lastvalue"]);

                $span_class = "badge bg-green";
    
                print '<tr>
                    <td>'.$item_name.'</td>
                    <td><span class="'.$span_class.'">'.$jobs_endtime.'</span></td>
                    </tr>';
    
            }
        }
        
		if ($count % $divider == 0) {
			print '</table>
				</div>
				</div>';
		}
		else if ($count == $countHost) {
			print '</table>
				</div>
				</div>';
		}
		else {
			print '</table>
				</div>';
		}

		$count++;
	}
	?>

<script type="text/javascript">
	$(document).ready(function() {
    $('#dbstatus_table').DataTable();
} );
</script>

</html>