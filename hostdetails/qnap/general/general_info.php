<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET['hostid'];


//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid
);
//call api method
$result = $zbx->call('host.get', $params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2)
{
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
	<title>general Info - qnap</title>
</head>

<body>
	<table id="generalinfo_tbl" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><b>Item</b></th>
                <td><b>Value</b></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php
                $params = array(
                    "output" => array("name","lastvalue"),
                    "hostids" => $hostid,
                    "search" => array("key_" => "system.name")
                );
                //call api method
                $result = $zbx->call('item.get',$params);
                foreach($result as $row){

                    print "<th>".$row['name']."</th>";

                    if(!empty($row['lastvalue'])){

                        print "<td>".$row['lastvalue']."</td>";

                    }else{

                        print "<td>No Data</td>";
                    }
                }
                ?>
            </tr>
            
            <tr>
                <?php
                $params = array(
                    "output" => array("name","lastvalue"),
                    "hostids" => $hostid,
                    "search" => array("name" => "SNMP traps (fallback)")
                );
                //call api method
                $result = $zbx->call('item.get',$params);
                foreach($result as $row){

                    print "<th>".$row['name']."</th>";

                    if(!empty($row['lastvalue'])){

                        print "<td>".$row['lastvalue']."</td>";

                    }else{

                        print "<td>No Data</td>";
                    }
                }
                ?>
            </tr>

            <tr>
                <?php
                $params = array(
                    "output" => array("name","lastvalue"),
                    "hostids" => $hostid,
                    "search" => array("name" => "System contact details")
                );
                //call api method
                $result = $zbx->call('item.get',$params);
                foreach($result as $row){

                    print "<th>".$row['name']."</th>";

                    if(!empty($row['lastvalue'])){

                        print "<td>".$row['lastvalue']."</td>";

                    }else{

                        print "<td>No Data</td>";
                    }
                }
                ?>
            </tr>
            
            <tr>
                <?php
                $params = array(
                    "output" => array("name","lastvalue"),
                    "hostids" => $hostid,
                    "search" => array("name" => "System description")
                );
                //call api method
                $result = $zbx->call('item.get',$params);
                foreach($result as $row){

                    print "<th>".$row['name']."</th>";

                    if(!empty($row['lastvalue'])){

                        print "<td>".$row['lastvalue']."</td>";

                    }else{

                        print "<td>No Data</td>";
                    }
                }
                ?>
            </tr>
            
            <tr>
                <?php
                $params = array(
                    "output" => array("name","lastvalue"),
                    "hostids" => $hostid,
                    "search" => array("name" => "System location")
                );
                //call api method
                $result = $zbx->call('item.get',$params);
                foreach($result as $row){

                    print "<th>".$row['name']."</th>";

                    if(!empty($row['lastvalue'])){

                        print "<td>".$row['lastvalue']."</td>";

                    }else{

                        print "<td>No Data</td>";
                    }
                }
                ?>
            </tr>
            
            <tr>
                <?php
                $params = array(
                    "output" => array("name","lastvalue"),
                    "hostids" => $hostid,
                    "search" => array("name" => "System object ID")
                );
                //call api method
                $result = $zbx->call('item.get',$params);
                foreach($result as $row){

                    print "<th>".$row['name']."</th>";

                    if(!empty($row['lastvalue'])){

                        print "<td>".$row['lastvalue']."</td>";

                    }else{

                        print "<td>No Data</td>";
                    }
                }
                ?>
            </tr>
        </tbody>

	</table>
</body>

<script>
    $(document).ready( function () {
        $('#generalinfo_tbl').DataTable({
            order: [[1, 'desc']],
        });
    } );
</script>

</html>
