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
	<table id="forecastdisk_table" class="table table-bordered table-striped">
		<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
		<thead>
			<tr>
				<th>Host Name</th>
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
            "output" => array("name", "lastvalue", "key_"),
            "hostids" => $hostID,
            "search" => array("key_" => "vfs.fs.forecast.30d")
            );
            //call api
            $result = $zbx->call('item.get',$params);
            foreach ($result as $item) {

                $lastvalue = $item["lastvalue"];

                if ($lastvalue > 10737418240) {
                    $status = "green";
                } else if ($lastvalue > 1073741824 && $lastvalue <= 10737418240) {
                    $status = "orange";
                } else if ($lastvalue > 0 && $lastvalue <= 1073741824) {
                    $status = "red";
                } else if ($lastvalue <= 0.00) {
                    $status = "red";
                }

                print '<tr>';
                print '<td><a href="hostdetails_windows.php?hostid=' . $hostID . '#forecast" target="_blank">' . $hostname . "</a> : " . $item["name"] . '</td>';
                print '<td style="color: white; background-color: ' . $status . ';">' . formatBytes($lastvalue) . ' </td>';
                print '</tr>';
            }  

        }

        ?>	
		</tbody>
	</table>
</div>
</div>

</body>
</html>

<script type="text/javascript">
    $(function () {
    $('#forecastdisk_table').DataTable({
        "columnDefs": [
            { "type": "file-size", "targets": 1 }
        ],
        "order": [[ 1, "asc" ]],
        "scrollY":        "400px",
        "scrollCollapse": true,
        "paging":         false
    });
    });
</script>