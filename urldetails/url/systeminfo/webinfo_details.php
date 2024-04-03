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

	<table id="tbl_web" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Website Name</th>
                <th>Url</th>
                <th>Host</th>
                <th>Download Speed</th>
                <th>Failed Step of Scenario</th>
                <th>Last Error Message of Scenario</th>
                <th>Response Time For Step</th>
                <th>Response Code For Step</th>
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
                foreach($result as $httptest){

                    $http_id = $httptest['httptestid'];
                    $host_id = $httptest['hostid'];

                    //get hostname for each web
                    $params = array(
                    "output" => "extend",
                    "hostids" => $host_id
                    );

                    $res_host = $zbx->call('host.get', $params);
                    if(!empty($res_host)){

                        foreach($res_host as $host){

                            $hostname = $host['name'];
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

                        foreach($httptest['steps'] as $step){
                            
                            $stepname = $step['name'];
                            $url = $step['url'];

                            $params = array(
                                "output" => "extend",
                                "hostids" => $host_id,
                                "webitems" => true
                            );
    
                            $res_webitem = $zbx->call('item.get', $params);

                            if(!empty($res_webitem)){
                                foreach($res_webitem as $webItems){
                                    if (strpos($webItems['name'], $webgroup) !== false){
    
                                        //download speed
                                        if (strpos($webItems['name'], "Download speed for scenario") !== false){
                                            if($webItems['lastvalue'] !== '' || $webItems['lastvalue'] !== null){
    
                                                $download_speed = formatBytes($webItems['lastvalue'],2)."ps";
    
                                            }else{
                                                $download_speed= "No Data";
                                            }
                                        }
    
                                        //fail steps
                                        if (strpos($webItems['name'], "Failed step of scenario") !== false){
                                            if($webItems['lastvalue'] !== '' || $webItems['lastvalue'] !== null){
    
                                                $fail_step = $webItems['lastvalue'];
    
                                            }else{
                                                $fail_step= "No Data";
                                            }
                                        }
    
                                        //Last error message
                                        if (strpos($webItems['name'], "Last error message of scenario") !== false){
                                            if(empty($webItems['lastvalue'])){

                                                $last_err_msg="No data available";
                                                $style_err= "style='color:red'";
                                                
                                            }else{
                                               
                                                $last_err_msg = $webItems['lastvalue'];
                                                $style_err = "";
                                            }
                                        }

                                        //Response time for step
                                        if (strpos($webItems['name'], "Response time for step") !== false){
                                            if(empty($webItems['lastvalue'])){
    
                                                $response_time= "No Data";
    
                                            }else{

                                                $response_time = $webItems['lastvalue']*1000;
                                            }
                                        }
    
                                        //Response code for step
                                        if (strpos($webItems['name'], "Response code for step") !== false){
    
                                            if($webItems['lastvalue'] !== '' || $webItems['lastvalue'] !== null){
    
                                                $response_code = $webItems['lastvalue'];
                                                if($response_code == 200){
    
                                                    $response_code = "200(OK)";
                                                    $style = "style='background-color:green;color:white'";
    
                                                }else{
                
                                                    $response_code = $response_code;
                                                    $style = "style='background-color:red;color:white'";
                
                                                }
    
                                            }else{
                                                $response_code= "No Data";
                                            }
                                        }
                                    }
                                }
                            }
                            
                            print "<tr>";
                            print "<td $hoststyle>$stepname</td>";
                            print "<td $hoststyle>$url</td>";
                            print "<td><a $hoststyle href='hostdetails.php?hostid=".$hostid."'>".$hostname.$hoststatus."</a></td>";
                            print "<td $hoststyle>$download_speed</td>";
                            print "<td $hoststyle>$fail_step</td>";
                            print "<td $style_err>$last_err_msg</td>";
                            print "<td $hoststyle>$response_time</td>";
                            print "<td $style>$response_code</td>";
                            print "</tr>";

                        }
                    }
                }
            ?>
        </tbody>
    </table>
    <script type="text/javascript">
      $(function () {
        $("#tbl_web").dataTable();
      });
    </script>
</body>

</html>