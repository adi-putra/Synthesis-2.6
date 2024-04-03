<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"] ?? array("10361", "10324");

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//setup for link to pivot problems
$counthost = 0;
$hostArr = "";
foreach ($hostid as $hostID) {
	$hostArr .= "hostid[]=".$hostID."&";
	$counthost++;
}
$formlink = "/adminlte/pivot_problems.php?".$hostArr; 
$timerange = "timefrom=".$timefrom."&timetill=".$timetill;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title>Windows Overview</title>
</head>
<body>
	<a href="<?php echo $formlink.$timerange; ?>" target="_blank"><button class="btn bg-green" style="float: right;"><i class="fa fa-mail-forward"></i> Show Full Report</button></a>
	<br><br><br>
	<!-- CPU Util table -->
	<table id="eventstable" class="table table-bordered table-striped">
		<caption><i>Date range: <?php echo date("d/m/y h:i A", $timefrom)." - ".date("d/m/y h:i A", $timetill); ?></i></caption>
		<thead>
			<tr>
				<th>Host Name</th>
				<th style='background-color: red; color: white;'>Disaster</th>
				<th style='background-color: tomato; color: white;'>High</th>
				<th style='background-color: orange; color: white;'>Average</th>
				<th style='background-color: gold; color: white;'>Warning</th>
				<th style='background-color: blue; color: white;'>Info</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
					<?php

					//get disk space on percentage
					foreach ($hostid as $hostID) {

						//count total problems
						$totalproblems = 0;

						$params = array(
				          "output" => array("name"),
				          "hostids" => $hostID
				          );
				          //call api
				          $result = $zbx->call('host.get',$params);
				          foreach ($result as $row) {
				            $hostname = $row["name"];
				          }

				        print "<tr>
				        <td><a href='hostdetails.php?hostid=".$hostID."#issues' target='_blank'>$hostname</td>";


				        //count severity = Disaster
	                    $params = array(
	                    "output" => array("severity"),
	                    "hostids" => $hostID,
	                    "severities" => "5",
	                    "time_from" => $timefrom,
	                    "time_till" => $timetill,
	                    "countOutput" => true
	                    );
	                    //call api problem.get only to get eventid
	                    $result = $zbx->call('problem.get',$params);
	                    $totalproblems = $totalproblems + $result;
	                    
	                    print "<td>$result</td>";

	                    //count severity = High
	                    $params = array(
	                    "output" => array("severity"),
	                    "hostids" => $hostID,
	                    "severities" => "4",
	                    "time_from" => $timefrom,
	                    "time_till" => $timetill,
	                    
	                    "countOutput" => true
	                    );
	                    //call api problem.get only to get eventid
	                    $result = $zbx->call('problem.get',$params);
	                    $totalproblems = $totalproblems + $result;
	                    
	                    print "<td>$result</td>";

	                    //count severity = Average
	                    $params = array(
	                    "output" => array("severity"),
	                    "hostids" => $hostID,
	                    "severities" => "3",
	                    "time_from" => $timefrom,
	                    "time_till" => $timetill,
	                    
	                    "countOutput" => true
	                    );
	                    //call api problem.get only to get eventid
	                    $result = $zbx->call('problem.get',$params);
	                    $totalproblems = $totalproblems + $result;
	                    
	                    print "<td>$result</td>";

	                    //count severity = Warning
	                    $params = array(
	                    "output" => array("severity"),
	                    "hostids" => $hostID,
	                    "severities" => "2",
	                    "time_from" => $timefrom,
	                    "time_till" => $timetill,
	                    
	                    "countOutput" => true
	                    );
	                    //call api problem.get only to get eventid
	                    $result = $zbx->call('problem.get',$params);
	                    $totalproblems = $totalproblems + $result;

	                    print "<td>$result</td>";


	                    //count severity = Info
	                    $params = array(
	                    "output" => array("severity"),
	                    "hostids" => $hostID,
	                    "severities" => "1",
	                    "time_from" => $timefrom,
	                    "time_till" => $timetill,
	                    
	                    "countOutput" => true
	                    );
	                    //call api problem.get only to get eventid
	                    $result = $zbx->call('problem.get',$params);
	                    $totalproblems = $totalproblems + $result;

	                    print "<td>$result</td>";

	                    //print total problems
	                    print "<td>$totalproblems</td>";
	                    print "</tr>";
                }

?>				
		</tbody>
	</table>
	<script type="text/javascript">
      $(function () {
        $('#eventstable').DataTable({
        	"order": [[ 6, "desc" ]],
        	"scrollY":        "400px",
          	scrollCollapse: true,
          	"paging":         true
        })
      });
    </script>
</body>
</html>
