<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title>Windows Overview</title>
</head>
<body>
	<div class="row">
	<div class="col-md-12">
	<!-- CPU Util table -->
	<table id="forecastmem_table" class="table table-bordered table-striped">
		<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
		<thead>
			<tr>
				<th>Host Name</th>
                <th>Forecast Used Memory in 30 days (%)</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
        <?php

        //get disk space on percentage
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

            $params = array(
            "output" => array("name", "lastvalue"),
            "hostids" => $hostID,
            "search" => array("name" => "forecast used memory")
            );
            //call api
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {

                $lastvalue = number_format((float)$item["lastvalue"], 2, '.', '');
                $label = $lastvalue;

                if ($lastvalue > 70) {
                    $status = "red";
                    $loader = "progress-bar progress-bar-danger";
                } else if ($lastvalue > 30 && $lastvalue <= 70) {
                    $status = "orange";
                    $loader = "progress-bar progress-bar-warning";
                } else if ($lastvalue > 0 && $lastvalue <= 30) {
                    $status = "seagreen";
                    $loader = "progress-bar progress-bar-success";
                } else if ($lastvalue <= 0.00) {
                    $status = "red";
                    $loader = "progress-bar progress-bar-danger";
                    $label = "0.00";
                    $lastvalue = 1;
                }
            

                print '<tr>
                    <td><a href="hostdetails_linux.php?hostid=' . $hostID . '#forecast" target="_blank">' . $hostname . '</a></td>
                        <td>
                        <div class="progress progress-lg progress-striped active">
                            <div class="' . $loader . '" style="width: ' . $lastvalue . '%;"></div>
                            </div>
                        </td>
                        <td style="color: white; background-color: ' . $status . ';">' . $label . ' %</td>
                        </tr>';

            }  

        }

        ?>	
		</tbody>
	</table>
</div>
</div>
	<script type="text/javascript">
      $(function () {
        $('#forecastmem_table').DataTable({
          "order": [[ 2, "desc" ]],
          "scrollY":        "400px",
          "scrollCollapse": true,
          "paging":         false
        });
      });
    </script>
</body>
</html>
