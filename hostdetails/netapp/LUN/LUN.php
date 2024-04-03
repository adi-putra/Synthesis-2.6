<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
	);
//call api method
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
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
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>
        <div style="overflow-x:auto;">
            <table id="LUN_table" class="table table-bordered table-striped">
            <?php

                $params = array(
                                "output" => array("name","lastvalue"),
                                "hostids" => $hostid
                            );
                //call api method
                $result = $zbx->call('application.get',$params);

                foreach($result as $data){

                    if($data['name'] == 'LUN API'){

                        $applicationid = $data['applicationid'];

                    }
                }
                
                // get LUN application
                $params = array(
                                    "output" => array("itemid","name","lastvalue"),
                                    "applicationids" => $applicationid
                                );
                //call api method
                $LUN = $zbx->call('item.get',$params);
                
                //tbl head
                print'<thead>';
                    print'<tr>';
                        print'<th>Details/Name</th>';
                        foreach ($LUN as $row) {

                            $name = $row['name'];
                            $value = $row["lastvalue"];
                    
                            if (strpos($name, 'LUN Total Capacity') !== false) {

                                if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                    $name = substr($name, 27,-1);
                                    print "<th>$name</th>";
                                }
                            }
                        }
                    print'</tr>';
                print '</thead>';
                

                // total capacity
                print "<tr>";
                    print "<td><b>LUN Total Capacity(GB)<b></td>";
                    foreach ($LUN as $row) {

                        $name = $row['name'];
                        $value = $row["lastvalue"];
                        
                        if (strpos($name, 'LUN Total Capacity') !== false) {

                            if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                $value = number_format($value/(1024*1024*1024),2);
                                print "<td>$value</td>";  
                            }
                        }
                    }
                print "</tr>";
                
                // available capacity
                print "<tr>";
                    print "<td><b>LUN Available Capacity (GB)<b></td>";
                    foreach ($LUN as $row) {

                        $name = $row['name'];
                        $value = $row["lastvalue"];
                        
                        if (strpos($name, 'LUN Available Capacity') !== false) {

                            if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                $value = number_format($value/(1024*1024*1024),2);
                                print "<td>$value</td>";  

                            }
                        }
                    }
                print "</tr>";
            ?>
            </table>
        </div>

        <!-- page script -->
        <script type="text/javascript">
            $(function () {
                $("#LUN_table").dataTable(
                    {
                        dom: 'Bfrtip',
                        buttons: [
                            'colvis'
                        ]
                    } 
                );
            });
        </script>
    </body>
</html>