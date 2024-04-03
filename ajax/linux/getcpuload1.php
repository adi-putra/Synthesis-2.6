<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title>Windows Overview</title>
</head>
<body>
	<div class="row">
	<div class="col-md-6">
	<!-- CPU Util table -->
	<table id="cputable" class="table table-bordered table-striped">
		<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
		<thead>
			<tr>
				<th>Host Name</th>
				<th>CPU System time (%)</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
					<?php
					foreach ($hostid as $hostID) {

						$params = array(
				          "output" => array("name"),
				          "hostids" => $hostID
				          );
				          //call api
				          $result = $zbx->call('host.get',$params);
				          foreach ($result as $row) {
				            $hostname = $row["name"];
				          }

						//Current CPU Utilization Loader
	                      $params = array(
	                        "output" => array("itemid"),
	                        "hostids" => $hostID,
	                        "search" => array("key_" => "system.cpu.util[,system]")//seach id contains specific word
	                      );
	                      //call api method
	                      $result = $zbx->call('item.get',$params);
	                      foreach ($result as $item) {
	                        $itemid = $item["itemid"];
	                      }

	                      $params = array(
	                        "output" => array("lastvalue"),
	                        "itemids" => $itemid
	                      );
	                      //call api history.get with params
	                      $result = $zbx->call('item.get',$params);
	                      foreach ($result as $row) {
	                        $usedCPU = number_format((float)$row["lastvalue"], 2, '.', '');
	                        $label = $usedCPU;

	                        if ($usedCPU > 70) {
	                        	$status = "red";
	                        	$loader = "progress-bar progress-bar-danger";
	                        }
	                        else if ($usedCPU > 30 && $usedCPU <= 70) {
	                        	$status = "orange";
	                        	$loader = "progress-bar progress-bar-warning";
	                        }
	                        else if ($usedCPU > 0 && $usedCPU <= 30) {
	                        	$status = "seagreen";
	                        	$loader = "progress-bar progress-bar-success";
	                        }

	                        else if ($usedCPU <= 0.00) {
	                        	$status = "seagreen";
	                        	$loader = "progress-bar progress-bar-success";
	                        	$label = "0.00";
	                        	$usedCPU = 1;
	                        }
	                    }

	                    print '<tr>
                    			<td><a href="hostdetails.php?hostid='.$hostID.'#performance" target="_blank">'.$hostname.'</a></td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="'.$loader.'" style="width: '.$usedCPU.'%;"></div>
	                                  </div>
	                                </td>
	                                <td style="color: white; background-color: '.$status.';">'.$label.' %</td>
	                                </tr>';
						}
					?>
						</tbody>
					</table>	
				</div>

	<div class="col-md-6">
	<!-- Used Mem table -->
	<table id="memtable" class="table table-bordered table-striped">
		<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
		<thead>
			<tr>
				<th>Host Name</th>
				<th>CPU User time (%)</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
					<?php
					foreach ($hostid as $hostID) {

						$params = array(
				          "output" => array("name"),
				          "hostids" => $hostID
				          );
				          //call api
				          $result = $zbx->call('host.get',$params);
				          foreach ($result as $row) {
				            $hostname = $row["name"];
				          }

						//Current CPU Utilization Loader
	                      $params = array(
	                        "output" => array("itemid"),
	                        "hostids" => $hostID,
	                        "search" => array("key_" => "system.cpu.util[,user]")//seach id contains specific word
	                      );
	                      //call api method
	                      $result = $zbx->call('item.get',$params);
	                      foreach ($result as $item) {
	                        $itemid = $item["itemid"];
	                      }

	                      $params = array(
	                        "output" => array("lastvalue"),
	                        "itemids" => $itemid
	                      );
	                      //call api history.get with params
	                      $result = $zbx->call('item.get',$params);
	                      foreach ($result as $row) {
	                        $usedMem = number_format((float)$row["lastvalue"], 2, '.', '');
	                        $label = $usedMem;

	                        if ($usedMem > 70) {
	                        	$status = "red";
	                        	$loader = "progress-bar progress-bar-danger";
	                        }
	                        else if ($usedMem > 30 && $usedMem <= 70) {
	                        	$status = "orange";
	                        	$loader = "progress-bar progress-bar-warning";
	                        }
	                        else if ($usedMem > 0 && $usedMem <= 30) {
	                        	$status = "seagreen";
	                        	$loader = "progress-bar progress-bar-success";
	                        }

	                        else if ($usedMem <= 0.00) {
	                        	$status = "seagreen";
	                        	$loader = "progress-bar progress-bar-success";
	                        	$label = "0.00";
	                        	$usedCPU = 1;
	                        }
	                    }

	                    print '<tr>
                    			<td><a href="hostdetails.php?hostid='.$hostID.'#performance" target="_blank">'.$hostname.'</a></td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="'.$loader.'" style="width: '.$usedMem.'%;"></div>
	                                  </div>
	                                </td>
	                                <td style="color: white; background-color: '.$status.';">'.$label.' %</td>
	                                </tr>';
						}
					?>
						</tbody>
					</table>	
				</div>
			</div>	
</body>
</html>

<script>
$(function () {
	$('#cputable').DataTable({
          "order": [[ 2, "desc" ]],
          "scrollY":        "400px",
          scrollCollapse: true,
          "paging":         true
        });
	$('#memtable').DataTable({
          "order": [[ 2, "desc" ]],
          "scrollY":        "400px",
          scrollCollapse: true,
          "paging":         true
        });
});
</script>