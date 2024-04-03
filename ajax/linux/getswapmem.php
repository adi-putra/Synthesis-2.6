<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title>Linux Overview</title>
</head>
<body>
	<div class="row">
	<div class="col-md-12">
	<!-- CPU Util table -->
	<table id="swapmemtable" class="table table-bordered table-striped">
		<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
		<thead>
			<tr>
				<th>Host Name</th>
				<th>Free Swap Space (%)</th>
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
	                        "search" => array("key_" => "system.swap.size[,pfree]")//seach id contains specific word
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
	                        $swapMem = number_format((float)$row["lastvalue"], 2, '.', '');
	                        $label = $swapMem;

	                        if ($swapMem > 70) {
	                        	$status = "seagreen";
	                        	$loader = "progress-bar progress-bar-success";
	                        }
	                        else if ($swapMem > 30 && $swapMem <= 70) {
	                        	$status = "orange";
	                        	$loader = "progress-bar progress-bar-warning";
	                        }
	                        else if ($swapMem > 0 && $swapMem <= 30) {
	                        	$status = "red";
	                        	$loader = "progress-bar progress-bar-danger";
	                        }

	                        else if ($swapMem <= 0.00) {
	                        	$status = "red";
	                        	$loader = "progress-bar progress-bar-danger";
	                        	$label = "0.00";
	                        	$swapMem = 1;
	                        }
	                    }

	                    print '<tr>
                    			<td><a href="hostdetails_linux.php?hostid='.$hostID.'#performance" target="_blank">'.$hostname.'</a></td>
	                              <td>
	                    			<div class="progress progress-lg progress-striped active">
	                                    <div class="'.$loader.'" style="width: '.$swapMem.'%;"></div>
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
	$('#swapmemtable').DataTable({
          "order": [[ 2, "asc" ]],
          "scrollY":        "400px",
          scrollCollapse: true,
          "paging":         true
        });
});
</script>