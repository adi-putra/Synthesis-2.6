<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
//$httpid = $_GET['httpid'];
$webgroup = $_GET['webgroup'];

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
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <table id="example2" class="table table-bordered table-striped">
            <thead>
                <tr>
                <th>Group</th>
                <th>Web Scenario</th>
            </tr>
            </thead>
            <tbody>
            <?php
                
                $params = array(
                "output" => "extend",
                "selectSteps" => "extend",
                "selectTags" => "extend"
                );

                $result = $zbx->call('httptest.get', $params);
                foreach ($result as $item) {

                    $http_id = $item['httptestid'];
                    $host_id = $item['hostid'];

                    //get hostname for each web
                    $params = array(
                        "output" => "extend",
                        "hostids" => $host_id
                    );
            
                    $res_host = $zbx->call('host.get', $params);
                    if(!empty($res_host)){

                        $webgroups[] = $item['name'];
                    }
                }

                $uniqueValues = [];
                foreach ($webgroups as $webgroup) {
                    $uniqueValues[$webgroup] = $webgroup;
                }
               
                foreach ($uniqueValues as $webgroup) {
                   
                    print "<tr>";
                    print "<td><a href='url_monitoring_details.php?webgroup=".$webgroup."'>".$webgroup."</a></td>";
                    print "<td>";

                    $tbl_web[] = strtok($webgroup," ");

                    ?>
                    <table id="example3_<?= $tbl_web; ?>" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Url</th>
                            <th>Host</th>
                            <th>Status Code</th>
                            <!-- <th>Status Host</th> -->
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                            $params = array(
                            "output" => "extend",
                            "selectSteps" => "extend",
                            "selectTags" => "extend",
                            "search" => array("name" => $webgroup)
                            );

                            $result = $zbx->call('httptest.get', $params);
                            foreach ($result as $item) {

                                $http_id = $item['httptestid'];
                                $host_id = $item['hostid'];
                                
                                //get hostname for each web
                                $params = array(
                                    "output" => "extend",
                                    "hostids" => $host_id
                                );

                                $gethost = $zbx->call('host.get', $params);
                                if(!empty($gethost)){

                                    if($item['name'] == $webgroup){
                                    
                                        foreach($gethost as $host){
                                            $hostname = $host['host'];
                                            $hoststatus = $host['status'];
                                            $hostid =$host['hostid'];

                                            if($hoststatus == 1){

                                                $hoststatus = "(Disabled)";
                                                $hoststyle="style='color:red !important'";
                
                                            }else{
                
                                                $hoststatus = "";
                                                $hoststyle="";
                
                                            }
                                        }
                                    
                                        $webName = $item['name'];
                                        foreach ($item['steps'] as $step) {
                                            $url = $step['url'];
                                            $webName = $step['name'];

                                            $params = array(
                                            "output" => "extend",
                                            "hostids" => $host_id,
                                            "webitems" => true
                                            );

                                            $res_webitem = $zbx->call('item.get', $params);
                                            foreach($res_webitem as $webItems){
                                                if (strpos($webItems['name'], "Response code for step") !== false){
                                                    if($webItems['lastvalue'] != "" || $webItems['lastvalue'] != null){

                                                    $status = $webItems['lastvalue'];
                                                    if($status == 200){
                                                        $status= "200 (Ok)";
                                                        $style = "style='background-color:green;color:white'";
                                                    }else{
                                                        $status = $webItems['lastvalue'];
                                                        $style = "style='background-color:red;color:white'";
                                                    }

                                                    }else{
                                                    $status =  "No Data";
                                                    }
                                                }
                                            }


                                            print "<tr>";
                                            print "<td $hoststyle>$webName</td>";
                                            print "<td $hoststyle>$url</td>";
                                            print "<td><a $hoststyle href='hostdetails.php?hostid=".$hostid."'>".$hostname.$hoststatus."</a></td>";
                                            print "<td $style>$status</td>";
                                            //print "<td $hoststyle>$hoststatus</td>";
                                            print "</tr>";
                                        }
                                    }
                                    
                                }
                            }
                            
                        ?>
                        </tbody>
                    </table>

                    <?php
                    print "</td>";
                    print "</tr>";
                }
                ?>
            </tbody>
        </table>

        <script type="text/javascript">
            $(function () {
                $("#example1").dataTable();
            });

            $(function () {
                $("#example2").dataTable();
            });

            $(function () {

                var tbl= '<?= json_encode($tbl_web); ?>';
                var array_tbl = JSON.parse(tbl);

                array_tbl.forEach(function(item) {
                    
                    id = '"#'+item+'"';
                    console.log(id);
                    //$(id).dataTable();
                });
            });
        </script>
    </body>
</html>