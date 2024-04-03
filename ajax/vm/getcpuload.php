<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Windows Overview</title>
</head>

<body>
	<?php
	include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
	$hostid = $_GET["hostid"];


	//Current CPU Utilization Box
	$params = array(
		"output" => array("itemid"),
		"hostids" => $hostid,
		"search" => array("key_" => "system.cpu.util[all,system,avg1]") //seach id contains specific word
	);
	//call api method
	$result = $zbx->call('item.get', $params);
	foreach ($result as $item) {
		$itemid = $item["itemid"];
	}

	$params = array(
		"output" => array("lastvalue"),
		"itemids" => $itemid
	);
	//call api history.get with params
	$result = $zbx->call('item.get', $params);
	foreach ($result as $row) {
		$usedCPU = $row["lastvalue"];
		$usedCPU = number_format((float)$usedCPU, 2, '.', '');
	}

	print '<table class="table table-bordered table-striped">
	                              <tr>
	                                <th>CPU Utilization</th>
	                              </tr>
	                              <tr>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="progress-bar progress-bar-primary" style="width: ' . $usedCPU . '%;"></div>
	                                  </div>
	                                </td>
	                                <td><span class="badge bg-light-blue">' . $usedCPU . '%</span></td>
	                                </tr>
	                              </table>';

	?>

</body>

</html>